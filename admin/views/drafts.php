<?php
/**
 * Draft Management Admin Page
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get drafts data
$drafts = COB_Database::get_all_drafts(100);
$total_drafts = count($drafts);
?>

<div class="wrap">
    <h1>Draft Management</h1>
    <p>Track client form progress and manage shareable links</p>

    <div class="cob-admin-stats">
        <div class="cob-stat-box">
            <h3><?php echo $total_drafts; ?></h3>
            <p>Total Drafts</p>
        </div>
        <div class="cob-stat-box">
            <h3><?php echo count(array_filter($drafts, function($d) { return $d->progress_percentage > 50; })); ?></h3>
            <p>Over 50% Complete</p>
        </div>
        <div class="cob-stat-box">
            <h3><?php echo count(array_filter($drafts, function($d) { return !empty($d->share_token); })); ?></h3>
            <p>Shared Links</p>
        </div>
    </div>

    <div class="cob-drafts-table-wrapper">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Client Email</th>
                    <th>Project/Business Name</th>
                    <th>Current Step</th>
                    <th>Progress</th>
                    <th>Last Saved</th>
                    <th>Share Link</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($drafts)): ?>
                    <tr>
                        <td colspan="7">No drafts found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($drafts as $draft): 
                        $form_data = json_decode($draft->form_data, true);
                        $project_name = $form_data['project_name'] ?? 'N/A';
                        $business_name = $form_data['business_name'] ?? 'N/A';
                        $client_email = $draft->client_email ?: ($form_data['primary_contact_email'] ?? 'N/A');
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($client_email); ?></strong>
                            </td>
                            <td>
                                <strong><?php echo esc_html($project_name); ?></strong><br>
                                <small><?php echo esc_html($business_name); ?></small>
                            </td>
                            <td>
                                <span class="cob-step-badge step-<?php echo $draft->current_step; ?>">
                                    Step <?php echo $draft->current_step; ?>/4
                                </span>
                            </td>
                            <td>
                                <div class="cob-progress-container">
                                    <div class="cob-progress-bar">
                                        <div class="cob-progress-fill" style="width: <?php echo $draft->progress_percentage; ?>%"></div>
                                    </div>
                                    <span class="cob-progress-text"><?php echo $draft->progress_percentage; ?>%</span>
                                </div>
                            </td>
                            <td>
                                <?php echo wp_date('M j, Y g:i A', strtotime($draft->last_saved)); ?>
                            </td>
                            <td>
                                <?php if ($draft->share_token): ?>
                                    <button class="button button-small cob-copy-link" 
                                            data-link="<?php echo home_url('?cob_share=' . $draft->share_token); ?>">
                                        Copy Link
                                    </button>
                                <?php else: ?>
                                    <button class="button button-small cob-generate-link" 
                                            data-session="<?php echo esc_attr($draft->session_id); ?>">
                                        Generate Link
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="cob-action-buttons">
                                    <button class="button button-small cob-view-draft" 
                                            data-session="<?php echo esc_attr($draft->session_id); ?>">
                                        View
                                    </button>
                                    <button class="button button-small cob-delete-draft" 
                                            data-session="<?php echo esc_attr($draft->session_id); ?>">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Draft Details Modal -->
<div id="cob-draft-modal" class="cob-modal" style="display: none;">
    <div class="cob-modal-content">
        <div class="cob-modal-header">
            <h2>Draft Details</h2>
            <span class="cob-modal-close">&times;</span>
        </div>
        <div class="cob-modal-body">
            <div id="cob-draft-details"></div>
        </div>
    </div>
</div>

<style>
.cob-admin-stats {
    display: flex;
    gap: 20px;
    margin: 20px 0;
}

.cob-stat-box {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
    min-width: 120px;
}

.cob-stat-box h3 {
    margin: 0;
    font-size: 2em;
    color: #0073aa;
}

.cob-stat-box p {
    margin: 5px 0 0 0;
    color: #666;
}

.cob-step-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: bold;
    color: white;
}

.cob-step-badge.step-1 { background-color: #dc3545; }
.cob-step-badge.step-2 { background-color: #fd7e14; }
.cob-step-badge.step-3 { background-color: #ffc107; color: #000; }
.cob-step-badge.step-4 { background-color: #28a745; }

.cob-progress-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.cob-progress-bar {
    flex: 1;
    height: 20px;
    background-color: #f0f0f0;
    border-radius: 10px;
    overflow: hidden;
}

.cob-progress-fill {
    height: 100%;
    background-color: #9dff00;
    transition: width 0.3s ease;
}

.cob-progress-text {
    font-weight: bold;
    font-size: 12px;
    min-width: 35px;
}

.cob-action-buttons {
    display: flex;
    gap: 5px;
}

.cob-modal {
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.cob-modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border: 1px solid #888;
    width: 80%;
    max-width: 800px;
    border-radius: 4px;
}

.cob-modal-header {
    padding: 20px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cob-modal-header h2 {
    margin: 0;
}

.cob-modal-close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.cob-modal-close:hover {
    color: #000;
}

.cob-modal-body {
    padding: 20px;
    max-height: 70vh;
    overflow-y: auto;
}

.cob-drafts-table-wrapper {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Copy link functionality
    $('.cob-copy-link').on('click', function() {
        const link = $(this).data('link');
        navigator.clipboard.writeText(link).then(function() {
            alert('Link copied to clipboard!');
        });
    });

    // Generate link functionality
    $('.cob-generate-link').on('click', function() {
        const sessionId = $(this).data('session');
        const button = $(this);
        
        button.prop('disabled', true).text('Generating...');
        
        $.post(ajaxurl, {
            action: 'cob_generate_share_link',
            nonce: '<?php echo wp_create_nonce('cob_admin_nonce'); ?>',
            session_id: sessionId
        }).done(function(response) {
            if (response.success) {
                const link = response.data.link;
                button.replaceWith(
                    '<button class="button button-small cob-copy-link" data-link="' + link + '">Copy Link</button>'
                );
                // Re-bind click event for new button
                $('.cob-copy-link').off('click').on('click', function() {
                    const link = $(this).data('link');
                    navigator.clipboard.writeText(link).then(function() {
                        alert('Link copied to clipboard!');
                    });
                });
            } else {
                alert('Failed to generate link');
                button.prop('disabled', false).text('Generate Link');
            }
        });
    });

    // View draft functionality
    $('.cob-view-draft').on('click', function() {
        const sessionId = $(this).data('session');
        
        $.post(ajaxurl, {
            action: 'cob_get_draft_details',
            nonce: '<?php echo wp_create_nonce('cob_admin_nonce'); ?>',
            session_id: sessionId
        }).done(function(response) {
            if (response.success) {
                $('#cob-draft-details').html(response.data.html);
                $('#cob-draft-modal').show();
            }
        });
    });

    // Delete draft functionality
    $('.cob-delete-draft').on('click', function() {
        if (!confirm('Are you sure you want to delete this draft?')) return;
        
        const sessionId = $(this).data('session');
        const row = $(this).closest('tr');
        
        $.post(ajaxurl, {
            action: 'cob_delete_draft',
            nonce: '<?php echo wp_create_nonce('cob_admin_nonce'); ?>',
            session_id: sessionId
        }).done(function(response) {
            if (response.success) {
                row.fadeOut();
            } else {
                alert('Failed to delete draft');
            }
        });
    });

    // Close modal
    $('.cob-modal-close, .cob-modal').on('click', function(e) {
        if (e.target === this) {
            $('#cob-draft-modal').hide();
        }
    });
});
</script>