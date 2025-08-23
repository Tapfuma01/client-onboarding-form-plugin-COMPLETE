<?php
/**
 * Form handler class for managing form submissions and AJAX requests
 */

if (!defined('ABSPATH')) {
    exit;
}

class COB_Form_Handler {

    public function __construct() {
        // Load email notifications class
        $this->load_email_notifications();
        
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_cob_save_draft', [$this, 'ajax_save_draft']);
        add_action('wp_ajax_nopriv_cob_save_draft', [$this, 'ajax_save_draft']);
        add_action('wp_ajax_cob_get_draft', [$this, 'ajax_get_draft']);
        add_action('wp_ajax_nopriv_cob_get_draft', [$this, 'ajax_get_draft']);
        add_action('wp_ajax_cob_submit_form', [$this, 'ajax_submit_form']);
        add_action('wp_ajax_nopriv_cob_submit_form', [$this, 'ajax_submit_form']);
        add_action('wp_ajax_cob_get_shared_draft', [$this, 'ajax_get_shared_draft']);
        add_action('wp_ajax_nopriv_cob_get_shared_draft', [$this, 'ajax_get_shared_draft']);
        
        // Ensure database class is loaded for AJAX requests
        $this->load_dependencies();
    }

    private function load_email_notifications() {
        if (!class_exists('COB_Email_Notifications')) {
            require_once COB_PLUGIN_PATH . 'includes/class-email-notifications.php';
        }
        new COB_Email_Notifications();
    }

    private function load_dependencies() {
        if (!class_exists('COB_Database')) {
            require_once COB_PLUGIN_PATH . 'includes/class-database.php';
        }
    }

