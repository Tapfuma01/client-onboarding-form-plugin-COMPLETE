<?php
/**
 * Settings Page View
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Client Onboarding Settings', 'client-onboarding-form'); ?></h1>

    <?php
    // Display success/error messages
    if (isset($_GET['settings-updated'])) {
        if ($_GET['settings-updated'] === 'true') {
            echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Failed to save settings. Please try again.</p></div>';
        }
    }
    ?>

    <form method="post" action="" id="cob-settings-form">
        <?php wp_nonce_field('cob_settings_nonce'); ?>
        <input type="hidden" name="cob_form_submitted" value="1" />
        <input type="hidden" name="submit" value="1" />
        
        <table class="form-table">
            <!-- Email Settings -->
            <tr>
                <th scope="row">
                    <label for="admin_email"><?php _e('Admin Email', 'client-onboarding-form'); ?></label>
                </th>
                <td>
                    <input type="email" id="admin_email" name="admin_email" value="<?php echo esc_attr($settings['admin_email']); ?>" class="regular-text" />
                    <p class="description"><?php _e('Email address to receive form submission notifications.', 'client-onboarding-form'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="company_name"><?php _e('Company Name', 'client-onboarding-form'); ?></label>
                </th>
                <td>
                    <input type="text" id="company_name" name="company_name" value="<?php echo esc_attr($settings['company_name']); ?>" class="regular-text" />
                    <p class="description"><?php _e('Your company name for email templates.', 'client-onboarding-form'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <?php _e('Legacy Email Notifications', 'client-onboarding-form'); ?>
                </th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><?php _e('Legacy Email Notifications', 'client-onboarding-form'); ?></legend>
                        <label for="enable_notifications">
                            <input type="checkbox" id="enable_notifications" name="enable_notifications" value="1" <?php checked($settings['enable_notifications']); ?> />
                            <?php _e('Enable legacy email notifications (deprecated - use Email Notifications page instead)', 'client-onboarding-form'); ?>
                        </label>
                        <p class="description">
                            <strong>Note:</strong> This setting is deprecated. Please use the new 
                            <a href="<?php echo admin_url('admin.php?page=cob-email-settings'); ?>">Email Notifications</a> 
                            page for advanced email configuration.
                        </p>
                    </fieldset>
                </td>
            </tr>

            <!-- Form Settings -->
            <tr>
                <th scope="row">
                    <label for="auto_save_interval"><?php _e('Auto-save Interval', 'client-onboarding-form'); ?></label>
                </th>
                <td>
                    <input type="number" id="auto_save_interval" name="auto_save_interval" value="<?php echo esc_attr($settings['auto_save_interval']); ?>" min="10" max="300" class="small-text" />
                    <span><?php _e('seconds', 'client-onboarding-form'); ?></span>
                    <p class="description"><?php _e('How often to automatically save form drafts (10-300 seconds).', 'client-onboarding-form'); ?></p>
                </td>
            </tr>

            <!-- Webhook Settings -->
            <tr>
                <th scope="row">
                    <label for="webhook_url"><?php _e('Webhook URL', 'client-onboarding-form'); ?></label>
                </th>
                <td>
                    <input type="url" id="webhook_url" name="webhook_url" value="<?php echo esc_attr($settings['webhook_url'] ?? ''); ?>" class="regular-text" />
                    <p class="description"><?php _e('Optional webhook URL to send form data to external services.', 'client-onboarding-form'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <?php _e('Enable Webhook', 'client-onboarding-form'); ?>
                </th>
                <td>
                    <fieldset>
                        <legend class="screen-reader-text"><?php _e('Enable Webhook', 'client-onboarding-form'); ?></legend>
                        <label for="enable_webhook">
                            <input type="checkbox" id="enable_webhook" name="enable_webhook" value="1" <?php checked($settings['enable_webhook'] ?? false); ?> />
                            <?php _e('Send form submissions to webhook URL', 'client-onboarding-form'); ?>
                        </label>
                    </fieldset>
                </td>
            </tr>
        </table>

        <!-- Form Styling Preview -->
        <h2><?php _e('Form Preview', 'client-onboarding-form'); ?></h2>
        <p><?php _e('Use this shortcode to display the form:', 'client-onboarding-form'); ?></p>
        <p><code>[client_onboarding_form]</code></p>
        
        <h3><?php _e('Available Parameters', 'client-onboarding-form'); ?></h3>
        <ul>
            <li><strong>theme:</strong> "dark" (default) or "light"</li>
            <li><strong>show_progress:</strong> true (default) or false</li>
            <li><strong>auto_save:</strong> true (default) or false</li>
        </ul>

        <?php submit_button(__('Save Settings', 'client-onboarding-form')); ?>
        
        <!-- Test button for debugging -->
        <button type="button" id="cob-test-button" style="margin-left: 10px;">Test Form Data</button>
    </form>

    <script>
    jQuery(document).ready(function($) {
        console.log('COB: Main settings page JavaScript loaded');
        
        // Test button to check form data
        $('#cob-test-button').on('click', function() {
            var formData = $('#cob-settings-form').serialize();
            console.log('COB: Form data test:', formData);
            alert('Form data: ' + formData);
        });
        
        // Debug form submission
        $('#cob-settings-form').on('submit', function(e) {
            console.log('COB: Main settings form submit event triggered');
            console.log('COB: Main settings form data:', $(this).serialize());
            console.log('COB: Form action:', $(this).attr('action'));
            console.log('COB: Form method:', $(this).attr('method'));
            
            // Check if all required fields are filled
            var requiredFields = $(this).find('[required]');
            var missingFields = [];
            
            requiredFields.each(function() {
                if (!$(this).val().trim()) {
                    missingFields.push($(this).attr('name'));
                }
            });
            
            if (missingFields.length > 0) {
                console.log('COB: Missing required fields:', missingFields);
                alert('Please fill in all required fields: ' + missingFields.join(', '));
                e.preventDefault();
                return false;
            }
            
            console.log('COB: Main settings form submission proceeding...');
        });
        
        // Monitor submit button click
        $('input[type="submit"]').on('click', function(e) {
            console.log('COB: Submit button clicked');
            console.log('COB: Submit button value:', $(this).val());
            console.log('COB: Submit button name:', $(this).attr('name'));
        });
    });
    </script>

    <!-- Database Information -->
    <div class="cob-database-info">
        <h2><?php _e('Database Information', 'client-onboarding-form'); ?></h2>
        
        <?php
        global $wpdb;
        $submissions_table = $wpdb->prefix . 'cob_submissions';
        $drafts_table = $wpdb->prefix . 'cob_drafts';
        $logs_table = $wpdb->prefix . 'cob_logs';
        
        $submission_count = $wpdb->get_var("SELECT COUNT(*) FROM $submissions_table");
        $draft_count = $wpdb->get_var("SELECT COUNT(*) FROM $drafts_table");
        $log_count = $wpdb->get_var("SELECT COUNT(*) FROM $logs_table");
        ?>
        
        <table class="widefat fixed">
            <thead>
                <tr>
                    <th><?php _e('Table', 'client-onboarding-form'); ?></th>
                    <th><?php _e('Records', 'client-onboarding-form'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo esc_html($submissions_table); ?></td>
                    <td><?php echo esc_html($submission_count); ?></td>
                </tr>
                <tr>
                    <td><?php echo esc_html($drafts_table); ?></td>
                    <td><?php echo esc_html($draft_count); ?></td>
                </tr>
                <tr>
                    <td><?php echo esc_html($logs_table); ?></td>
                    <td><?php echo esc_html($log_count); ?></td>
                </tr>
            </tbody>
        </table>
        
        <!-- Database Update Section -->
        <div class="cob-database-update" style="margin-top: 20px;">
            <h3><?php _e('Database Maintenance', 'client-onboarding-form'); ?></h3>
            
            <p><?php _e('If you encounter database errors with field sizes, click the button below to update the database schema:', 'client-onboarding-form'); ?></p>
            
            <button type="button" id="cob-update-database" class="button button-primary">
                <?php _e('Update Database Schema', 'client-onboarding-form'); ?>
            </button>
            
            <div id="cob-update-result" style="margin-top: 10px;"></div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Database update button
    $('#cob-update-database').on('click', function() {
        var $button = $(this);
        var $result = $('#cob-update-result');
        
        $button.prop('disabled', true).text('<?php _e('Updating...', 'client-onboarding-form'); ?>');
        $result.html('');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cob_update_database_schema',
                nonce: '<?php echo wp_create_nonce('cob_admin_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $result.html('<div class="notice notice-success"><p>' + response.data + '</p></div>');
                } else {
                    $result.html('<div class="notice notice-error"><p>Error: ' + response.data + '</p></div>');
                }
            },
            error: function() {
                $result.html('<div class="notice notice-error"><p>AJAX request failed. Please try again.</p></div>');
            },
            complete: function() {
                $button.prop('disabled', false).text('<?php _e('Update Database Schema', 'client-onboarding-form'); ?>');
            }
        });
    });
});
</script>