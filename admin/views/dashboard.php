<?php
/**
 * Admin Dashboard View - Modern Flux Branding
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap flux-admin-dashboard">
    <!-- Flux Header -->
    <div class="flux-header">
        <div class="flux-brand">
            <div class="flux-title">
                <h1>FLUX</h1>
                <span class="flux-subtitle">Client Onboarding Dashboard</span>
            </div>
        </div>
        <div class="flux-version">
            <span class="version-badge">v2.0</span>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="flux-dashboard-content">
        <!-- Statistics Overview -->
        <div class="flux-stats-section">
            <div class="flux-section-header">
                <h2>Performance Overview</h2>
                <p>Real-time insights into your client onboarding process</p>
            </div>
            
            <div class="flux-stats-grid">
                <div class="flux-stat-card primary">
                    <div class="stat-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo esc_html($total_submissions); ?></div>
                        <div class="stat-label">Total Submissions</div>
                    </div>
                    <div class="stat-trend positive">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 4L12 8L8 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>+12%</span>
                    </div>
                </div>

                <div class="flux-stat-card secondary">
                    <div class="stat-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 2V5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16 2V5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M3 10H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo esc_html($this_month); ?></div>
                        <div class="stat-label">This Month</div>
                    </div>
                    <div class="stat-trend positive">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 4L12 8L8 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>+8%</span>
                    </div>
                </div>

                <div class="flux-stat-card tertiary">
                    <div class="stat-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 8V12L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo esc_html($this_week); ?></div>
                        <div class="stat-label">This Week</div>
                    </div>
                    <div class="stat-trend neutral">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 4L8 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>0%</span>
                    </div>
                </div>

                <div class="flux-stat-card accent">
                    <div class="stat-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo esc_html($total_submissions > 0 ? round(($this_month / $total_submissions) * 100, 1) : 0); ?>%</div>
                        <div class="stat-label">Conversion Rate</div>
                    </div>
                    <div class="stat-trend positive">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 4L12 8L8 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>+5%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="flux-actions-section">
            <div class="flux-section-header">
                <h2>Quick Actions</h2>
                <p>Manage your onboarding process efficiently</p>
            </div>
            
            <div class="flux-actions-grid">
                <a href="<?php echo admin_url('admin.php?page=cob-submissions'); ?>" class="flux-action-card primary">
                    <div class="action-icon">
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16 4L28 16L16 28L4 16L16 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16 12L20 16L16 20L12 16L16 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="action-content">
                        <h3>View All Submissions</h3>
                        <p>Browse and manage all client submissions</p>
                    </div>
                    <div class="action-arrow">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7.5 5L12.5 10L7.5 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </a>

                <a href="<?php echo admin_url('admin.php?page=cob-settings'); ?>" class="flux-action-card secondary">
                    <div class="action-icon">
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="16" cy="16" r="3" stroke="currentColor" stroke-width="2"/>
                            <path d="M16 1V5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16 27V31" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M31 16H27" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5 16H1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M27.5 4.5L24.5 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M7.5 24.5L4.5 27.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M27.5 27.5L24.5 24.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M7.5 7.5L4.5 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="action-content">
                        <h3>Plugin Settings</h3>
                        <p>Configure core plugin preferences</p>
                    </div>
                    <div class="action-arrow">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7.5 5L12.5 10L7.5 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </a>

                <a href="<?php echo admin_url('admin.php?page=cob-email-settings'); ?>" class="flux-action-card tertiary">
                    <div class="action-icon">
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 8L16 18L28 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <rect x="4" y="8" width="24" height="16" rx="2" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="action-content">
                        <h3>Email Settings</h3>
                        <p>Customize notification templates</p>
                    </div>
                    <div class="action-arrow">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7.5 5L12.5 10L7.5 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </a>

                <button type="button" class="flux-action-card accent" onclick="window.open('<?php echo home_url(); ?>?cob_preview=1')">
                    <div class="action-icon">
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16 4L28 16L16 28L4 16L16 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="16" cy="16" r="6" stroke="currentColor" stroke-width="2"/>
                            <circle cx="16" cy="16" r="2" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="action-content">
                        <h3>Preview Form</h3>
                        <p>See how your form looks to clients</p>
                    </div>
                    <div class="action-arrow">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7.5 5L12.5 10L7.5 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </button>
            </div>
        </div>

        <!-- Recent Submissions -->
        <div class="flux-submissions-section">
            <div class="flux-section-header">
                <h2>Recent Submissions</h2>
                <p>Latest client onboarding requests</p>
            </div>
            
            <?php if (!empty($recent_submissions)): ?>
                <div class="flux-submissions-table">
                    <div class="flux-table-header">
                        <div class="flux-table-row">
                            <div class="flux-table-cell header">Business</div>
                            <div class="flux-table-cell header">Contact</div>
                            <div class="flux-table-cell header">Project</div>
                            <div class="flux-table-cell header">Submitted</div>
                            <div class="flux-table-cell header">Actions</div>
                        </div>
                    </div>
                    <div class="flux-table-body">
                        <?php foreach ($recent_submissions as $submission): ?>
                            <div class="flux-table-row">
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
                                    <div class="submission-date">
                                        <?php echo esc_html(date_i18n('M j, Y', strtotime($submission->submitted_at))); ?>
                                        <div class="submission-time">
                                            <?php echo esc_html(date_i18n('g:i A', strtotime($submission->submitted_at))); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="flux-table-cell">
                                    <a href="<?php echo admin_url('admin.php?page=cob-submissions&action=view&view=' . $submission->id); ?>" class="flux-btn flux-btn-primary">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8 4L12 8L8 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        View
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
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
                    <h3>No Submissions Yet</h3>
                    <p>When clients start using your onboarding form, their submissions will appear here.</p>
                    <a href="<?php echo home_url(); ?>?cob_preview=1" class="flux-btn flux-btn-primary" target="_blank">
                        Preview Your Form
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Shortcode Information -->
        <div class="flux-shortcode-section">
            <div class="flux-section-header">
                <h2>Integration Guide</h2>
                <p>Display your onboarding form anywhere on your website</p>
            </div>
            
            <div class="flux-shortcode-card">
                <div class="shortcode-header">
                    <h3>Form Shortcode</h3>
                    <button class="flux-btn flux-btn-secondary copy-shortcode" data-shortcode="[client_onboarding_form]">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M6 2H10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <rect x="2" y="8" width="12" height="6" rx="1" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        Copy
                    </button>
                </div>
                <div class="shortcode-content">
                    <code class="flux-code">[client_onboarding_form]</code>
                </div>
                
                <div class="shortcode-options">
                    <h4>Available Parameters</h4>
                    <div class="flux-options-grid">
                        <div class="flux-option">
                            <span class="option-name">theme</span>
                            <span class="option-desc">"dark" or "light" (default: "dark")</span>
                        </div>
                        <div class="flux-option">
                            <span class="option-name">show_progress</span>
                            <span class="option-desc">true or false (default: true)</span>
                        </div>
                        <div class="flux-option">
                            <span class="option-name">auto_save</span>
                            <span class="option-desc">true or false (default: true)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy shortcode functionality
    const copyButtons = document.querySelectorAll('.copy-shortcode');
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const shortcode = this.getAttribute('data-shortcode');
            navigator.clipboard.writeText(shortcode).then(() => {
                const originalText = this.innerHTML;
                this.innerHTML = `
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 4L12 8L8 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Copied!
                `;
                this.classList.add('copied');
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.classList.remove('copied');
                }, 2000);
            });
        });
    });
});
</script>