<?php
/**
 * Email Notifications Class - Similar to Gravity Forms email system
 */

if (!defined('ABSPATH')) {
    exit;
}

class COB_Email_Notifications {

    private $settings;
    private $submission_data;
    private $submission_id;

    public function __construct() {
        $this->settings = get_option('cob_settings', []);
        
        // Hook into form submission
        add_action('cob_after_submission', [$this, 'send_notifications'], 10, 2);
        
        // Add email template filters
        add_filter('cob_admin_email_template', [$this, 'get_admin_email_template'], 10, 2);
        add_filter('cob_client_email_template', [$this, 'get_client_email_template'], 10, 2);
        
        // Add AJAX handlers for email testing
        add_action('wp_ajax_cob_test_email', [$this, 'test_email']);
        add_action('wp_ajax_cob_send_test_notification', [$this, 'send_test_notification']);
        add_action('wp_ajax_cob_test_notifications', [$this, 'test_notifications']);
        add_action('wp_ajax_cob_test_notification', [$this, 'test_notification']);
    }

    /**
     * Main method to send all configured notifications
     */
    public function send_notifications($submission_id, $submission_data) {
        $this->submission_id = $submission_id;
        $this->submission_data = $submission_data;

        // Debug logging
        if (class_exists('COB_Database')) {
            COB_Database::log_activity('email_notification_started', $submission_id, $submission_data['session_id'], 
                'Settings: ' . json_encode($this->settings));
        }

        // Send admin notification
        if ($this->should_send_admin_notification()) {
            if (class_exists('COB_Database')) {
                COB_Database::log_activity('admin_notification_triggered', $submission_id, $submission_data['session_id'], 
                    'Admin email: ' . ($this->settings['admin_email'] ?? 'NOT_SET'));
            }
            $this->send_admin_notification();
        } else {
            if (class_exists('COB_Database')) {
                COB_Database::log_activity('admin_notification_skipped', $submission_id, $submission_data['session_id'], 
                    'enable_admin_notification: ' . ($this->settings['enable_admin_notification'] ?? 'NOT_SET') . 
                    ', admin_email: ' . ($this->settings['admin_email'] ?? 'NOT_SET'));
            }
        }

        // Send client confirmation
        if ($this->should_send_client_confirmation()) {
            if (class_exists('COB_Database')) {
                COB_Database::log_activity('client_notification_triggered', $submission_id, $submission_data['session_id'], 
                    'Client email: ' . $submission_data['primary_contact_email']);
            }
            $this->send_client_confirmation();
        } else {
            if (class_exists('COB_Database')) {
                COB_Database::log_activity('client_notification_skipped', $submission_id, $submission_data['session_id'], 
                    'enable_client_confirmation: ' . ($this->settings['enable_client_confirmation'] ?? 'NOT_SET') . 
                    ', client_email: ' . ($submission_data['primary_contact_email'] ?? 'NOT_SET'));
            }
        }

        // Send additional notifications if configured
        $this->send_additional_notifications();
    }

    /**
     * Check if admin notification should be sent
     */
    private function should_send_admin_notification() {
        // Check if admin notifications are enabled
        if (empty($this->settings['enable_admin_notification'])) {
            return false;
        }
        
        // Use custom admin email if set, otherwise fall back to WordPress admin email
        $admin_email = $this->settings['admin_email'] ?? get_option('admin_email');
        
        return !empty($admin_email);
    }

    /**
     * Check if client confirmation should be sent
     */
    private function should_send_client_confirmation() {
        return !empty($this->settings['enable_client_confirmation']) && 
               !empty($this->submission_data['primary_contact_email']);
    }

    /**
     * Send admin notification email
     */
    private function send_admin_notification() {
        $to = $this->get_admin_email_recipients();
        $subject = $this->parse_template($this->get_admin_email_subject());
        $message = $this->parse_template($this->get_admin_email_body());
        $headers = $this->get_email_headers('admin');

        // Prepare file attachments
        $attachments = $this->prepare_file_attachments();

        $sent = wp_mail($to, $subject, $message, $headers, $attachments);

        // Log the email attempt
        COB_Database::log_activity(
            $sent ? 'admin_email_sent' : 'admin_email_failed',
            $this->submission_id,
            $this->submission_data['session_id'],
            'To: ' . (is_array($to) ? implode(', ', $to) : $to) . 
            (empty($attachments) ? '' : ', Attachments: ' . count($attachments))
        );

        return $sent;
    }

    /**
     * Prepare file attachments for email notifications
     * @return array Array of file paths for attachments
     */
    private function prepare_file_attachments() {
        $attachments = [];
        
        // Check for uploaded files
        $file_fields = [
            'logo_file' => ['url', 'name', 'id'],
            'brand_guidelines' => ['url', 'name', 'id'],
            'brand_guidelines_upload' => ['url', 'name', 'id']
        ];
        
        foreach ($file_fields as $field => $suffixes) {
            $url_key = $field . '_url';
            $id_key = $field . '_id';
            
            if (!empty($this->submission_data[$url_key]) && !empty($this->submission_data[$id_key])) {
                $attachment_id = intval($this->submission_data[$id_key]);
                $file_path = get_attached_file($attachment_id);
                
                if ($file_path && file_exists($file_path)) {
                    $attachments[] = $file_path;
                    error_log("COB: Added file attachment: $file_path for field $field");
                } else {
                    error_log("COB: File not found for attachment ID $attachment_id (field: $field)");
                }
            }
        }
        
        if (!empty($attachments)) {
            error_log("COB: Prepared " . count($attachments) . " file attachments for email");
        } else {
            error_log("COB: No file attachments found for email");
        }
        
        return $attachments;
    }

    /**
     * Send client confirmation email
     */
    private function send_client_confirmation() {
        $to = $this->submission_data['primary_contact_email'];
        $subject = $this->parse_template($this->get_client_email_subject());
        $message = $this->parse_template($this->get_client_email_body());
        $headers = $this->get_email_headers('client');

        $sent = wp_mail($to, $subject, $message, $headers);

        // Log the email attempt
        COB_Database::log_activity(
            $sent ? 'client_email_sent' : 'client_email_failed',
            $this->submission_id,
            $this->submission_data['session_id'],
            'To: ' . $to
        );

        return $sent;
    }

