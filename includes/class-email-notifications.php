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
    }

    /**
     * Main method to send all configured notifications
     */
    public function send_notifications($submission_id, $submission_data) {
        $this->submission_id = $submission_id;
        $this->submission_data = $submission_data;

        // Send admin notification
        if ($this->should_send_admin_notification()) {
            $this->send_admin_notification();
        }

        // Send client confirmation
        if ($this->should_send_client_confirmation()) {
            $this->send_client_confirmation();
        }

        // Send additional notifications if configured
        $this->send_additional_notifications();
    }

    /**
     * Check if admin notification should be sent
     */
    private function should_send_admin_notification() {
        return !empty($this->settings['enable_admin_notification']) && 
               !empty($this->settings['admin_email']);
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

        $sent = wp_mail($to, $subject, $message, $headers);

        // Log the email attempt
        COB_Database::log_activity(
            $sent ? 'admin_email_sent' : 'admin_email_failed',
            $this->submission_id,
            $this->submission_data['session_id'],
            'To: ' . (is_array($to) ? implode(', ', $to) : $to)
        );

        return $sent;
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
        
        // Primary admin email
        if (!empty($this->settings['admin_email'])) {
            $recipients[] = $this->settings['admin_email'];
        }

        // Additional admin emails
        if (!empty($this->settings['additional_admin_emails'])) {
            $additional = array_map('trim', explode(',', $this->settings['additional_admin_emails']));
            $recipients = array_merge($recipients, array_filter($additional, 'is_email'));
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
            
            // Client information
            'business_name' => $this->submission_data['business_name'] ?? '',
            'project_name' => $this->submission_data['project_name'] ?? '',
            'primary_contact_name' => $this->submission_data['primary_contact_name'] ?? '',
            'primary_contact_email' => $this->submission_data['primary_contact_email'] ?? '',
            'primary_contact_phone' => $this->submission_data['primary_contact_phone'] ?? '',
            'milestone_approver' => $this->submission_data['milestone_approver'] ?? '',
            'billing_email' => $this->submission_data['billing_email'] ?? '',
            'preferred_contact_method' => $this->submission_data['preferred_contact_method'] ?? '',
            
            // Technical information
            'current_website' => $this->submission_data['current_website'] ?? '',
            'hosting_provider' => $this->submission_data['hosting_provider'] ?? '',
            'technical_contact_name' => $this->submission_data['technical_contact_name'] ?? '',
            'technical_contact_email' => $this->submission_data['technical_contact_email'] ?? '',
            'preferred_cms' => $this->submission_data['preferred_cms'] ?? '',
            'technology_stack' => $this->submission_data['technology_stack'] ?? '',
            
            // Reporting information
            'reporting_frequency' => $this->submission_data['reporting_frequency'] ?? '',
            'reporting_contact_name' => $this->submission_data['reporting_contact_name'] ?? '',
            'reporting_contact_email' => $this->submission_data['reporting_contact_email'] ?? '',
            'key_metrics' => $this->submission_data['key_metrics'] ?? '',
            
            // Marketing information
            'target_audience' => $this->submission_data['target_audience'] ?? '',
            'marketing_goals' => $this->submission_data['marketing_goals'] ?? '',
            'marketing_budget' => $this->submission_data['marketing_budget'] ?? '',
            'current_marketing_channels' => $this->submission_data['current_marketing_channels'] ?? '',
            
            // Links
            'view_submission_link' => admin_url('admin.php?page=cob-submissions&view=' . $this->submission_id),
            'admin_dashboard_link' => admin_url('admin.php?page=client-onboarding'),
        ];

        // Add billing address
        if (!empty($this->submission_data['billing_address_line1'])) {
            $address_parts = array_filter([
                $this->submission_data['billing_address_line1'],
                $this->submission_data['billing_address_line2'],
                $this->submission_data['billing_address_city'],
                $this->submission_data['billing_address_country'],
                $this->submission_data['billing_address_postal_code']
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
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Current CRM:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{current_crm}</td>
                    </tr>
                    {/if}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>3rd Party Integrations:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{third_party_integrations}</td>
                    </tr>
                    {if:third_party_integrations:yes}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>3rd Party Name:</strong></td>
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
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Google Analytics Account:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{google_analytics_account}</td>
                    </tr>
                    {if:google_analytics_account:yes}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>GA Account ID:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{google_analytics_account_id}</td>
                    </tr>
                    {/if}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Google Tag Manager Account:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{google_tag_manager_account}</td>
                    </tr>
                    {if:google_tag_manager_account:yes}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>GTM Admin:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{google_tag_manager_admin}</td>
                    </tr>
                    {/if}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Google Ads Account:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{google_ads_account}</td>
                    </tr>
                    {if:google_ads_account:yes}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Google Ads Admin:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{google_ads_admin}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Google Ads Customer ID:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{google_ads_customer_id}</td>
                    </tr>
                    {/if}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Meta Business Manager:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{meta_business_manager_account}</td>
                    </tr>
                    {if:meta_business_manager_account:yes}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Meta BM Admin:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{meta_business_manager_admin}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Meta BM ID:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{meta_business_manager_id}</td>
                    </tr>
                    {/if}
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Paid Media History:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{paid_media_history}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Current Paid Media:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{current_paid_media}</td>
                    </tr>
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
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Brand Guidelines Upload:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{brand_guidelines_upload}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Communication Tone:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{communication_tone}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Brand Accounts:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{brand_accounts}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Industry Entities:</strong></td>
                        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">{industry_entities}</td>
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
        
        // Send test notification
        $this->submission_id = $submission_id;
        $this->submission_data = $submission_data;
        
        $sent = $this->send_admin_notification();
        
        if ($sent) {
            wp_send_json_success('Test notification sent successfully');
        } else {
            wp_send_json_error('Failed to send test notification');
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