    public function enqueue_scripts() {
        if (is_singular() && has_shortcode(get_post()->post_content, 'client_onboarding_form')) {
            wp_enqueue_style(
                'cob-form-style', 
                COB_PLUGIN_URL . 'assets/css/form-style.css', 
                [], 
                COB_PLUGIN_VERSION
            );
            
            wp_enqueue_script(
                'cob-form-script', 
                COB_PLUGIN_URL . 'assets/js/form-script.js', 
                ['jquery'], 
                COB_PLUGIN_VERSION, 
                true
            );

            wp_localize_script('cob-form-script', 'cob_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cob_form_nonce'),
                'messages' => [
                    'draft_saved' => __('Draft saved successfully!', 'client-onboarding-form'),
                    'submit_success' => __('Form submitted successfully!', 'client-onboarding-form'),
                    'submit_error' => __('Error submitting form. Please try again.', 'client-onboarding-form'),
                    'validation_error' => __('Please fill in all required fields.', 'client-onboarding-form'),
                    'exit_confirm' => __('Are you sure you want to exit? Your progress will be saved as a draft.', 'client-onboarding-form')
                ]
            ]);
        }
    }

    public function ajax_save_draft() {
        try {
            check_ajax_referer('cob_form_nonce', 'nonce');

            $session_id = sanitize_text_field($_POST['session_id'] ?? '');
            $form_data = $_POST['form_data'] ?? [];
            $current_step = intval($_POST['current_step'] ?? 1);
            $client_email = sanitize_email($_POST['client_email'] ?? '');

            if (empty($session_id)) {
                COB_Database::log_activity('draft_save_failed', null, $session_id, 'Invalid session ID');
                wp_die(json_encode(['success' => false, 'message' => 'Invalid session ID']));
            }

            // Check database connection
            global $wpdb;
            if (!$wpdb->check_connection()) {
                COB_Database::log_activity('draft_save_failed', null, $session_id, 'Database connection failed');
                wp_die(json_encode(['success' => false, 'message' => 'Database connection error. Please try again.']));
            }

            // Check for table locks before attempting to save
            COB_Database::check_table_locks();

            // Sanitize form data
            $sanitized_data = $this->sanitize_form_data($form_data);

            // Add a small delay to reduce concurrent access
            if (function_exists('usleep')) {
                usleep(rand(10000, 50000)); // 10-50ms random delay
            }

            $result = COB_Database::save_draft($session_id, $sanitized_data, $current_step, $client_email);

            if ($result) {
                wp_die(json_encode([
                    'success' => true, 
                    'message' => 'Draft saved successfully',
                    'last_saved' => current_time('mysql')
                ]));
            } else {
                // Check if it's a deadlock issue
                $last_error = $wpdb->last_error;
                if (strpos($last_error, 'Deadlock') !== false || 
                    strpos($last_error, 'try restarting transaction') !== false) {
                    
                    COB_Database::log_activity('draft_save_deadlock', null, $session_id, 'Deadlock detected in AJAX handler');
                    
                    wp_die(json_encode([
                        'success' => false, 
                        'message' => 'Temporary database issue. Please try again in a moment.',
                        'retry_after' => 2
                    ]));
                } else {
                    COB_Database::log_activity('draft_save_failed', null, $session_id, 'Database error: ' . $last_error);
                    wp_die(json_encode(['success' => false, 'message' => 'Failed to save draft. Please try again.']));
                }
            }
        } catch (Exception $e) {
            COB_Database::log_activity('draft_save_error', null, $session_id ?? '', 'Exception: ' . $e->getMessage());
            wp_die(json_encode(['success' => false, 'message' => 'Server error occurred. Please try again.']));
        }
    }

    public function ajax_get_draft() {
        check_ajax_referer('cob_form_nonce', 'nonce');

        $session_id = sanitize_text_field($_GET['session_id'] ?? '');

        if (empty($session_id)) {
            wp_die(json_encode(['success' => false, 'message' => 'Invalid session ID']));
        }

        $draft_data = COB_Database::get_draft($session_id);

        wp_die(json_encode([
            'success' => true,
            'form_data' => $draft_data['form_data'] ?? [],
            'current_step' => $draft_data['current_step'] ?? 1,
            'progress_percentage' => $draft_data['progress_percentage'] ?? 0,
            'client_email' => $draft_data['client_email'] ?? ''
        ]));
    }

    public function ajax_submit_form() {
        try {
            // Ensure proper content type
            if (!wp_doing_ajax()) {
                wp_die(json_encode(['success' => false, 'message' => 'Invalid request']));
            }

            check_ajax_referer('cob_form_nonce', 'nonce');

            $form_data = $_POST['form_data'] ?? [];
            $session_id = sanitize_text_field($_POST['session_id'] ?? '');

            // Ensure database class is loaded
            $this->load_dependencies();

            // Log submission attempt (with error handling)
            if (class_exists('COB_Database')) {
                COB_Database::log_activity('submission_attempt', null, $session_id, 'Form submission started');
            }

            // Validate required fields
            $required_fields = [
                // Step 1: Client Information
                'project_name', 'business_name', 'primary_contact_name', 
                'primary_contact_email', 'primary_contact_number', 'main_approver',
                'billing_email', 'preferred_contact_method', 'address_line_1',
                'city', 'country', 'postal_code',
                
                // Step 2: Technical Information
                'website_hosting_company', 'website_contact_email',
                'domain_hosting_company', 'domain_contact_email',
                'cms_link', 'cms_username', 'cms_password',
                'third_party_integrations', 'technical_objective',
                
                // Step 3: Reporting Information
                'google_analytics_account', 'google_tag_manager_account',
                'google_ads_account', 'meta_business_manager_account',
                'paid_media_history', 'current_paid_media',
                
                // Step 4: Marketing Information
                'main_objective', 'brand_focus', 'commercial_objective',
                'push_impact', 'founder_inspiration', 'brand_tone_mission',
                'brand_perception', 'global_team_introduction', 'service_introduction',
                'brand_line_1', 'mission_1', 'brand_line_2', 'mission_2',
                'brand_line_3', 'mission_3', 'brand_guidelines_upload',
                'communication_tone', 'brand_accounts', 'industry_entities',
                'market_insights', 'content_social_media', 'business_focus_elements',
                'ideal_customer_description', 'potential_client_view',
                'target_age_range', 'problems_solved', 'business_challenges',
                'tracking_accounting'
            ];

            // Debug logging for problematic fields
            $debug_fields = ['paid_media_history', 'current_paid_media', 'industry_entities', 'target_age_range'];
            foreach ($debug_fields as $debug_field) {
                if (class_exists('COB_Database')) {
                    $field_value = $form_data[$debug_field] ?? 'NOT_SET';
                    $field_type = is_array($field_value) ? 'ARRAY' : 'STRING';
                    $field_content = is_array($field_value) ? json_encode($field_value) : $field_value;
                    COB_Database::log_activity('field_debug', null, $session_id, 
                        "Field: $debug_field, Type: $field_type, Value: $field_content");
                }
            }

            $missing_fields = [];
            foreach ($required_fields as $field) {
                $field_value = $form_data[$field] ?? '';
                
                // Handle array fields (checkboxes, radio buttons)
                if (is_array($field_value)) {
                    if (empty($field_value) || (count($field_value) === 1 && empty($field_value[0]))) {
                        $missing_fields[] = $field;
                    }
                } else {
                    // Handle string fields
                    if (empty(trim($field_value))) {
                        $missing_fields[] = $field;
                    }
                }
            }

            // Additional validation for specific checkbox array fields
            $checkbox_array_fields = [
                'paid_media_history' => 'Paid Media History',
                'current_paid_media' => 'Current Paid Media',
                'industry_entities' => 'Industry Entities',
                'target_age_range' => 'Target Age Range'
            ];

            foreach ($checkbox_array_fields as $field => $display_name) {
                $field_value = $form_data[$field] ?? '';
                if (!is_array($field_value) || empty($field_value) || (count($field_value) === 1 && empty($field_value[0]))) {
                    if (!in_array($field, $missing_fields)) {
                        $missing_fields[] = $field;
                    }
                }
            }

            if (!empty($missing_fields)) {
                if (class_exists('COB_Database')) {
                    COB_Database::log_activity('submission_validation_failed', null, $session_id, 
                        'Missing fields: ' . implode(', ', $missing_fields));
                }
                wp_die(json_encode([
                    'success' => false,
                    'message' => 'Missing required fields: ' . implode(', ', $missing_fields),
                    'missing_fields' => $missing_fields
                ]));
            }

            // Validate email addresses
            $email_fields = [
                'primary_contact_email', 'billing_email', 'website_contact_email',
                'domain_contact_email', 'third_party_contact_email',
                'booking_engine_contact_email'
            ];
            foreach ($email_fields as $field) {
                if (!empty($form_data[$field]) && !is_email($form_data[$field])) {
                    if (class_exists('COB_Database')) {
                        COB_Database::log_activity('submission_validation_failed', null, $session_id, 
                            'Invalid email: ' . $field);
                    }
                    wp_die(json_encode([
                        'success' => false,
                        'message' => 'Invalid email address in ' . str_replace('_', ' ', $field)
                    ]));
                }
            }

            // Sanitize and prepare data for database
            $submission_data = $this->prepare_submission_data($form_data, $session_id);

            // Log the data being saved (with error handling)
            if (class_exists('COB_Database')) {
                COB_Database::log_activity('submission_processing', null, $session_id, 
                    'Saving data for: ' . ($submission_data['business_name'] ?? 'Unknown'));
            }

            // Ensure database class exists before calling methods
            if (!class_exists('COB_Database')) {
                wp_die(json_encode([
                    'success' => false, 
                    'message' => 'Database system not available. Please contact administrator.'
                ]));
            }

            $submission_id = COB_Database::save_submission($submission_data);

            if ($submission_id) {
                // Delete draft after successful submission
                try {
                    COB_Database::delete_draft($session_id);
                } catch (Exception $e) {
                    // Don't fail submission if draft deletion fails
                }

                // Trigger email notifications
                do_action('cob_after_submission', $submission_id, $submission_data);

                COB_Database::log_activity('submission_completed', $submission_id, $session_id, 
                    'Form submitted successfully');

                wp_die(json_encode([
                    'success' => true,
                    'message' => 'Form submitted successfully',
                    'submission_id' => $submission_id
                ]));
            } else {
                global $wpdb;
                $db_error = $wpdb->last_error;
                if (class_exists('COB_Database')) {
                    COB_Database::log_activity('submission_db_error', null, $session_id, 
                        'Database error: ' . $db_error);
                }
                
                wp_die(json_encode([
                    'success' => false, 
                    'message' => 'Failed to submit form. Please try again.',
                    'debug' => WP_DEBUG ? $db_error : ''
                ]));
            }
        } catch (Exception $e) {
            if (class_exists('COB_Database')) {
                try {
                    COB_Database::log_activity('submission_exception', null, $session_id ?? '', 
                        'Exception: ' . $e->getMessage());
                } catch (Exception $log_error) {
                    // Ignore logging errors
                }
            }
            
            wp_die(json_encode([
                'success' => false, 
                'message' => 'Server error occurred. Please try again.',
                'debug' => WP_DEBUG ? $e->getMessage() : ''
            ]));
        }
    }

    private function sanitize_form_data($data) {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = array_map('sanitize_text_field', $value);
            } else {
                $sanitized[$key] = sanitize_textarea_field($value);
            }
        }

        return $sanitized;
    }

    public function ajax_get_shared_draft() {
        check_ajax_referer('cob_form_nonce', 'nonce');

        $token = sanitize_text_field($_GET['token'] ?? '');

        if (empty($token)) {
            wp_die(json_encode(['success' => false, 'message' => 'Invalid token']));
        }

        $draft_data = COB_Database::get_draft_by_token($token);

        if ($draft_data) {
            // Use the existing session ID from the shared draft
            wp_die(json_encode([
                'success' => true,
                'session_id' => $draft_data['session_id'],
                'form_data' => $draft_data['form_data'],
                'current_step' => $draft_data['current_step'],
                'progress_percentage' => $draft_data['progress_percentage'],
                'client_email' => $draft_data['client_email']
            ]));
        } else {
            wp_die(json_encode(['success' => false, 'message' => 'Draft not found or expired']));
        }
    }

    public function prepare_submission_data($form_data, $session_id) {
        return [
            'session_id' => $session_id,
            'business_name' => $form_data['business_name'] ?? '',
            'project_name' => $form_data['project_name'] ?? '',
            'primary_contact_name' => $form_data['primary_contact_name'] ?? '',
            'primary_contact_email' => sanitize_email($form_data['primary_contact_email'] ?? ''),
            'primary_contact_phone' => $form_data['primary_contact_phone'] ?? '',
            'milestone_approver' => $form_data['milestone_approver'] ?? '',
            'billing_email' => sanitize_email($form_data['billing_email'] ?? ''),
            'vat_number' => $form_data['vat_number'] ?? '',
            'preferred_contact_method' => $form_data['preferred_contact_method'] ?? 'email',
            'billing_address_line1' => $form_data['billing_address_line1'] ?? '',
            'billing_address_line2' => $form_data['billing_address_line2'] ?? '',
            'billing_address_city' => $form_data['billing_address_city'] ?? '',
            'billing_address_country' => $form_data['billing_address_country'] ?? '',
            'billing_address_postal_code' => $form_data['billing_address_postal_code'] ?? '',
            'current_website' => esc_url_raw($form_data['current_website'] ?? ''),
            'hosting_provider' => $form_data['hosting_provider'] ?? '',
            'domain_provider' => $form_data['domain_provider'] ?? '',
            'technical_contact_name' => $form_data['technical_contact_name'] ?? '',
            'technical_contact_email' => sanitize_email($form_data['technical_contact_email'] ?? ''),
            'preferred_cms' => $form_data['preferred_cms'] ?? '',
            'integration_requirements' => $form_data['integration_requirements'] ?? '',
            'technology_stack' => $this->safe_array_implode($form_data['technology_stack'] ?? []),
            'reporting_frequency' => $form_data['reporting_frequency'] ?? '',
            'reporting_format' => $form_data['reporting_format'] ?? '',
            'key_metrics' => $this->safe_array_implode($form_data['key_metrics'] ?? []),
            'reporting_contact_name' => $form_data['reporting_contact_name'] ?? '',
            'reporting_contact_email' => sanitize_email($form_data['reporting_contact_email'] ?? ''),
            'dashboard_access' => $form_data['dashboard_access'] ?? '',
            'additional_reporting_requirements' => $form_data['additional_reporting_requirements'] ?? '',
            'target_audience' => $form_data['target_audience'] ?? '',
            'marketing_goals' => $form_data['marketing_goals'] ?? '',
            'marketing_budget' => $form_data['marketing_budget'] ?? '',
            'current_marketing_channels' => $this->safe_array_implode($form_data['current_marketing_channels'] ?? []),
            'brand_guidelines' => $form_data['brand_guidelines'] ?? '',
            'competitor_analysis' => $form_data['competitor_analysis'] ?? '',
            'marketing_challenges' => $form_data['marketing_challenges'] ?? '',
            'success_metrics' => $form_data['success_metrics'] ?? '',
            'status' => 'submitted',
            'submitted_at' => current_time('mysql')
        ];
    }

    private function safe_array_implode($value) {
        // Handle NULL or empty values
        if (is_null($value) || $value === '') {
            return '';
        }
        
        // Handle arrays
        if (is_array($value)) {
            // Filter out empty values and sanitize each element
            $filtered_array = array_filter($value, function($item) {
                return !is_null($item) && $item !== '';
            });
            
            return implode(',', $filtered_array);
        }
        
        // Handle strings (including when form data sends comma-separated values)
        if (is_string($value)) {
            // If it's already a comma-separated string, return as is
            if (strpos($value, ',') !== false) {
                return $value;
            }
            // If it's a single value, return it as is
            return $value;
        }
        
        // For any other data types, convert to string
        return (string) $value;
    }

}