    /**
     * Send additional notifications (CC, BCC, etc.)
     */
    private function send_additional_notifications() {
        // Technical contact notification
        if (!empty($this->settings['notify_technical_contact']) && 
            !empty($this->submission_data['technical_contact_email'])) {
            $this->send_technical_notification();
        }

        // Reporting contact notification
        if (!empty($this->settings['notify_reporting_contact']) && 
            !empty($this->submission_data['reporting_contact_email'])) {
            $this->send_reporting_notification();
        }
    }

    /**
     * Send technical contact notification
     */
    private function send_technical_notification() {
        $to = $this->submission_data['technical_contact_email'];
        $subject = $this->parse_template($this->get_technical_email_subject());
        $message = $this->parse_template($this->get_technical_email_body());
        $headers = $this->get_email_headers('technical');

        $sent = wp_mail($to, $subject, $message, $headers);

        COB_Database::log_activity(
            $sent ? 'technical_email_sent' : 'technical_email_failed',
            $this->submission_id,
            $this->submission_data['session_id'],
            'To: ' . $to
        );

        return $sent;
    }

    /**
     * Send reporting contact notification
     */
    private function send_reporting_notification() {
        $to = $this->submission_data['reporting_contact_email'];
        $subject = $this->parse_template($this->get_reporting_email_subject());
        $message = $this->parse_template($this->get_reporting_email_body());
        $headers = $this->get_email_headers('reporting');

        $sent = wp_mail($to, $subject, $message, $headers);

        COB_Database::log_activity(
            $sent ? 'reporting_email_sent' : 'reporting_email_failed',
            $this->submission_id,
            $this->submission_data['session_id'],
            'To: ' . $to
        );

        return $sent;
    }

    /**
     * Get admin email recipients
     */
    private function get_admin_email_recipients() {
        $recipients = [];
        
        // Primary admin email - use custom if set, otherwise fall back to WordPress admin email
        $admin_email = $this->settings['admin_email'] ?? get_option('admin_email');
        if (!empty($admin_email)) {
            $recipients[] = $admin_email;
        }

        // Additional admin emails
        if (!empty($this->settings['additional_admin_emails'])) {
            $additional = array_map('trim', explode(',', $this->settings['additional_admin_emails']));
            $recipients = array_unique(array_merge($recipients, array_filter($additional, 'is_email')));
        }

        return array_unique($recipients);
    }

    /**
     * Get email headers
     */
    private function get_email_headers($type = 'admin') {
        $headers = [];
        
        // Content type
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        
        // From header
        $from_name = $this->settings['email_from_name'] ?? get_bloginfo('name');
        $from_email = $this->settings['email_from_email'] ?? get_option('admin_email');
        $headers[] = "From: {$from_name} <{$from_email}>";

        // Reply-to for client emails
        if ($type === 'client' && !empty($this->settings['admin_email'])) {
            $headers[] = "Reply-To: {$this->settings['admin_email']}";
        }

        // CC and BCC based on settings
        if ($type === 'admin') {
            if (!empty($this->settings['admin_email_cc'])) {
                $cc_emails = array_map('trim', explode(',', $this->settings['admin_email_cc']));
                foreach (array_filter($cc_emails, 'is_email') as $cc_email) {
                    $headers[] = "Cc: {$cc_email}";
                }
            }

            if (!empty($this->settings['admin_email_bcc'])) {
                $bcc_emails = array_map('trim', explode(',', $this->settings['admin_email_bcc']));
                foreach (array_filter($bcc_emails, 'is_email') as $bcc_email) {
                    $headers[] = "Bcc: {$bcc_email}";
                }
            }
        }

        return $headers;
    }

    /**
     * Parse email template with merge tags
     */
    private function parse_template($template) {
        if (empty($template)) {
            return '';
        }

        // Define merge tags
        $merge_tags = $this->get_merge_tags();

        // Replace merge tags in template
        foreach ($merge_tags as $tag => $value) {
            $template = str_replace('{' . $tag . '}', $value, $template);
        }

        // Handle conditional merge tags
        $template = $this->parse_conditional_tags($template);

        return $template;
    }

