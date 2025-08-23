<?php
/**
 * Email Settings Admin Page
 */

if (!defined('ABSPATH')) {
    exit;
}

$merge_tags = COB_Email_Notifications::get_available_merge_tags();
?>

<div class="wrap">
    <h1><?php _e('Email Notifications', 'client-onboarding-form'); ?></h1>
    <p class="description">Configure email notifications sent when forms are submitted. Similar to Gravity Forms email system.</p>

    <form method="post" action="" id="cob-email-settings-form">
        <?php wp_nonce_field('cob_email_settings_nonce'); ?>
        
        <div class="cob-email-settings">
            <!-- Email Configuration Tabs -->
            <div class="cob-email-tabs">
                <nav class="nav-tab-wrapper">
                    <a href="#general" class="nav-tab nav-tab-active">General Settings</a>
                    <a href="#admin" class="nav-tab">Admin Notification</a>
                    <a href="#client" class="nav-tab">Client Confirmation</a>
                    <a href="#additional" class="nav-tab">Additional Notifications</a>
                    <a href="#templates" class="nav-tab">Template Help</a>
                </nav>

                <!-- General Settings Tab -->
                <div id="general" class="cob-tab-content cob-tab-active">
                    <h2>General Email Settings</h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="email_from_name"><?php _e('From Name', 'client-onboarding-form'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="email_from_name" name="email_from_name" 
                                       value="<?php echo esc_attr($settings['email_from_name'] ?? get_bloginfo('name')); ?>" 
                                       class="regular-text" />
                                <p class="description">Name that appears in the "From" field of emails.</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="email_from_email"><?php _e('From Email', 'client-onboarding-form'); ?></label>
                            </th>
                            <td>
                                <input type="email" id="email_from_email" name="email_from_email" 
                                       value="<?php echo esc_attr($settings['email_from_email'] ?? get_option('admin_email')); ?>" 
                                       class="regular-text" />
                                <p class="description">Email address that appears in the "From" field of emails.</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Admin Notification Tab -->
                <div id="admin" class="cob-tab-content">
                    <h2>Admin Notification Settings</h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php _e('Enable Admin Notification', 'client-onboarding-form'); ?>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="enable_admin_notification">
                                        <input type="checkbox" id="enable_admin_notification" name="enable_admin_notification" 
                                               value="1" <?php checked($settings['enable_admin_notification'] ?? true); ?> />
                                        <?php _e('Send email notification to admin when form is submitted', 'client-onboarding-form'); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="admin_email"><?php _e('Admin Email(s)', 'client-onboarding-form'); ?></label>
                            </th>
                            <td>
                                <input type="email" id="admin_email" name="admin_email" 
                                       value="<?php echo esc_attr($settings['admin_email'] ?? get_option('admin_email')); ?>" 
                                       class="regular-text" required />
                                <p class="description">Primary admin email address.</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="additional_admin_emails"><?php _e('Additional Admin Emails', 'client-onboarding-form'); ?></label>
                            </th>
                            <td>
                                <textarea id="additional_admin_emails" name="additional_admin_emails" 
                                          class="large-text" rows="3"><?php echo esc_textarea($settings['additional_admin_emails'] ?? ''); ?></textarea>
                                <p class="description">Additional email addresses (comma-separated) to receive notifications.</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="admin_email_cc"><?php _e('CC Emails', 'client-onboarding-form'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="admin_email_cc" name="admin_email_cc" 
                                       value="<?php echo esc_attr($settings['admin_email_cc'] ?? ''); ?>" 
                                       class="regular-text" />
                                <p class="description">CC email addresses (comma-separated).</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="admin_email_bcc"><?php _e('BCC Emails', 'client-onboarding-form'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="admin_email_bcc" name="admin_email_bcc" 
                                       value="<?php echo esc_attr($settings['admin_email_bcc'] ?? ''); ?>" 
                                       class="regular-text" />
                                <p class="description">BCC email addresses (comma-separated).</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="admin_email_subject"><?php _e('Subject Line', 'client-onboarding-form'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="admin_email_subject" name="admin_email_subject" 
                                       value="<?php echo esc_attr($settings['admin_email_subject'] ?? 'New Client Onboarding Submission - {business_name}'); ?>" 
                                       class="large-text" />
                                <p class="description">Email subject line. Use merge tags like {business_name}.</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="admin_email_body"><?php _e('Email Body', 'client-onboarding-form'); ?></label>
                            </th>
                            <td>
                                <div class="cob-email-editor">
                                    <?php
                                    wp_editor(
                                        $settings['admin_email_body'] ?? '',
                                        'admin_email_body',
                                        [
                                            'textarea_name' => 'admin_email_body',
                                            'textarea_rows' => 15,
                                            'media_buttons' => false,
                                            'teeny' => false,
                                            'tinymce' => [
                                                'toolbar1' => 'bold,italic,underline,strikethrough,|,bullist,numlist,|,link,unlink,|,undo,redo',
                                                'toolbar2' => 'formatselect,|,forecolor,backcolor,|,alignleft,aligncenter,alignright,alignjustify'
                                            ]
                                        ]
                                    );
                                    ?>
                                    <p class="description">
                                        Leave empty to use the default template. Use merge tags and HTML for formatting.
                                        <button type="button" class="button cob-insert-merge-tags" data-target="admin_email_body">Insert Merge Tags</button>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="cob-email-test">
                        <h3>Test Admin Email</h3>
                        <p>Send a test email to verify your settings:</p>
                        <input type="email" id="admin_test_email" placeholder="test@example.com" class="regular-text" />
                        <button type="button" class="button" id="test-admin-email">Send Test Email</button>
                    </div>
                </div>

                <!-- Client Confirmation Tab -->
                <div id="client" class="cob-tab-content">
                    <h2>Client Confirmation Settings</h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php _e('Enable Client Confirmation', 'client-onboarding-form'); ?>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="enable_client_confirmation">
                                        <input type="checkbox" id="enable_client_confirmation" name="enable_client_confirmation" 
                                               value="1" <?php checked($settings['enable_client_confirmation'] ?? true); ?> />
                                        <?php _e('Send confirmation email to client when form is submitted', 'client-onboarding-form'); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="client_email_subject"><?php _e('Subject Line', 'client-onboarding-form'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="client_email_subject" name="client_email_subject" 
                                       value="<?php echo esc_attr($settings['client_email_subject'] ?? 'Thank you for your submission - {project_name}'); ?>" 
                                       class="large-text" />
                                <p class="description">Email subject line. Use merge tags like {project_name}.</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="client_email_body"><?php _e('Email Body', 'client-onboarding-form'); ?></label>
                            </th>
                            <td>
                                <div class="cob-email-editor">
                                    <?php
                                    wp_editor(
                                        $settings['client_email_body'] ?? '',
                                        'client_email_body',
                                        [
                                            'textarea_name' => 'client_email_body',
                                            'textarea_rows' => 15,
                                            'media_buttons' => false,
                                            'teeny' => false,
                                            'tinymce' => [
                                                'toolbar1' => 'bold,italic,underline,strikethrough,|,bullist,numlist,|,link,unlink,|,undo,redo',
                                                'toolbar2' => 'formatselect,|,forecolor,backcolor,|,alignleft,aligncenter,alignright,alignjustify'
                                            ]
                                        ]
                                    );
                                    ?>
                                    <p class="description">
                                        Leave empty to use the default template. Use merge tags and HTML for formatting.
                                        <button type="button" class="button cob-insert-merge-tags" data-target="client_email_body">Insert Merge Tags</button>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="cob-email-test">
                        <h3>Test Client Email</h3>
                        <p>Send a test email to verify your settings:</p>
                        <input type="email" id="client_test_email" placeholder="test@example.com" class="regular-text" />
                        <button type="button" class="button" id="test-client-email">Send Test Email</button>
                    </div>
                </div>

                <!-- Additional Notifications Tab -->
                <div id="additional" class="cob-tab-content">
                    <h2>Additional Notification Settings</h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php _e('Technical Contact Notification', 'client-onboarding-form'); ?>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="notify_technical_contact">
                                        <input type="checkbox" id="notify_technical_contact" name="notify_technical_contact" 
                                               value="1" <?php checked($settings['notify_technical_contact'] ?? false); ?> />
                                        <?php _e('Send notification to technical contact', 'client-onboarding-form'); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="technical_email_subject"><?php _e('Technical Email Subject', 'client-onboarding-form'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="technical_email_subject" name="technical_email_subject" 
                                       value="<?php echo esc_attr($settings['technical_email_subject'] ?? 'Technical Information Required - {project_name}'); ?>" 
                                       class="large-text" />
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <?php _e('Reporting Contact Notification', 'client-onboarding-form'); ?>
                            </th>
                            <td>
                                <fieldset>
                                    <label for="notify_reporting_contact">
                                        <input type="checkbox" id="notify_reporting_contact" name="notify_reporting_contact" 
                                               value="1" <?php checked($settings['notify_reporting_contact'] ?? false); ?> />
                                        <?php _e('Send notification to reporting contact', 'client-onboarding-form'); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="reporting_email_subject"><?php _e('Reporting Email Subject', 'client-onboarding-form'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="reporting_email_subject" name="reporting_email_subject" 
                                       value="<?php echo esc_attr($settings['reporting_email_subject'] ?? 'Reporting Setup Required - {project_name}'); ?>" 
                                       class="large-text" />
                            </td>
                        </tr>
                    </table>
                    
                    <div class="cob-email-test">
                        <h3>Test Additional Emails</h3>
                        <p>Send test emails to verify your settings:</p>
                        <div style="margin-bottom: 10px;">
                            <input type="email" id="technical_test_email" placeholder="technical@example.com" class="regular-text" />
                            <button type="button" class="button" id="test-technical-email">Send Technical Test</button>
                        </div>
                        <div>
                            <input type="email" id="reporting_test_email" placeholder="reporting@example.com" class="regular-text" />
                            <button type="button" class="button" id="test-reporting-email">Send Reporting Test</button>
                        </div>
                    </div>
                </div>

                <!-- Template Help Tab -->
                <div id="templates" class="cob-tab-content">
                    <h2>Template Help & Merge Tags</h2>
                    
                    <div class="cob-merge-tags-help">
                        <p>Use these merge tags in your email templates. They will be replaced with actual form data when emails are sent.</p>
                        
                        <?php foreach ($merge_tags as $category => $tags): ?>
                            <div class="cob-merge-tag-category">
                                <h3><?php echo esc_html($category); ?></h3>
                                <div class="cob-merge-tag-grid">
                                    <?php foreach ($tags as $tag => $description): ?>
                                        <div class="cob-merge-tag-item">
                                            <code class="cob-merge-tag" data-tag="<?php echo esc_attr($tag); ?>"><?php echo esc_html($tag); ?></code>
                                            <span class="cob-merge-tag-desc"><?php echo esc_html($description); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="cob-conditional-tags">
                            <h3>Conditional Tags</h3>
                            <p>Use conditional tags to show content only when a field has a value:</p>
                            <code>{if:field_name}This content shows only if field_name has a value{/if}</code>
                            
                            <h4>Examples:</h4>
                            <ul>
                                <li><code>{if:primary_contact_phone}Phone: {primary_contact_phone}{/if}</code></li>
                                <li><code>{if:current_website}Website: &lt;a href="{current_website}"&gt;{current_website}&lt;/a&gt;{/if}</code></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php submit_button(__('Save Email Settings', 'client-onboarding-form')); ?>
    </form>
</div>

<!-- Merge Tags Modal -->
<div id="cob-merge-tags-modal" class="cob-modal" style="display: none;">
    <div class="cob-modal-content">
        <div class="cob-modal-header">
            <h2>Insert Merge Tags</h2>
            <span class="cob-modal-close">&times;</span>
        </div>
        <div class="cob-modal-body">
            <div class="cob-merge-tags-list">
                <?php foreach ($merge_tags as $category => $tags): ?>
                    <div class="cob-merge-tag-category">
                        <h3><?php echo esc_html($category); ?></h3>
                        <?php foreach ($tags as $tag => $description): ?>
                            <div class="cob-merge-tag-item">
                                <button type="button" class="button cob-insert-tag" data-tag="<?php echo esc_attr($tag); ?>">
                                    <?php echo esc_html($tag); ?>
                                </button>
                                <span class="cob-merge-tag-desc"><?php echo esc_html($description); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
.cob-email-settings {
    max-width: 1200px;
}

.cob-email-tabs .nav-tab-wrapper {
    margin-bottom: 20px;
}

.cob-tab-content {
    display: none;
    background: #fff;
    padding: 20px;
    border: 1px solid #ddd;
    border-top: none;
}

.cob-tab-content.cob-tab-active {
    display: block;
}

.cob-email-editor {
    position: relative;
}

.cob-email-test {
    background: #f8f9fa;
    padding: 20px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    margin-top: 20px;
}

.cob-email-test h3 {
    margin-top: 0;
}

.cob-merge-tags-help {
    background: #fff;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.cob-merge-tag-category {
    margin-bottom: 30px;
}

.cob-merge-tag-category h3 {
    color: #2271b1;
    border-bottom: 2px solid #2271b1;
    padding-bottom: 8px;
    margin-bottom: 15px;
}

.cob-merge-tag-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 10px;
}

.cob-merge-tag-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 4px;
}

.cob-merge-tag {
    background: #e9ecef;
    padding: 4px 8px;
    border-radius: 3px;
    font-family: monospace;
    font-size: 12px;
    cursor: pointer;
    min-width: 120px;
    display: inline-block;
}

.cob-merge-tag:hover {
    background: #9dff00;
    color: #000;
}

.cob-merge-tag-desc {
    font-size: 12px;
    color: #666;
}

.cob-conditional-tags {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.cob-conditional-tags code {
    background: #f1f1f1;
    padding: 4px 8px;
    border-radius: 3px;
    font-family: monospace;
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
    max-height: 80vh;
    overflow-y: auto;
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
}

.cob-merge-tags-list .cob-merge-tag-item {
    justify-content: flex-start;
    margin-bottom: 5px;
}

.cob-insert-tag {
    min-width: 150px;
    text-align: left;
    font-family: monospace;
    font-size: 12px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        var target = $(this).attr('href');
        
        // Update active tab
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Update active content
        $('.cob-tab-content').removeClass('cob-tab-active');
        $(target).addClass('cob-tab-active');
    });

    // Test email functionality
    $('#test-admin-email').on('click', function() {
        var email = $('#admin_test_email').val();
        sendTestEmail('admin', email, $(this));
    });

    $('#test-client-email').on('click', function() {
        var email = $('#client_test_email').val();
        sendTestEmail('client', email, $(this));
    });

    $('#test-technical-email').on('click', function() {
        var email = $('#technical_test_email').val();
        sendTestEmail('technical', email, $(this));
    });

    $('#test-reporting-email').on('click', function() {
        var email = $('#reporting_test_email').val();
        sendTestEmail('reporting', email, $(this));
    });

    function sendTestEmail(type, email, button) {
        if (!email) {
            alert('Please enter an email address');
            return;
        }

        var originalText = button.text();
        button.prop('disabled', true).text('Sending...');

        $.post(ajaxurl, {
            action: 'cob_test_email',
            nonce: '<?php echo wp_create_nonce('cob_admin_nonce'); ?>',
            email_type: type,
            test_email: email
        }).done(function(response) {
            if (response.success) {
                alert('Test email sent successfully!');
            } else {
                alert('Failed to send test email: ' + response.data);
            }
        }).fail(function() {
            alert('Failed to send test email. Please try again.');
        }).always(function() {
            button.prop('disabled', false).text(originalText);
        });
    }

    // Merge tags modal
    var currentEditor = null;

    $('.cob-insert-merge-tags').on('click', function() {
        currentEditor = $(this).data('target');
        $('#cob-merge-tags-modal').show();
    });

    $('.cob-modal-close, .cob-modal').on('click', function(e) {
        if (e.target === this) {
            $('#cob-merge-tags-modal').hide();
        }
    });

    $('.cob-insert-tag').on('click', function() {
        var tag = $(this).data('tag');
        
        if (currentEditor) {
            // Insert into TinyMCE editor if active
            if (typeof tinyMCE !== 'undefined' && tinyMCE.get(currentEditor)) {
                tinyMCE.get(currentEditor).insertContent(tag);
            } else {
                // Insert into textarea
                var textarea = $('#' + currentEditor);
                var cursorPos = textarea.prop('selectionStart');
                var textBefore = textarea.val().substring(0, cursorPos);
                var textAfter = textarea.val().substring(cursorPos);
                textarea.val(textBefore + tag + textAfter);
            }
        }
        
        $('#cob-merge-tags-modal').hide();
    });

    // Click to copy merge tags
    $(document).on('click', '.cob-merge-tag', function() {
        var tag = $(this).text();
        navigator.clipboard.writeText(tag).then(function() {
            // Visual feedback
            var $tag = $(this);
            var originalBg = $tag.css('background-color');
            $tag.css('background-color', '#9dff00');
            setTimeout(function() {
                $tag.css('background-color', originalBg);
            }, 500);
        });
    });
});
</script>