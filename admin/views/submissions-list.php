<?php
/**
 * Submissions List View
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Form Submissions', 'client-onboarding-form'); ?></h1>
    
    <?php if (!empty($submissions)): ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-cb check-column">
                        <input type="checkbox" />
                    </th>
                    <th scope="col" class="manage-column"><?php _e('ID', 'client-onboarding-form'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Business Name', 'client-onboarding-form'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Contact Person', 'client-onboarding-form'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Email', 'client-onboarding-form'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Project', 'client-onboarding-form'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Status', 'client-onboarding-form'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Submitted', 'client-onboarding-form'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Actions', 'client-onboarding-form'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $submission): ?>
                    <tr>
                        <th scope="row" class="check-column">
                            <input type="checkbox" name="submission[]" value="<?php echo esc_attr($submission->id); ?>" />
                        </th>
                        <td><?php echo esc_html($submission->id); ?></td>
                        <td><strong><?php echo esc_html($submission->business_name); ?></strong></td>
                        <td><?php echo esc_html($submission->primary_contact_name); ?></td>
                        <td>
                            <a href="mailto:<?php echo esc_attr($submission->primary_contact_email); ?>">
                                <?php echo esc_html($submission->primary_contact_email); ?>
                            </a>
                        </td>
                        <td><?php echo esc_html($submission->project_name); ?></td>
                        <td>
                            <span class="cob-status cob-status-<?php echo esc_attr($submission->status); ?>">
                                <?php echo esc_html(ucfirst($submission->status)); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($submission->submitted_at))); ?>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=cob-submissions&action=view&view=' . $submission->id); ?>" class="button button-small">
                                <?php _e('View', 'client-onboarding-form'); ?>
                            </a>
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=cob-submissions&action=delete&view=' . $submission->id), 'delete_submission_' . $submission->id); ?>" 
                               class="button button-small button-link-delete" 
                               onclick="return confirm('<?php _e('Are you sure you want to delete this submission?', 'client-onboarding-form'); ?>')">
                                <?php _e('Delete', 'client-onboarding-form'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination would go here if needed -->
        
    <?php else: ?>
        <div class="notice notice-info">
            <p><?php _e('No form submissions found.', 'client-onboarding-form'); ?></p>
        </div>
    <?php endif; ?>
</div>