    /**
     * Get available merge tags
     */
    private function get_merge_tags() {
        $tags = [
            // Submission info
            'submission_id' => $this->submission_id,
            'submission_date' => date_i18n(get_option('date_format') . ' ' . get_option('time_format')),
            'submission_time' => date_i18n(get_option('time_format')),
            
            // Site info
            'site_name' => get_bloginfo('name'),
            'site_url' => home_url(),
            'admin_email' => get_option('admin_email'),
            
            // Step 1: Client Information
            'project_name' => $this->submission_data['project_name'] ?? '',
            'business_name' => $this->submission_data['business_name'] ?? '',
            'primary_contact_name' => $this->submission_data['primary_contact_name'] ?? '',
            'primary_contact_email' => $this->submission_data['primary_contact_email'] ?? '',
            'primary_contact_number' => $this->submission_data['primary_contact_number'] ?? '',
            'main_approver' => $this->submission_data['main_approver'] ?? '',
            'billing_email' => $this->submission_data['billing_email'] ?? '',
            'vat_number' => $this->submission_data['vat_number'] ?? '',
            'preferred_contact_method' => $this->submission_data['preferred_contact_method'] ?? '',
            'address_line_1' => $this->submission_data['address_line_1'] ?? '',
            'address_line_2' => $this->submission_data['address_line_2'] ?? '',
            'city' => $this->submission_data['city'] ?? '',
            'country' => $this->submission_data['country'] ?? '',
            'postal_code' => $this->submission_data['postal_code'] ?? '',
            
            // Step 2: Technical Information
            'current_cms' => $this->submission_data['current_cms'] ?? '',
            'website_hosting_company' => $this->submission_data['website_hosting_company'] ?? '',
            'website_contact_email' => $this->submission_data['website_contact_email'] ?? '',
            'domain_hosting_company' => $this->submission_data['domain_hosting_company'] ?? '',
            'domain_contact_email' => $this->submission_data['domain_contact_email'] ?? '',
            'cms_link' => $this->submission_data['cms_link'] ?? '',
            'cms_username' => $this->submission_data['cms_username'] ?? '',
            'cms_password' => $this->submission_data['cms_password'] ?? '',
            'current_crm' => $this->submission_data['current_crm'] ?? '',
            'third_party_integrations' => $this->submission_data['third_party_integrations'] ?? '',
            'third_party_name' => $this->submission_data['third_party_name'] ?? '',
            'third_party_contact_number' => $this->submission_data['third_party_contact_number'] ?? '',
            'third_party_contact_email' => $this->submission_data['third_party_contact_email'] ?? '',
            'booking_engine_name' => $this->submission_data['booking_engine_name'] ?? '',
            'booking_engine_username' => $this->submission_data['booking_engine_username'] ?? '',
            'booking_engine_password' => $this->submission_data['booking_engine_password'] ?? '',
            'booking_engine_contact_email' => $this->submission_data['booking_engine_contact_email'] ?? '',
            'technical_objective' => $this->submission_data['technical_objective'] ?? '',
            
            // Step 3: Reporting Information
            'google_analytics_account' => $this->submission_data['google_analytics_account'] ?? '',
            'google_analytics_account_id' => $this->submission_data['google_analytics_account_id'] ?? '',
            'google_tag_manager_account' => $this->submission_data['google_tag_manager_account'] ?? '',
            'google_tag_manager_admin' => $this->submission_data['google_tag_manager_admin'] ?? '',
            'google_ads_account' => $this->submission_data['google_ads_account'] ?? '',
            'google_ads_admin' => $this->submission_data['google_ads_admin'] ?? '',
            'google_ads_customer_id' => $this->submission_data['google_ads_customer_id'] ?? '',
            'meta_business_manager_account' => $this->submission_data['meta_business_manager_account'] ?? '',
            'meta_business_manager_admin' => $this->submission_data['meta_business_manager_admin'] ?? '',
            'meta_business_manager_id' => $this->submission_data['meta_business_manager_id'] ?? '',
            'paid_media_history' => $this->submission_data['paid_media_history'] ?? '',
            'paid_media_history_other' => $this->submission_data['paid_media_history_other'] ?? '',
            'current_paid_media' => $this->submission_data['current_paid_media'] ?? '',
            'current_paid_media_other' => $this->submission_data['current_paid_media_other'] ?? '',
            
            // Step 4: Marketing Information
            'main_objective' => $this->submission_data['main_objective'] ?? '',
            'brand_focus' => $this->submission_data['brand_focus'] ?? '',
            'commercial_objective' => $this->submission_data['commercial_objective'] ?? '',
            'push_impact' => $this->submission_data['push_impact'] ?? '',
            'founder_inspiration' => $this->submission_data['founder_inspiration'] ?? '',
            'brand_tone_mission' => $this->submission_data['brand_tone_mission'] ?? '',
            'brand_perception' => $this->submission_data['brand_perception'] ?? '',
            'global_team_introduction' => $this->submission_data['global_team_introduction'] ?? '',
            'service_introduction' => $this->submission_data['service_introduction'] ?? '',
            'brand_line_1' => $this->submission_data['brand_line_1'] ?? '',
            'mission_1' => $this->submission_data['mission_1'] ?? '',
            'brand_line_2' => $this->submission_data['brand_line_2'] ?? '',
            'mission_2' => $this->submission_data['mission_2'] ?? '',
            'brand_line_3' => $this->submission_data['brand_line_3'] ?? '',
            'mission_3' => $this->submission_data['mission_3'] ?? '',
            'current_website' => $this->submission_data['current_website'] ?? '',
            'brand_guidelines_upload' => $this->submission_data['brand_guidelines_upload'] ?? '',
            'brand_guidelines_files' => $this->submission_data['brand_guidelines_files'] ?? '',
            'communication_tone' => $this->submission_data['communication_tone'] ?? '',
            'casual_tone_explanation' => $this->submission_data['casual_tone_explanation'] ?? '',
            'formal_tone_explanation' => $this->submission_data['formal_tone_explanation'] ?? '',
            'brand_accounts' => $this->submission_data['brand_accounts'] ?? '',
            'facebook_page' => $this->submission_data['facebook_page'] ?? '',
            'instagram_username' => $this->submission_data['instagram_username'] ?? '',
            'industry_entities' => $this->submission_data['industry_entities'] ?? '',
            'industry_entities_other' => $this->submission_data['industry_entities_other'] ?? '',
            'industry_status' => $this->submission_data['industry_status'] ?? '',
            'marketing_goals' => $this->submission_data['marketing_goals'] ?? '',
            'marketing_goals_other' => $this->submission_data['marketing_goals_other'] ?? '',
            'industry' => $this->submission_data['industry'] ?? '',
            'industry_other' => $this->submission_data['industry_other'] ?? '',
            'market_insights' => $this->submission_data['market_insights'] ?? '',
            'content_social_media' => $this->submission_data['content_social_media'] ?? '',
            'business_focus_elements' => $this->submission_data['business_focus_elements'] ?? '',
            'social_media_accounts' => $this->submission_data['social_media_accounts'] ?? '',
            'facebook_accounts_url' => $this->submission_data['facebook_accounts_url'] ?? '',
            'facebook_page_url' => $this->submission_data['facebook_page_url'] ?? '',
            'twitter_accounts_url' => $this->submission_data['twitter_accounts_url'] ?? '',
            'instagram_page_url' => $this->submission_data['instagram_page_url'] ?? '',
            'ideal_customer_description' => $this->submission_data['ideal_customer_description'] ?? '',
            'potential_client_view' => $this->submission_data['potential_client_view'] ?? '',
            'target_age_range' => $this->submission_data['target_age_range'] ?? '',
            'problems_solved' => $this->submission_data['problems_solved'] ?? '',
            'business_challenges' => $this->submission_data['business_challenges'] ?? '',
            'tracking_accounting' => $this->submission_data['tracking_accounting'] ?? '',
            'additional_information' => $this->submission_data['additional_information'] ?? '',
            
            // Links
            'view_submission_link' => admin_url('admin.php?page=cob-submissions&view=' . $this->submission_id),
            'admin_dashboard_link' => admin_url('admin.php?page=client-onboarding'),
        ];

        // Add billing address (for backward compatibility)
        if (!empty($this->submission_data['address_line_1'])) {
            $address_parts = array_filter([
                $this->submission_data['address_line_1'],
                $this->submission_data['address_line_2'],
                $this->submission_data['city'],
                $this->submission_data['country'],
                $this->submission_data['postal_code']
            ]);
            $tags['billing_address'] = implode(', ', $address_parts);
        } else {
            $tags['billing_address'] = '';
        }

        return apply_filters('cob_email_merge_tags', $tags, $this->submission_data, $this->submission_id);
    }

