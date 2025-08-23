<?php
/**
 * Submissions List View - Modern Flux Branding
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap flux-admin-dashboard">
    <!-- Flux Header -->
    <div class="flux-header">
        <div class="flux-brand">
            <div class="flux-logo">
                <h1 style="color: white; margin: 0; font-size: 2rem; font-weight: 800;">FLUX</h1>
            </div>
            <div class="flux-title">
                <h1>FLUX</h1>
                <span class="flux-subtitle">Form Submissions</span>
            </div>
        </div>
        <div class="flux-version">
            <span class="version-badge">v2.0</span>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="flux-dashboard-content">
        <!-- Page Actions -->
        <div class="flux-actions-section">
            <div class="flux-section-header">
                <h2>Submission Management</h2>
                <p>Review and manage all client onboarding submissions</p>
            </div>
            
            <div class="flux-actions-grid">
                <button type="button" class="flux-action-card primary" id="cob-test-notifications">
                    <div class="action-icon">
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 8L16 18L28 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <rect x="4" y="8" width="24" height="16" rx="2" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="action-content">
                        <h3>Test Email Notifications</h3>
                        <p>Send test notifications to verify email setup</p>
                    </div>
                </button>
            </div>
        </div>

        <!-- Submissions Table -->
        <?php if (!empty($submissions)): ?>
            <div class="flux-submissions-section">
                <div class="flux-section-header">
                    <h2>All Submissions</h2>
                    <p>Total: <?php echo count($submissions); ?> submissions</p>
                </div>
                
                <div class="flux-submissions-table">
                    <div class="flux-table-header">
                        <div class="flux-table-row">
                            <div class="flux-table-cell header">ID</div>
                            <div class="flux-table-cell header">Business</div>
                            <div class="flux-table-cell header">Contact</div>
                            <div class="flux-table-cell header">Project</div>
                            <div class="flux-table-cell header">Status</div>
                            <div class="flux-table-cell header">Submitted</div>
                            <div class="flux-table-cell header">Actions</div>
                        </div>
                    </div>
                    <div class="flux-table-body">
                        <?php foreach ($submissions as $submission): ?>
                            <div class="flux-table-row">
                                <div class="flux-table-cell">
                                    <div class="submission-id">#<?php echo esc_html($submission->id); ?></div>
                                </div>
                                <div class="flux-table-cell">
                                    <div class="business-info">
                                        <div class="business-name"><?php echo esc_html($submission->business_name); ?></div>
                                        <div class="business-email"><?php echo esc_html($submission->primary_contact_email); ?></div>
                                    </div>
                                </div>
                                <div class="flux-table-cell">
                                    <div class="contact-info">
                                        <div class="contact-name"><?php echo esc_html($submission->primary_contact_name); ?></div>
                                    </div>
                                </div>
                                <div class="flux-table-cell">
                                    <div class="project-info">
                                        <div class="project-name"><?php echo esc_html($submission->project_name); ?></div>
                                    </div>
                                </div>
                                <div class="flux-table-cell">
                                    <span class="flux-status flux-status-<?php echo esc_attr($submission->status); ?>">
                                        <?php echo esc_html(ucfirst($submission->status)); ?>
                                    </span>
                                </div>
                                <div class="flux-table-cell">
                                    <div class="submission-date">
                                        <?php echo esc_html(date_i18n('M j, Y', strtotime($submission->submitted_at))); ?>
                                        <div class="submission-time">
                                            <?php echo esc_html(date_i18n('g:i A', strtotime($submission->submitted_at))); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="flux-table-cell">
                                    <div class="flux-action-buttons">
                                        <a href="<?php echo admin_url('admin.php?page=cob-submissions&action=view&view=' . $submission->id); ?>" class="flux-btn flux-btn-primary">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M8 4L12 8L8 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            View
                                        </a>
                                        <button type="button" class="flux-btn flux-btn-secondary cob-test-notification" 
                                                data-submission-id="<?php echo $submission->id; ?>">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M4 8L16 18L28 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Test
                                        </button>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=cob-submissions&action=delete&view=' . $submission->id), 'delete_submission_' . $submission->id); ?>" 
                                           class="flux-btn flux-btn-danger" 
                                           onclick="return confirm('<?php _e('Are you sure you want to delete this submission?', 'client-onboarding-form'); ?>')">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M3 6H5H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M19 6V20C19 21.1046 18.1046 22 17 22H7C5.89543 22 5 21.1046 5 20V6M8 6V4C8 2.89543 8.89543 2 10 2H14C15.1046 2 16 2.89543 16 4V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="flux-empty-state">
                <div class="empty-icon">
                    <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M32 8L56 32L32 56L8 32L32 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M32 24L40 32L32 40L24 32L32 24Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h3>No Submissions Found</h3>
                <p>When clients start using your onboarding form, their submissions will appear here.</p>
                <a href="<?php echo home_url(); ?>?cob_preview=1" class="flux-btn flux-btn-primary" target="_blank">
                    Preview Your Form
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Test notification for specific submission
    $('.cob-test-notification').on('click', function() {
        var submissionId = $(this).data('submission-id');
        var button = $(this);
        var originalText = button.innerHTML;
        
        if (!confirm('Send test notification using this submission data?')) {
            return;
        }
        
        button.prop('disabled', true).html(`
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="2"/>
                <path d="M8 4V8L11 11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Sending...
        `);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cob_test_notification',
                submission_id: submissionId,
                nonce: '<?php echo wp_create_nonce('cob_test_notification'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    button.html(`
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 4L12 8L8 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Sent!
                    `).addClass('flux-btn-success');
                    
                    setTimeout(function() {
                        button.html(originalText).removeClass('flux-btn-success');
                    }, 3000);
                } else {
                    alert('Failed to send test notification: ' + (response.data || 'Unknown error'));
                    button.html(originalText);
                }
            },
            error: function() {
                alert('Failed to send test notification. Please try again.');
                button.html(originalText);
            }
        });
    });
    
    // Global test notifications
    $('#cob-test-notifications').on('click', function() {
        if (!confirm('Send test notifications to all configured email addresses?')) {
            return;
        }
        
        var button = $(this);
        var originalText = button.html();
        
        button.prop('disabled', true).html(`
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="2"/>
                <path d="M8 4V8L11 11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Sending Test Notifications...
        `);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cob_test_notifications',
                nonce: '<?php echo wp_create_nonce('cob_test_notifications'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    button.html(`
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 4L12 8L8 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Test Notifications Sent!
                    `).addClass('flux-btn-success');
                    
                    setTimeout(function() {
                        button.html(originalText).removeClass('flux-btn-success');
                    }, 3000);
                } else {
                    alert('Failed to send test notifications: ' + (response.data || 'Unknown error'));
                    button.html(originalText);
                }
            },
            error: function() {
                alert('Failed to send test notifications. Please try again.');
                button.html(originalText);
            }
        });
    });
});
</script>