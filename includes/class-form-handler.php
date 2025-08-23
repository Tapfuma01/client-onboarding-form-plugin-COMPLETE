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

            // Sanitize form data
            $sanitized_data = $this->sanitize_form_data($form_data);

            $result = COB_Database::save_draft($session_id, $sanitized_data, $current_step, $client_email);

            if ($result) {
                wp_die(json_encode([
                    'success' => true, 
                    'message' => 'Draft saved successfully',
                    'last_saved' => current_time('mysql')
                ]));
            } else {
                COB_Database::log_activity('draft_save_failed', null, $session_id, 'Database error');
                wp_die(json_encode(['success' => false, 'message' => 'Failed to save draft']));
            }
        } catch (Exception $e) {
            COB_Database::log_activity('draft_save_error', null, $session_id ?? '', 'Exception: ' . $e->getMessage());
            wp_die(json_encode(['success' => false, 'message' => 'Server error occurred']));
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
                'business_name', 'project_name', 'primary_contact_name', 
                'primary_contact_email', 'technical_contact_name', 
                'technical_contact_email', 'reporting_contact_name', 
                'reporting_contact_email'
            ];

            $missing_fields = [];
            foreach ($required_fields as $field) {
                if (empty(trim($form_data[$field] ?? ''))) {
                    $missing_fields[] = $field;
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
            $email_fields = ['primary_contact_email', 'technical_contact_email', 'reporting_contact_email'];
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