    /**
     * Parse conditional merge tags like {if:field_name}content{/if}
     */
    private function parse_conditional_tags($template) {
        // Pattern to match {if:field_name}content{/if}
        $pattern = '/\{if:([^}]+)\}(.*?)\{\/if\}/s';
        
        return preg_replace_callback($pattern, function($matches) {
            $field_name = $matches[1];
            $content = $matches[2];
            
            // Check if field has value
            if (!empty($this->submission_data[$field_name])) {
                return $content;
            }
            
            return '';
        }, $template);
    }

    /**
     * Get admin email subject
     */
    private function get_admin_email_subject() {
        return $this->settings['admin_email_subject'] ?? 
               'New Client Onboarding Submission - {business_name}';
    }

    /**
     * Get admin email body
     */
    private function get_admin_email_body() {
        if (!empty($this->settings['admin_email_body'])) {
            return $this->settings['admin_email_body'];
        }

        return $this->get_default_admin_email_template();
    }

    /**
     * Get client email subject
     */
    private function get_client_email_subject() {
        return $this->settings['client_email_subject'] ?? 
               'Thank you for your submission - {project_name}';
    }

    /**
     * Get client email body
     */
    private function get_client_email_body() {
        if (!empty($this->settings['client_email_body'])) {
            return $this->settings['client_email_body'];
        }

        return $this->get_default_client_email_template();
    }

    /**
     * Get technical email subject
     */
    private function get_technical_email_subject() {
        return $this->settings['technical_email_subject'] ?? 
               'Technical Information Required - {project_name}';
    }

    /**
     * Get technical email body
     */
    private function get_technical_email_body() {
        if (!empty($this->settings['technical_email_body'])) {
            return $this->settings['technical_email_body'];
        }

        return $this->get_default_technical_email_template();
    }

    /**
     * Get reporting email subject
     */
    private function get_reporting_email_subject() {
        return $this->settings['reporting_email_subject'] ?? 
               'Reporting Setup Required - {project_name}';
    }

    /**
     * Get reporting email body
     */
    private function get_reporting_email_body() {
        if (!empty($this->settings['reporting_email_body'])) {
            return $this->settings['reporting_email_body'];
        }

        return $this->get_default_reporting_email_template();
    }

