<?php
/**
 * Admin Dashboard View
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Client Onboarding Dashboard', 'client-onboarding-form'); ?></h1>

    <div class="cob-dashboard">
        <!-- Statistics Cards -->
        <div class="cob-stats-grid">
            <div class="cob-stat-card">
                <div class="cob-stat-number"><?php echo esc_html($total_submissions); ?></div>
                <div class="cob-stat-label"><?php _e('Total Submissions', 'client-onboarding-form'); ?></div>
            </div>
            <div class="cob-stat-card">
                <div class="cob-stat-number"><?php echo esc_html($this_month); ?></div>
                <div class="cob-stat-label"><?php _e('This Month', 'client-onboarding-form'); ?></div>
            </div>
            <div class="cob-stat-card">
                <div class="cob-stat-number"><?php echo esc_html($this_week); ?></div>
                <div class="cob-stat-label"><?php _e('This Week', 'client-onboarding-form'); ?></div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="cob-quick-actions">
            <h2><?php _e('Quick Actions', 'client-onboarding-form'); ?></h2>
            <div class="cob-action-buttons">
                <a href="<?php echo admin_url('admin.php?page=cob-submissions'); ?>" class="button button-primary">
                    <?php _e('View All Submissions', 'client-onboarding-form'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=cob-settings'); ?>" class="button">
                    <?php _e('Plugin Settings', 'client-onboarding-form'); ?>
                </a>
                <button type="button" class="button" onclick="window.open('<?php echo home_url(); ?>?cob_preview=1')">
                    <?php _e('Preview Form', 'client-onboarding-form'); ?>
                </button>
            </div>
        </div>

        <!-- Recent Submissions -->
        <div class="cob-recent-submissions">
            <h2><?php _e('Recent Submissions', 'client-onboarding-form'); ?></h2>
            
            <?php if (!empty($recent_submissions)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Business Name', 'client-onboarding-form'); ?></th>
                            <th><?php _e('Contact Person', 'client-onboarding-form'); ?></th>
                            <th><?php _e('Email', 'client-onboarding-form'); ?></th>
                            <th><?php _e('Project', 'client-onboarding-form'); ?></th>
                            <th><?php _e('Submitted', 'client-onboarding-form'); ?></th>
                            <th><?php _e('Actions', 'client-onboarding-form'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_submissions as $submission): ?>
                            <tr>
                                <td><strong><?php echo esc_html($submission->business_name); ?></strong></td>
                                <td><?php echo esc_html($submission->primary_contact_name); ?></td>
                                <td><?php echo esc_html($submission->primary_contact_email); ?></td>
                                <td><?php echo esc_html($submission->project_name); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($submission->submitted_at))); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=cob-submissions&action=view&view=' . $submission->id); ?>" class="button button-small">
                                        <?php _e('View', 'client-onboarding-form'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php _e('No submissions yet.', 'client-onboarding-form'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Shortcode Info -->
        <div class="cob-shortcode-info">
            <h2><?php _e('How to Display the Form', 'client-onboarding-form'); ?></h2>
            <p><?php _e('Use the following shortcode to display the client onboarding form on any page or post:', 'client-onboarding-form'); ?></p>
            <code>[client_onboarding_form]</code>
            
            <h3><?php _e('Shortcode Parameters', 'client-onboarding-form'); ?></h3>
            <ul>
                <li><strong>theme:</strong> "dark" or "light" (default: "dark")</li>
                <li><strong>show_progress:</strong> true or false (default: true)</li>
                <li><strong>auto_save:</strong> true or false (default: true)</li>
            </ul>
            
            <p><?php _e('Example with parameters:', 'client-onboarding-form'); ?></p>
            <code>[client_onboarding_form theme="dark" show_progress="true" auto_save="true"]</code>
        </div>
    </div>
</div>