<?php
/**
 * Single Submission Detail View
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!$submission) {
    echo '<div class="notice notice-error"><p>' . __('Submission not found.', 'client-onboarding-form') . '</p></div>';
    return;
}
?>

<div class="wrap">
    <h1><?php printf(__('Submission #%d - %s', 'client-onboarding-form'), $submission->id, esc_html($submission->business_name)); ?></h1>
    
    <div class="cob-submission-detail">
        <!-- Submission Meta -->
        <div class="cob-submission-meta">
            <p><strong><?php _e('Submitted:', 'client-onboarding-form'); ?></strong> 
               <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($submission->submitted_at))); ?>
            </p>
            <p><strong><?php _e('Status:', 'client-onboarding-form'); ?></strong> 
               <span class="cob-status cob-status-<?php echo esc_attr($submission->status); ?>">
                   <?php echo esc_html(ucfirst($submission->status)); ?>
               </span>
            </p>
            <p><strong><?php _e('Session ID:', 'client-onboarding-form'); ?></strong> <?php echo esc_html($submission->session_id); ?></p>
        </div>

        <!-- Business Information -->
        <div class="cob-section">
            <h2><?php _e('Business Information', 'client-onboarding-form'); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php _e('Business Name', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->business_name); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Project Name', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->project_name); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Primary Contact', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->primary_contact_name); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Contact Email', 'client-onboarding-form'); ?></th>
                    <td><a href="mailto:<?php echo esc_attr($submission->primary_contact_email); ?>"><?php echo esc_html($submission->primary_contact_email); ?></a></td>
                </tr>
                <?php if ($submission->primary_contact_phone): ?>
                <tr>
                    <th><?php _e('Contact Phone', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->primary_contact_phone); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($submission->milestone_approver): ?>
                <tr>
                    <th><?php _e('Milestone Approver', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->milestone_approver); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($submission->billing_email): ?>
                <tr>
                    <th><?php _e('Billing Email', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->billing_email); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><?php _e('Preferred Contact Method', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html(ucfirst($submission->preferred_contact_method)); ?></td>
                </tr>
            </table>
        </div>

        <!-- Billing Address -->
        <?php if ($submission->billing_address_line1 || $submission->billing_address_city): ?>
        <div class="cob-section">
            <h2><?php _e('Billing Address', 'client-onboarding-form'); ?></h2>
            <address>
                <?php if ($submission->billing_address_line1): ?>
                    <?php echo esc_html($submission->billing_address_line1); ?><br>
                <?php endif; ?>
                <?php if ($submission->billing_address_line2): ?>
                    <?php echo esc_html($submission->billing_address_line2); ?><br>
                <?php endif; ?>
                <?php if ($submission->billing_address_city): ?>
                    <?php echo esc_html($submission->billing_address_city); ?>
                    <?php if ($submission->billing_address_postal_code): ?>
                        <?php echo esc_html($submission->billing_address_postal_code); ?>
                    <?php endif; ?><br>
                <?php endif; ?>
                <?php if ($submission->billing_address_country): ?>
                    <?php echo esc_html($submission->billing_address_country); ?>
                <?php endif; ?>
            </address>
        </div>
        <?php endif; ?>

        <!-- Technical Information -->
        <div class="cob-section">
            <h2><?php _e('Technical Information', 'client-onboarding-form'); ?></h2>
            <table class="form-table">
                <?php if ($submission->current_website): ?>
                <tr>
                    <th><?php _e('Current Website', 'client-onboarding-form'); ?></th>
                    <td><a href="<?php echo esc_url($submission->current_website); ?>" target="_blank"><?php echo esc_html($submission->current_website); ?></a></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th><?php _e('Technical Contact', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->technical_contact_name); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Technical Email', 'client-onboarding-form'); ?></th>
                    <td><a href="mailto:<?php echo esc_attr($submission->technical_contact_email); ?>"><?php echo esc_html($submission->technical_contact_email); ?></a></td>
                </tr>
                <?php if ($submission->hosting_provider): ?>
                <tr>
                    <th><?php _e('Hosting Provider', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->hosting_provider); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($submission->preferred_cms): ?>
                <tr>
                    <th><?php _e('Preferred CMS', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->preferred_cms); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($submission->technology_stack): ?>
                <tr>
                    <th><?php _e('Technology Stack', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->technology_stack); ?></td>
                </tr>
                <?php endif; ?>
            </table>
            
            <?php if ($submission->integration_requirements): ?>
            <h3><?php _e('Integration Requirements', 'client-onboarding-form'); ?></h3>
            <p><?php echo wp_kses_post(wpautop($submission->integration_requirements)); ?></p>
            <?php endif; ?>
        </div>

        <!-- Reporting Information -->
        <div class="cob-section">
            <h2><?php _e('Reporting Information', 'client-onboarding-form'); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php _e('Reporting Frequency', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->reporting_frequency); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Reporting Contact', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->reporting_contact_name); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Reporting Email', 'client-onboarding-form'); ?></th>
                    <td><a href="mailto:<?php echo esc_attr($submission->reporting_contact_email); ?>"><?php echo esc_html($submission->reporting_contact_email); ?></a></td>
                </tr>
                <?php if ($submission->reporting_format): ?>
                <tr>
                    <th><?php _e('Report Format', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->reporting_format); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($submission->key_metrics): ?>
                <tr>
                    <th><?php _e('Key Metrics', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->key_metrics); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Marketing Information -->
        <div class="cob-section">
            <h2><?php _e('Marketing Information', 'client-onboarding-form'); ?></h2>
            
            <?php if ($submission->target_audience): ?>
            <h3><?php _e('Target Audience', 'client-onboarding-form'); ?></h3>
            <p><?php echo wp_kses_post(wpautop($submission->target_audience)); ?></p>
            <?php endif; ?>
            
            <?php if ($submission->marketing_goals): ?>
            <h3><?php _e('Marketing Goals', 'client-onboarding-form'); ?></h3>
            <p><?php echo wp_kses_post(wpautop($submission->marketing_goals)); ?></p>
            <?php endif; ?>
            
            <table class="form-table">
                <?php if ($submission->marketing_budget): ?>
                <tr>
                    <th><?php _e('Marketing Budget', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->marketing_budget); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($submission->current_marketing_channels): ?>
                <tr>
                    <th><?php _e('Current Marketing Channels', 'client-onboarding-form'); ?></th>
                    <td><?php echo esc_html($submission->current_marketing_channels); ?></td>
                </tr>
                <?php endif; ?>
            </table>
            
            <?php if ($submission->brand_guidelines): ?>
            <h3><?php _e('Brand Guidelines', 'client-onboarding-form'); ?></h3>
            <p><?php echo wp_kses_post(wpautop($submission->brand_guidelines)); ?></p>
            <?php endif; ?>
            
            <?php if ($submission->competitor_analysis): ?>
            <h3><?php _e('Competitor Analysis', 'client-onboarding-form'); ?></h3>
            <p><?php echo wp_kses_post(wpautop($submission->competitor_analysis)); ?></p>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <div class="cob-actions">
            <a href="<?php echo admin_url('admin.php?page=cob-submissions'); ?>" class="button">
                ← <?php _e('Back to Submissions', 'client-onboarding-form'); ?>
            </a>
            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=cob-submissions&action=delete&view=' . $submission->id), 'delete_submission_' . $submission->id); ?>" 
               class="button button-link-delete" 
               onclick="return confirm('<?php _e('Are you sure you want to delete this submission?', 'client-onboarding-form'); ?>')">
                <?php _e('Delete Submission', 'client-onboarding-form'); ?>
            </a>
        </div>
    </div>
</div>