    /**
     * Default admin email template
     */
    private function get_default_admin_email_template() {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; background-color: #f9f9f9; padding: 20px;">
            <div style="background-color: #1a1a1a; color: #ffffff; padding: 20px; text-align: center;">
                <h1 style="margin: 0; font-size: 24px; letter-spacing: 2px;">FLUX</h1>
                <p style="margin: 10px 0 0 0; color: #9dff00; font-size: 14px; letter-spacing: 1px;">NEW CLIENT ONBOARDING SUBMISSION</p>
            </div>
            
            <div style="background-color: #ffffff; padding: 30px;">
                <h2 style="color: #1a1a1a; margin-top: 0;">New Submission Details</h2>
                
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Submission ID:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{submission_id}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Submitted:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{submission_date}</td>
                    </tr>
                </table>

                <div style="background-color: #9dff00; padding: 15px; border-radius: 4px; margin: 20px 0;">
                    <p style="margin: 0; color: #000; font-weight: bold;">
                        <a href="{view_submission_link}" style="color: #000; text-decoration: none;">
                            → View Full Submission Details
                        </a>
                    </p>
                </div>

                <h3 style="color: #1a1a1a; border-bottom: 2px solid #9dff00; padding-bottom: 10px;">STEP 1: CLIENT INFORMATION</h3>
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Project Name:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{project_name}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Business Name:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{business_name}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Primary Contact Name:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{primary_contact_name}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Primary Contact Email:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><a href="mailto:{primary_contact_email}">{primary_contact_email}</a></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Primary Contact Number:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{primary_contact_number}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Main Approver of Milestones:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{main_approver}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Billing Email Address:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><a href="mailto:{billing_email}">{billing_email}</a></td>
                    </tr>
                    {if:vat_number}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>VAT Number:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{vat_number}</td>
                    </tr>
                    {/if}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Preferred Contact Method:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{preferred_contact_method}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Address Line 1:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{address_line_1}</td>
                    </tr>
                    {if:address_line_2}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Address Line 2:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{address_line_2}</td>
                    </tr>
                    {/if}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>City:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{city}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Country:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{country}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Postal Code:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{postal_code}</td>
                    </tr>
                </table>

                <h3 style="color: #1a1a1a; border-bottom: 2px solid #9dff00; padding-bottom: 10px;">STEP 2: TECHNICAL INFORMATION</h3>
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    {if:current_cms}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Current CMS:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{current_cms}</td>
                    </tr>
                    {/if}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Website Hosting Company:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{website_hosting_company}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Website Contact Email:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><a href="mailto:{website_contact_email}">{website_contact_email}</a></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Domain Hosting Company:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{domain_hosting_company}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Domain Contact Email:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><a href="mailto:{domain_contact_email}">{domain_contact_email}</a></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>CMS Backend Link:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><a href="{cms_link}" target="_blank">{cms_link}</a></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>CMS Username:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{cms_username}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>CMS Password:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">[PROTECTED]</td>
                    </tr>
                    {if:current_crm}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>CRM Integration:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{current_crm}</td>
                    </tr>
                    {/if}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>3rd Party Integrations:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{third_party_integrations}</td>
                    </tr>
                    {if:third_party_name}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>3rd Party Integration Name:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{third_party_name}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>3rd Party Contact:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{third_party_contact_number} / {third_party_contact_email}</td>
                    </tr>
                    {/if}
                    {if:booking_engine_name}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Booking Engine:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{booking_engine_name}</td>
                    </tr>
                    {/if}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Technical Objective:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{technical_objective}</td>
                    </tr>
                </table>

                <h3 style="color: #1a1a1a; border-bottom: 2px solid #9dff00; padding-bottom: 10px;">STEP 3: REPORTING INFORMATION</h3>
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Main Objective:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{main_objective}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Google Analytics Account:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{google_analytics_account}</td>
                    </tr>
                    {if:google_analytics_account_id}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>GA Account ID:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{google_analytics_account_id}</td>
                    </tr>
                    {/if}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Google Tag Manager Account:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{google_tag_manager_account}</td>
                    </tr>
                    {if:google_tag_manager_admin}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>GTM Admin:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{google_tag_manager_admin}</td>
                    </tr>
                    {/if}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Google Ads Account:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{google_ads_account}</td>
                    </tr>
                    {if:google_ads_admin}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Google Ads Admin:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{google_ads_admin}</td>
                    </tr>
                    {/if}
                    {if:google_ads_customer_id}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Google Ads Customer ID:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{google_ads_customer_id}</td>
                    </tr>
                    {/if}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Meta Business Manager:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{meta_business_manager_account}</td>
                    </tr>
                    {if:meta_business_manager_admin}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Meta BM Admin:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{meta_business_manager_admin}</td>
                    </tr>
                    {/if}
                    {if:meta_business_manager_id}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Meta BM ID:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{meta_business_manager_id}</td>
                    </tr>
                    {/if}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Paid Media History:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{paid_media_history}</td>
                    </tr>
                    {if:paid_media_history_other}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Other Paid Media Specify:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{paid_media_history_other}</td>
                    </tr>
                    {/if}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Current Paid Media:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{current_paid_media}</td>
                    </tr>
                    {if:current_paid_media_other}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Other Current Paid Media Specify:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{current_paid_media_other}</td>
                    </tr>
                    {/if}
                </table>

                <h3 style="color: #1a1a1a; border-bottom: 2px solid #9dff00; padding-bottom: 10px;">STEP 4: MARKETING INFORMATION</h3>
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Main Objective:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{main_objective}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Brand Focus:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{brand_focus}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Commercial Objective:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{commercial_objective}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Push Impact:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{push_impact}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Founder Inspiration:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{founder_inspiration}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Brand Tone & Mission:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{brand_tone_mission}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Brand Perception:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{brand_perception}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Global Team Intro:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{global_team_introduction}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Service Introduction:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{service_introduction}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Brand Lines & Missions:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">
                            <strong>1:</strong> {brand_line_1} - {mission_1}<br>
                            <strong>2:</strong> {brand_line_2} - {mission_2}<br>
                            <strong>3:</strong> {brand_line_3} - {mission_3}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Current Website:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><a href="{current_website}" target="_blank">{current_website}</a></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Brand Guidelines Upload:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{brand_guidelines_upload}</td>
                    </tr>
                    {if:brand_guidelines_upload}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Brand Guidelines Files:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{brand_guidelines_upload}</td>
                    </tr>
                    {/if}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Communication Tone:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{communication_tone}</td>
                    </tr>
                    {if:casual_tone_explanation}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Casual Tone Explanation:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{casual_tone_explanation}</td>
                    </tr>
                    {/if}
                    {if:formal_tone_explanation}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Formal Tone Explanation:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{formal_tone_explanation}</td>
                    </tr>
                    {/if}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Brand Accounts:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{brand_accounts}</td>
                    </tr>

                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Industry Entities:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{industry_entities}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Industry Entities Other:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{industry_entities_other}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Industry Status:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{industry_status}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Marketing Goals:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{marketing_goals}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Marketing Goals Other:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{marketing_goals_other}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Industry:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{industry}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Industry Other:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{industry_other}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Market Insights:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{market_insights}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Content/Social Media:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{content_social_media}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Business Focus Elements:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{business_focus_elements}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Target Age Range:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{target_age_range}</td>
                    </tr>

                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Problems Solved:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{problems_solved}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Business Challenges:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{business_challenges}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Tracking/Accounting:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{tracking_accounting}</td>
                    </tr>
                    {if:additional_information}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Additional Information:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{additional_information}</td>
                    </tr>
                    {/if}
                </table>

                <h3 style="color: #1a1a1a; border-bottom: 2px solid #9dff00; padding-bottom: 10px;">SOCIAL MEDIA ACCOUNTS</h3>
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    {if:facebook_page_url}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Facebook Page URL:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><a href="{facebook_page_url}" target="_blank">{facebook_page_url}</a></td>
                    </tr>
                    {/if}
                    {if:instagram_page_url}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Instagram Page URL:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><a href="{instagram_page_url}" target="_blank">{instagram_page_url}</a></td>
                    </tr>
                    {/if}
                    {if:twitter_accounts_url}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Twitter Account URL:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><a href="{twitter_accounts_url}" target="_blank">{twitter_accounts_url}</a></td>
                    </tr>
                    {/if}
                </table>
            </div>
            
            <div style="background-color: #1a1a1a; color: #888; padding: 20px; text-align: center; font-size: 12px;">
                <p style="margin: 0;">This email was sent from {site_name} client onboarding system.</p>
                <p style="margin: 5px 0 0 0;"><a href="{admin_dashboard_link}" style="color: #9dff00;">View Admin Dashboard</a></p>
            </div>
        </div>';
    }

