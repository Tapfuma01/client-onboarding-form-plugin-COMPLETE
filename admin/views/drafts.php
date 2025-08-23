<?php
/**
 * Drafts management page
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ensure database class is loaded
if (!class_exists('COB_Database')) {
    require_once COB_PLUGIN_PATH . 'includes/class-database.php';
}

// Handle database maintenance actions
if (isset($_POST['action']) && wp_verify_nonce($_POST['cob_admin_nonce'], 'cob_admin_action')) {
    switch ($_POST['action']) {
        case 'optimize_tables':
            COB_Database::optimize_tables();
            echo '<div class="notice notice-success"><p>Tables optimized successfully.</p></div>';
            break;
            
        case 'check_locks':
            COB_Database::check_table_locks();
            echo '<div class="notice notice-info"><p>Table lock check completed.</p></div>';
            break;
            
        case 'cleanup_drafts':
            $days = intval($_POST['retention_days'] ?? 30);
            $deleted = COB_Database::cleanup_old_drafts($days);
            echo '<div class="notice notice-success"><p>Cleaned up ' . $deleted . ' old drafts.</p></div>';
            break;
    }
}

$drafts = COB_Database::get_all_drafts(100, 0);
?>

<div class="wrap">
    <h1>Client Onboarding Form - Drafts Management</h1>
    
    <!-- Database Maintenance Section -->
    <div class="card">
        <h2>Database Maintenance</h2>
        <p>Use these tools to resolve database issues and prevent deadlocks:</p>
        
        <form method="post" style="margin: 20px 0;">
            <?php wp_nonce_field('cob_admin_action', 'cob_admin_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Database Actions</th>
                    <td>
                        <button type="submit" name="action" value="optimize_tables" class="button button-secondary">
                            Optimize Tables
                        </button>
                        <span class="description">Optimizes database tables to reduce deadlock likelihood</span>
                        <br><br>
                        
                        <button type="submit" name="action" value="check_locks" class="button button-secondary">
                            Check Table Locks
                        </button>
                        <span class="description">Checks for and resolves table locks</span>
                        <br><br>
                        
                        <label for="retention_days">Draft Retention (days):</label>
                        <input type="number" id="retention_days" name="retention_days" value="30" min="1" max="365" style="width: 80px;">
                        <button type="submit" name="action" value="cleanup_drafts" class="button button-secondary">
                            Cleanup Old Drafts
                        </button>
                        <span class="description">Removes old drafts to reduce table size</span>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    
    <!-- Drafts List -->
    <div class="card">
        <h2>Current Drafts</h2>
        
        <?php if (empty($drafts)): ?>
            <p>No drafts found.</p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Session ID</th>
                        <th>Client Email</th>
                        <th>Current Step</th>
                        <th>Progress</th>
                        <th>Last Saved</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($drafts as $draft): ?>
                        <tr>
                            <td><?php echo esc_html($draft->session_id); ?></td>
                            <td><?php echo esc_html($draft->client_email ?: 'Not provided'); ?></td>
                            <td><?php echo esc_html($draft->current_step); ?></td>
                            <td><?php echo esc_html($draft->progress_percentage); ?>%</td>
                            <td><?php echo esc_html($draft->last_saved); ?></td>
                            <td>
                                <button class="button button-small view-draft" data-session="<?php echo esc_attr($draft->session_id); ?>">
                                    View Details
                                </button>
                                <button class="button button-small generate-share" data-session="<?php echo esc_attr($draft->session_id); ?>">
                                    Generate Share Link
                                </button>
                                <button class="button button-small delete-draft" data-session="<?php echo esc_attr($draft->session_id); ?>">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Draft Details Modal -->
<div id="draft-details-modal" class="cob-modal" style="display: none;">
    <div class="cob-modal-content">
        <span class="cob-modal-close">&times;</span>
        <h2>Draft Details</h2>
        <div id="draft-details-content"></div>
    </div>
</div>

<style>
.cob-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
}

.cob-modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 800px;
    max-height: 80vh;
    overflow-y: auto;
}

.cob-modal-close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.cob-modal-close:hover {
    color: #000;
}

.card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}
</style>

<script>
jQuery(document).ready(function($) {
    // View draft details
    $('.view-draft').on('click', function() {
        const sessionId = $(this).data('session');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cob_get_draft_details',
                nonce: '<?php echo wp_create_nonce('cob_admin_nonce'); ?>',
                session_id: sessionId
            },
            success: function(response) {
                if (response.success) {
                    $('#draft-details-content').html(response.data.html);
                    $('#draft-details-modal').show();
                } else {
                    alert('Failed to load draft details');
                }
            }
        });
    });
    
    // Generate share link
    $('.generate-share').on('click', function() {
        const sessionId = $(this).data('session');
        const button = $(this);
        
        button.prop('disabled', true).text('Generating...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cob_generate_share_link',
                nonce: '<?php echo wp_create_nonce('cob_admin_nonce'); ?>',
                session_id: sessionId
            },
            success: function(response) {
                if (response.success) {
                    const link = response.data.link;
                    prompt('Share this link with your client:', link);
                } else {
                    alert('Failed to generate share link');
                }
            },
            complete: function() {
                button.prop('disabled', false).text('Generate Share Link');
            }
        });
    });
    
    // Delete draft
    $('.delete-draft').on('click', function() {
        if (!confirm('Are you sure you want to delete this draft? This action cannot be undone.')) {
            return;
        }
        
        const sessionId = $(this).data('session');
        const row = $(this).closest('tr');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cob_delete_draft',
                nonce: '<?php echo wp_create_nonce('cob_admin_nonce'); ?>',
                session_id: sessionId
            },
            success: function(response) {
                if (response.success) {
                    row.fadeOut();
                } else {
                    alert('Failed to delete draft');
                }
            }
        });
    });
    
    // Close modal
    $('.cob-modal-close').on('click', function() {
        $('#draft-details-modal').hide();
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(event) {
        if (event.target === document.getElementById('draft-details-modal')) {
            $('#draft-details-modal').hide();
        }
    });
});
</script>