    /**
     * Default client confirmation email template
     */
    private function get_default_client_email_template() {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #f9f9f9; padding: 20px;">
            <div style="background-color: #1a1a1a; color: #ffffff; padding: 20px; text-align: center;">
                <h1 style="margin: 0; font-size: 24px; letter-spacing: 2px;">FLUX</h1>
                <p style="margin: 10px 0 0 0; color: #9dff00; font-size: 14px; letter-spacing: 1px;">SUBMISSION CONFIRMATION</p>
            </div>
            
            <div style="background-color: #ffffff; padding: 30px;">
                <h2 style="color: #1a1a1a; margin-top: 0;">Thank You, {primary_contact_name}!</h2>
                
                <p style="font-size: 16px; line-height: 1.6; color: #333;">
                    We have successfully received your client onboarding form for <strong>{project_name}</strong>. 
                    Our team will review your information and get back to you within 24-48 hours.
                </p>

                <div style="background-color: #9dff00; padding: 20px; border-radius: 4px; margin: 20px 0; text-align: center;">
                    <h3 style="margin: 0 0 10px 0; color: #000;">Submission Details</h3>
                    <p style="margin: 0; color: #000;"><strong>Submission ID:</strong> {submission_id}</p>
                    <p style="margin: 5px 0 0 0; color: #000;"><strong>Submitted:</strong> {submission_date}</p>
                </div>

                <h3 style="color: #1a1a1a;">What Happens Next?</h3>
                <ol style="line-height: 1.8; color: #333;">
                    <li><strong>Review Process:</strong> Our team will carefully review all the information you provided</li>
                    <li><strong>Initial Consultation:</strong> We\'ll schedule a call to discuss your project in detail</li>
                    <li><strong>Proposal:</strong> You\'ll receive a customized proposal based on your requirements</li>
                    <li><strong>Project Kickoff:</strong> Once approved, we\'ll begin working on your project</li>
                </ol>

                <h3 style="color: #1a1a1a;">Your Project Summary</h3>
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Business:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{business_name}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Project:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{project_name}</td>
                    </tr>
                    {if:current_website}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Current Website:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><a href="{current_website}" target="_blank">{current_website}</a></td>
                    </tr>
                    {/if}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Preferred Contact:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{preferred_contact_method}</td>
                    </tr>
                </table>

                <div style="background-color: #f8f9fa; padding: 20px; border-left: 3px solid #9dff00; margin: 20px 0;">
                    <h4 style="margin: 0 0 10px 0; color: #1a1a1a;">Need to Make Changes?</h4>
                    <p style="margin: 0; color: #666;">
                        If you need to update any information or have questions about your submission, 
                        please reply to this email or contact us directly.
                    </p>
                </div>
            </div>
            
            <div style="background-color: #1a1a1a; color: #888; padding: 20px; text-align: center; font-size: 12px;">
                <p style="margin: 0;">Thank you for choosing FLUX for your digital needs.</p>
                <p style="margin: 5px 0 0 0;">Visit us at <a href="{site_url}" style="color: #9dff00;">{site_url}</a></p>
            </div>
        </div>';
    }

    /**
     * Default technical email template
     */
    private function get_default_technical_email_template() {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #f9f9f9; padding: 20px;">
            <div style="background-color: #1a1a1a; color: #ffffff; padding: 20px; text-align: center;">
                <h1 style="margin: 0; font-size: 24px; letter-spacing: 2px;">FLUX</h1>
                <p style="margin: 10px 0 0 0; color: #9dff00; font-size: 14px; letter-spacing: 1px;">TECHNICAL COORDINATION REQUIRED</p>
            </div>
            
            <div style="background-color: #ffffff; padding: 30px;">
                <h2 style="color: #1a1a1a; margin-top: 0;">Hello {technical_contact_name},</h2>
                
                <p style="font-size: 16px; line-height: 1.6; color: #333;">
                    You have been designated as the technical contact for the <strong>{project_name}</strong> project 
                    at <strong>{business_name}</strong>. We may need to coordinate with you regarding technical aspects 
                    of this project.
                </p>

                <div style="background-color: #9dff00; padding: 20px; border-radius: 4px; margin: 20px 0;">
                    <h3 style="margin: 0 0 10px 0; color: #000;">Project Information</h3>
                    <p style="margin: 0; color: #000;"><strong>Business:</strong> {business_name}</p>
                    <p style="margin: 5px 0 0 0; color: #000;"><strong>Project:</strong> {project_name}</p>
                    <p style="margin: 5px 0 0 0; color: #000;"><strong>Primary Contact:</strong> {primary_contact_name}</p>
                </div>

                <h3 style="color: #1a1a1a;">Technical Details</h3>
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    {if:current_website}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Current Website:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><a href="{current_website}" target="_blank">{current_website}</a></td>
                    </tr>
                    {/if}
                    {if:hosting_provider}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Hosting Provider:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{hosting_provider}</td>
                    </tr>
                    {/if}
                    {if:preferred_cms}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Preferred CMS:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{preferred_cms}</td>
                    </tr>
                    {/if}
                    {if:technology_stack}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Technology Stack:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{technology_stack}</td>
                    </tr>
                    {/if}
                </table>

                <p style="color: #666; font-style: italic;">
                    We will be in touch soon to discuss the technical requirements and next steps for this project.
                </p>
            </div>
            
            <div style="background-color: #1a1a1a; color: #888; padding: 20px; text-align: center; font-size: 12px;">
                <p style="margin: 0;">This email was sent from {site_name} client onboarding system.</p>
            </div>
        </div>';
    }

    /**
     * Default reporting email template
     */
    private function get_default_reporting_email_template() {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #f9f9f9; padding: 20px;">
            <div style="background-color: #1a1a1a; color: #ffffff; padding: 20px; text-align: center;">
                <h1 style="margin: 0; font-size: 24px; letter-spacing: 2px;">FLUX</h1>
                <p style="margin: 10px 0 0 0; color: #9dff00; font-size: 14px; letter-spacing: 1px;">REPORTING SETUP NOTIFICATION</p>
            </div>
            
            <div style="background-color: #ffffff; padding: 30px;">
                <h2 style="color: #1a1a1a; margin-top: 0;">Hello {reporting_contact_name},</h2>
                
                <p style="font-size: 16px; line-height: 1.6; color: #333;">
                    You have been designated as the reporting contact for the <strong>{project_name}</strong> project 
                    at <strong>{business_name}</strong>. We will be setting up regular reporting based on your preferences.
                </p>

                <div style="background-color: #9dff00; padding: 20px; border-radius: 4px; margin: 20px 0;">
                    <h3 style="margin: 0 0 10px 0; color: #000;">Reporting Preferences</h3>
                    <p style="margin: 0; color: #000;"><strong>Frequency:</strong> {reporting_frequency}</p>
                    {if:key_metrics}<p style="margin: 5px 0 0 0; color: #000;"><strong>Key Metrics:</strong> {key_metrics}</p>{/if}
                </div>

                <h3 style="color: #1a1a1a;">What to Expect</h3>
                <ul style="line-height: 1.8; color: #333;">
                    <li>Regular reports delivered according to your specified frequency</li>
                    <li>Detailed analytics and performance metrics</li>
                    <li>Actionable insights and recommendations</li>
                    <li>Direct access to our reporting dashboard (if requested)</li>
                </ul>

                <p style="color: #666; font-style: italic;">
                    We will contact you soon to finalize the reporting setup and provide access credentials if needed.
                </p>
            </div>
            
            <div style="background-color: #1a1a1a; color: #888; padding: 20px; text-align: center; font-size: 12px;">
                <p style="margin: 0;">This email was sent from {site_name} client onboarding system.</p>
            </div>
        </div>';
    }

    /**
     * Test email functionality
     */
    public function test_email() {
        check_ajax_referer('cob_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $email_type = sanitize_text_field($_POST['email_type'] ?? 'admin');
        $test_email = sanitize_email($_POST['test_email'] ?? '');

        if (empty($test_email)) {
            wp_send_json_error('Please provide a test email address');
        }

        // Create dummy submission data for testing
        $this->submission_id = 999999;
        $this->submission_data = [
            'business_name' => 'Test Business Inc.',
            'project_name' => 'Test Project',
            'primary_contact_name' => 'John Doe',
            'primary_contact_email' => $test_email,
            'primary_contact_phone' => '+1 (555) 123-4567',
            'current_website' => 'https://example.com',
            'technical_contact_name' => 'Jane Smith',
            'technical_contact_email' => 'jane@example.com',
            'reporting_contact_name' => 'Bob Johnson',
            'reporting_contact_email' => 'bob@example.com',
            'preferred_cms' => 'WordPress',
            'reporting_frequency' => 'Monthly',
            'target_audience' => 'Small to medium businesses looking for digital solutions',
            'marketing_goals' => 'Increase brand awareness and generate qualified leads',
            'session_id' => 'test_session_123'
        ];

        $subject = '';
        $message = '';
        $headers = $this->get_email_headers($email_type);

        switch ($email_type) {
            case 'admin':
                $subject = $this->parse_template($this->get_admin_email_subject());
                $message = $this->parse_template($this->get_admin_email_body());
                break;
            case 'client':
                $subject = $this->parse_template($this->get_client_email_subject());
                $message = $this->parse_template($this->get_client_email_body());
                break;
            case 'technical':
                $subject = $this->parse_template($this->get_technical_email_subject());
                $message = $this->parse_template($this->get_technical_email_body());
                break;
            case 'reporting':
                $subject = $this->parse_template($this->get_reporting_email_subject());
                $message = $this->parse_template($this->get_reporting_email_body());
                break;
        }

        $sent = wp_mail($test_email, '[TEST] ' . $subject, $message, $headers);

        if ($sent) {
            wp_send_json_success('Test email sent successfully to ' . $test_email);
        } else {
            wp_send_json_error('Failed to send test email. Please check your email configuration.');
        }
    }

    /**
     * Send global test notifications to all configured email addresses
     */
    public function test_notifications() {
        check_ajax_referer('cob_test_notifications', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        try {
            // Get admin email recipients
            $to = $this->get_admin_email_recipients();
            
            if (empty($to)) {
                wp_send_json_error('No admin email recipients configured. Please check your settings.');
            }
            
            // Create dummy submission data for testing
            $this->submission_id = 999999;
            $this->submission_data = [
                'business_name' => 'Test Business Inc.',
                'project_name' => 'Test Project',
                'primary_contact_name' => 'John Doe',
                'primary_contact_email' => 'test@example.com',
                'primary_contact_number' => '+1 (555) 123-4567',
                'current_website' => 'https://example.com',
                'session_id' => 'test_session_123'
            ];
            
            // Get email content
            $subject = $this->parse_template($this->get_admin_email_subject());
            $message = $this->parse_template($this->get_admin_email_body());
            $headers = $this->get_email_headers('admin');
            
            // Add test indicator
            $subject = '[GLOBAL TEST] ' . $subject;
            $message = '<div style="background-color: #ffffcc; border: 1px solid #ffcc00; padding: 10px; margin-bottom: 20px; border-radius: 4px;"><strong>GLOBAL TEST EMAIL:</strong> This is a global test email sent from the submissions listing page.</div>' . $message;
            
            // Send to all recipients
            $success_count = 0;
            $failed_recipients = [];
            
            foreach ($to as $recipient) {
                $sent = wp_mail($recipient, $subject, $message, $headers);
                if ($sent) {
                    $success_count++;
                } else {
                    $failed_recipients[] = $recipient;
                }
            }
            
            // Log the test
            if (class_exists('COB_Database')) {
                COB_Database::log_activity(
                    'global_test_notifications_sent',
                    null,
                    'test',
                    "Global test notifications sent. Success: $success_count, Failed: " . count($failed_recipients)
                );
            }
            
            if ($success_count > 0) {
                $message = "Test notifications sent successfully to $success_count recipient(s)";
                if (!empty($failed_recipients)) {
                    $message .= ". Failed to send to: " . implode(', ', $failed_recipients);
                }
                wp_send_json_success($message);
            } else {
                wp_send_json_error('Failed to send test notifications to any recipients.');
            }
            
        } catch (Exception $e) {
            wp_send_json_error('Global test notifications failed with error: ' . $e->getMessage());
        }
    }

    /**
     * Send test notification for a specific submission
     */
    public function test_notification() {
        check_ajax_referer('cob_test_notification', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $submission_id = intval($_POST['submission_id'] ?? 0);
        
        if (!$submission_id) {
            wp_send_json_error('Invalid submission ID');
        }

        // Get the submission data
        $submission = COB_Database::get_submission($submission_id);
        
        if (!$submission) {
            wp_send_json_error('Submission not found');
        }

        // Convert submission object to array
        $submission_data = (array) $submission;
        
        // Set up the email notification system
        $this->submission_id = $submission_id;
        $this->submission_data = $submission_data;
        
        try {
            // Get admin email recipients
            $to = $this->get_admin_email_recipients();
            
            if (empty($to)) {
                wp_send_json_error('No admin email recipients configured. Please check your settings.');
            }
            
            // Get email content
            $subject = $this->parse_template($this->get_admin_email_subject());
            $message = $this->parse_template($this->get_admin_email_body());
            $headers = $this->get_email_headers('admin');
            
            // Add test indicator
            $subject = '[TEST] ' . $subject;
            $message = '<div style="background-color: #ffffcc; border: 1px solid #ffcc00; padding: 10px; margin-bottom: 20px; border-radius: 4px;"><strong>TEST EMAIL:</strong> This is a test email sent from the submissions listing page using submission #' . $submission_id . ' data.</div>' . $message;
            
            // Send the email
            $sent = wp_mail($to, $subject, $message, $headers);
            
            // Log the test email attempt
            if (class_exists('COB_Database')) {
                COB_Database::log_activity(
                    $sent ? 'test_email_sent' : 'test_email_failed',
                    $submission_id,
                    $submission_data['session_id'] ?? 'test',
                    'Test email to: ' . (is_array($to) ? implode(', ', $to) : $to)
                );
            }
            
            if ($sent) {
                $recipient_list = is_array($to) ? implode(', ', $to) : $to;
                wp_send_json_success("Test notification sent successfully to: $recipient_list");
            } else {
                wp_send_json_error('Failed to send test notification. Please check your email configuration.');
            }
            
        } catch (Exception $e) {
            wp_send_json_error('Test email failed with error: ' . $e->getMessage());
        }
    }

    /**
     * Send test notification with real submission data
     */
    public function send_test_notification() {
        check_ajax_referer('cob_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $submission_id = intval($_POST['submission_id'] ?? 0);
        
        if (!$submission_id) {
            wp_send_json_error('Invalid submission ID');
        }

        $submission = COB_Database::get_submission($submission_id);
        
        if (!$submission) {
            wp_send_json_error('Submission not found');
        }

        // Convert submission object to array
        $submission_data = (array) $submission;
        
        // Set up the email notification system
        $this->submission_id = $submission_id;
        $this->submission_data = $submission_data;
        
        try {
            // Debug: Log current settings
            error_log('COB: Test email - Current settings: ' . print_r($this->settings, true));
            
            // Get admin email recipients
            $to = $this->get_admin_email_recipients();
            error_log('COB: Test email - Admin recipients: ' . print_r($to, true));
            
            if (empty($to)) {
                wp_send_json_error('No admin email recipients configured. Please check your settings.');
            }
            
            // Get email content
            $subject = $this->parse_template($this->get_admin_email_subject());
            $message = $this->parse_template($this->get_admin_email_body());
            $headers = $this->get_email_headers('admin');
            
            // Prepare file attachments
            $attachments = $this->prepare_file_attachments();
            
            error_log('COB: Test email - Subject: ' . $subject);
            error_log('COB: Test email - Message length: ' . strlen($message));
            error_log('COB: Test email - Headers: ' . print_r($headers, true));
            error_log('COB: Test email - Attachments: ' . count($attachments));
            
            // Add test indicator
            $subject = '[TEST] ' . $subject;
            $message = '<div style="background-color: #ffffcc; border: 1px solid #ffcc00; padding: 10px; margin-bottom: 20px; border-radius: 4px;"><strong>TEST EMAIL:</strong> This is a test email sent from the submission detail view.</div>' . $message;
            
            // Send the email
            $sent = wp_mail($to, $subject, $message, $headers, $attachments);
            error_log('COB: Test email - wp_mail result: ' . ($sent ? 'SUCCESS' : 'FAILED'));
            
            // Log the test email attempt
            if (class_exists('COB_Database')) {
                COB_Database::log_activity(
                    $sent ? 'test_email_sent' : 'test_email_failed',
                    $submission_id,
                    $submission_data['session_id'] ?? 'test',
                    'Test email to: ' . (is_array($to) ? implode(', ', $to) : $to)
                );
            }
            
            if ($sent) {
                $recipient_list = is_array($to) ? implode(', ', $to) : $to;
                wp_send_json_success("Test notification sent successfully to: $recipient_list");
            } else {
                wp_send_json_error('Failed to send test notification. Please check your email configuration.');
            }
            
        } catch (Exception $e) {
            wp_send_json_error('Test email failed with error: ' . $e->getMessage());
        }
    }

    /**
     * Get available merge tags for template editor
     */
    public static function get_available_merge_tags() {
        return [
            'Submission Info' => [
                '{submission_id}' => 'Submission ID',
                '{submission_date}' => 'Submission Date',
                '{submission_time}' => 'Submission Time',
            ],
            'Site Info' => [
                '{site_name}' => 'Site Name',
                '{site_url}' => 'Site URL',
                '{admin_email}' => 'Admin Email',
            ],
            'Client Information' => [
                '{business_name}' => 'Business Name',
                '{project_name}' => 'Project Name',
                '{primary_contact_name}' => 'Primary Contact Name',
                '{primary_contact_email}' => 'Primary Contact Email',
                '{primary_contact_phone}' => 'Primary Contact Phone',
                '{milestone_approver}' => 'Milestone Approver',
                '{billing_email}' => 'Billing Email',
                '{preferred_contact_method}' => 'Preferred Contact Method',
                '{billing_address}' => 'Billing Address',
            ],
            'Technical Information' => [
                '{current_website}' => 'Current Website',
                '{hosting_provider}' => 'Hosting Provider',
                '{technical_contact_name}' => 'Technical Contact Name',
                '{technical_contact_email}' => 'Technical Contact Email',
                '{preferred_cms}' => 'Preferred CMS',
                '{technology_stack}' => 'Technology Stack',
            ],
            'Reporting Information' => [
                '{reporting_frequency}' => 'Reporting Frequency',
                '{reporting_contact_name}' => 'Reporting Contact Name',
                '{reporting_contact_email}' => 'Reporting Contact Email',
                '{key_metrics}' => 'Key Metrics',
            ],
            'Marketing Information' => [
                '{target_audience}' => 'Target Audience',
                '{marketing_goals}' => 'Marketing Goals',
                '{marketing_budget}' => 'Marketing Budget',
                '{current_marketing_channels}' => 'Current Marketing Channels',
            ],
            'Links' => [
                '{view_submission_link}' => 'View Submission Link',
                '{admin_dashboard_link}' => 'Admin Dashboard Link',
            ]
        ];
    }
}