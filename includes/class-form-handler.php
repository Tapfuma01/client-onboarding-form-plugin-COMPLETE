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

    /**
     * Process file uploads from the form
     * @return array|false Array of uploaded file data or false on failure
     */
    private function process_file_uploads() {
        if (empty($_FILES)) {
            return [];
        }

        $uploaded_files = [];
        $upload_dir = wp_upload_dir();
        $allowed_types = [
            'logo_file' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'brand_guidelines' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'brand_guidelines_upload' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png', 'image/gif', 'image/webp']
        ];

        foreach ($_FILES as $field_name => $file_data) {
            if ($file_data['error'] === UPLOAD_ERR_OK && !empty($file_data['name'])) {
                // Check if this is a file field we want to process
                if (!isset($allowed_types[$field_name])) {
                    continue;
                }

                // Validate file type
                $file_type = wp_check_filetype($file_data['name']);
                if (!in_array($file_type['type'], $allowed_types[$field_name])) {
                    error_log("COB: Invalid file type for $field_name: " . $file_type['type']);
                    continue;
                }

                // Create unique filename
                $filename = wp_unique_filename($upload_dir['path'], $file_data['name']);
                $file_path = $upload_dir['path'] . '/' . $filename;

                // Move uploaded file
                if (move_uploaded_file($file_data['tmp_name'], $file_path)) {
                    // Add to WordPress media library
                    $attachment_id = wp_insert_attachment([
                        'post_title' => sanitize_file_name($file_data['name']),
                        'post_content' => '',
                        'post_status' => 'inherit',
                        'post_mime_type' => $file_type['type']
                    ], $file_path);

                    if (!is_wp_error($attachment_id)) {
                        // Generate attachment metadata
                        wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $file_path));
                        
                        // Store file information
                        $uploaded_files[$field_name] = [
                            'id' => $attachment_id,
                            'url' => wp_get_attachment_url($attachment_id),
                            'name' => $file_data['name'],
                            'type' => $file_type['type'],
                            'size' => $file_data['size']
                        ];

                        error_log("COB: File uploaded successfully for $field_name: " . $uploaded_files[$field_name]['url']);
                    } else {
                        error_log("COB: Failed to create attachment for $field_name: " . $attachment_id->get_error_message());
                    }
                } else {
                    error_log("COB: Failed to move uploaded file for $field_name");
                }
            }
        }

        return $uploaded_files;
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
        // Comprehensive debugging for form submission
        error_log('=== COB FORM SUBMISSION DEBUG START ===');
        error_log('COB: AJAX submit form method called');
        error_log('COB: Request method: ' . $_SERVER['REQUEST_METHOD']);
        error_log('COB: Content type: ' . ($_SERVER['CONTENT_TYPE'] ?? 'Not set'));
        error_log('COB: POST data received: ' . print_r($_POST, true));
        error_log('COB: FILES data received: ' . print_r($_FILES, true));
        error_log('COB: Raw input: ' . file_get_contents('php://input'));
        
        try {
            // Ensure proper content type
            if (!wp_doing_ajax()) {
                error_log('COB: Not an AJAX request');
                wp_die(json_encode(['success' => false, 'message' => 'Invalid request']));
            }

            error_log('COB: AJAX request validated');
            check_ajax_referer('cob_form_nonce', 'nonce');
            error_log('COB: Nonce verification passed');

            // Determine if this is a file upload submission or regular form submission
            $is_file_upload = !empty($_FILES) && isset($_POST['action']) && $_POST['action'] === 'cob_submit_form';
            
            if ($is_file_upload) {
                // File upload submission - data is directly in $_POST
                error_log('COB: Processing file upload submission');
                $form_data = $_POST;
                // Remove action and nonce from form data
                unset($form_data['action']);
                unset($form_data['nonce']);
            } else {
                // Regular form submission - data is in $_POST['form_data']
                error_log('COB: Processing regular form submission');
                $form_data = $_POST['form_data'] ?? [];
            }
            
            $session_id = sanitize_text_field($_POST['session_id'] ?? '');
            
            error_log('COB: Session ID: ' . $session_id);
            error_log('COB: Form data received: ' . print_r($form_data, true));
            error_log('COB: Form data count: ' . count($form_data));

            // Ensure database class is loaded
            $this->load_dependencies();

            // Log submission attempt (with error handling)
            if (class_exists('COB_Database')) {
                COB_Database::log_activity('submission_attempt', null, $session_id, 'Form submission started');
            }

            // Validate required fields - only the ones actually marked as required in the form
            $required_fields = [
                // Step 1: Client Information
                'project_name', 'business_name', 'current_website', 'primary_contact_name', 
                'primary_contact_email', 'primary_contact_number', 'main_approver',
                'billing_email', 'preferred_contact_method', 'address_line_1',
                'city', 'country', 'postal_code', 'has_website',
                
                // Step 2: Technical Information
                'has_google_analytics', 'has_search_console', 'reporting_frequency',
                
                // Step 3: Reporting Information
                'main_objective',
                
                // Step 4: Marketing Information
                'business_description', 'target_audience', 'main_competitors',
                'unique_value_proposition', 'marketing_budget', 'start_timeline',
                
                // Additional required fields
                'brand_guidelines_upload_radio', 'communication_tone_radio', 
                'brand_accounts_radio', 'industry_entities', 'market_insights_radio', 
                'content_social_media_radio', 'business_focus_elements_radio',
                'target_age_range', 'gender_purchase_decision', 'lead_source_markets', 'lead_times'
            ];

            // Debug logging for problematic fields
            $debug_fields = ['marketing_goals', 'industry', 'social_media_platforms'];
            foreach ($debug_fields as $debug_field) {
                if (class_exists('COB_Database')) {
                    $field_value = $form_data[$debug_field] ?? 'NOT_SET';
                    $field_type = is_array($field_value) ? 'ARRAY' : 'STRING';
                    $field_content = is_array($field_value) ? json_encode($field_value) : $field_value;
                    COB_Database::log_activity('field_debug', null, $session_id, 
                        "Field: $debug_field, Type: $field_type, Value: $field_content");
                }
            }

            error_log('COB: Starting field validation...');
            error_log('COB: Required fields to check: ' . implode(', ', $required_fields));
            
            $missing_fields = [];
            foreach ($required_fields as $field) {
                $field_value = $form_data[$field] ?? '';
                $field_type = is_array($field_value) ? 'ARRAY' : 'STRING';
                $field_content = is_array($field_value) ? json_encode($field_value) : $field_value;
                
                error_log("COB: Checking field '$field' - Type: $field_type, Value: '$field_content'");
                
                // Handle array fields (checkboxes, radio buttons)
                if (is_array($field_value)) {
                    if (empty($field_value) || (count($field_value) === 1 && empty($field_value[0]))) {
                        $missing_fields[] = $field;
                        error_log("COB: Field '$field' is missing (array empty)");
                    } else {
                        error_log("COB: Field '$field' is valid (array has content)");
                    }
                } else {
                    // Handle string fields
                    if (empty(trim($field_value))) {
                        $missing_fields[] = $field;
                        error_log("COB: Field '$field' is missing (string empty)");
                    } else {
                        error_log("COB: Field '$field' is valid (string has content)");
                    }
                }
            }
            
            error_log('COB: Missing fields found: ' . implode(', ', $missing_fields));

            // Additional validation for specific checkbox array fields that are required
            $checkbox_array_fields = [
                'marketing_goals' => 'Marketing Goals',
                'industry' => 'Industry',
                'industry_entities' => 'Industry Entities',
                'target_age_range' => 'Target Age Range',
                'gender_purchase_decision' => 'Gender Purchase Decision',
                'paid_media_history' => 'Paid Media History',
                'current_paid_media' => 'Current Paid Media'
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

            // Process file uploads
            error_log('COB: Processing file uploads...');
            $uploaded_files = $this->process_file_uploads();
            if ($uploaded_files === false) {
                error_log('COB: File upload processing failed');
                wp_die(json_encode([
                    'success' => false, 
                    'message' => 'Error processing file uploads. Please try again.'
                ]));
            }

            // Merge uploaded file data with form data
            if (!empty($uploaded_files)) {
                error_log('COB: Files uploaded successfully: ' . print_r($uploaded_files, true));
                foreach ($uploaded_files as $field_name => $file_data) {
                    $form_data[$field_name . '_url'] = $file_data['url'];
                    $form_data[$field_name . '_name'] = $file_data['name'];
                    $form_data[$field_name . '_id'] = $file_data['id'];
                }
            } else {
                error_log('COB: No files uploaded');
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
                error_log('COB: Submission saved successfully with ID: ' . $submission_id);
                
                // Delete draft after successful submission
                try {
                    COB_Database::delete_draft($session_id);
                    error_log('COB: Draft deleted successfully');
                } catch (Exception $e) {
                    error_log('COB: Draft deletion failed: ' . $e->getMessage());
                    // Don't fail submission if draft deletion fails
                }

                // Trigger email notifications
                error_log('COB: Triggering email notifications...');
                do_action('cob_after_submission', $submission_id, $submission_data);

                // Test admin notification
                $this->test_admin_notification($submission_id, $submission_data);

                COB_Database::log_activity('submission_completed', $submission_id, $session_id, 
                    'Form submitted successfully');

                error_log('COB: Sending success response to frontend');
                wp_die(json_encode([
                    'success' => true,
                    'message' => 'Form submitted successfully',
                    'submission_id' => $submission_id
                ]));
            } else {
                error_log('COB: Submission failed - no submission ID returned');
                global $wpdb;
                $db_error = $wpdb->last_error;
                error_log('COB: Database error: ' . $db_error);
                
                if (class_exists('COB_Database')) {
                    COB_Database::log_activity('submission_db_error', null, $session_id, 
                        'Database error: ' . $db_error);
                }
                
                error_log('COB: Sending error response to frontend');
                wp_die(json_encode([
                    'success' => false, 
                    'message' => 'Failed to submit form. Please try again.',
                    'debug' => WP_DEBUG ? $db_error : ''
                ]));
            }
        } catch (Exception $e) {
            error_log('COB: Exception occurred during submission: ' . $e->getMessage());
            error_log('COB: Exception trace: ' . $e->getTraceAsString());
            
            if (class_exists('COB_Database')) {
                try {
                    COB_Database::log_activity('submission_exception', null, $session_id ?? '', 
                        'Exception: ' . $e->getMessage());
                } catch (Exception $log_error) {
                    error_log('COB: Failed to log exception to database: ' . $log_error->getMessage());
                }
            }
            
            error_log('COB: Sending exception response to frontend');
            wp_die(json_encode([
                'success' => false, 
                'message' => 'Server error occurred. Please try again.',
                'debug' => WP_DEBUG ? $e->getMessage() : ''
            ]));
        }
        
        error_log('=== COB FORM SUBMISSION DEBUG END ===');
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
            
            // Step 1: Client Information
            'project_name' => sanitize_text_field($form_data['project_name'] ?? ''),
            'business_name' => sanitize_text_field($form_data['business_name'] ?? ''),
            'primary_contact_name' => sanitize_text_field($form_data['primary_contact_name'] ?? ''),
            'primary_contact_email' => sanitize_email($form_data['primary_contact_email'] ?? ''),
            'primary_contact_number' => sanitize_text_field($form_data['primary_contact_number'] ?? ''),
            'main_approver' => sanitize_text_field($form_data['main_approver'] ?? ''),
            'billing_email' => sanitize_email($form_data['billing_email'] ?? ''),
            'vat_number' => sanitize_text_field($form_data['vat_number'] ?? ''),
            'preferred_contact_method' => sanitize_text_field($form_data['preferred_contact_method'] ?? 'email'),
            'address_line_1' => sanitize_text_field($form_data['address_line_1'] ?? ''),
            'address_line_2' => sanitize_text_field($form_data['address_line_2'] ?? ''),
            'city' => sanitize_text_field($form_data['city'] ?? ''),
            'country' => sanitize_text_field($form_data['country'] ?? ''),
            'postal_code' => sanitize_text_field($form_data['postal_code'] ?? ''),
            
            // Step 2: Technical Information
            'current_cms' => sanitize_text_field($form_data['current_cms'] ?? ''),
            'website_hosting_company' => sanitize_text_field($form_data['website_hosting_company'] ?? ''),
            'website_contact_email' => sanitize_email($form_data['website_contact_email'] ?? ''),
            'domain_hosting_company' => sanitize_text_field($form_data['domain_hosting_company'] ?? ''),
            'domain_contact_email' => sanitize_email($form_data['domain_contact_email'] ?? ''),
            'cms_link' => esc_url_raw($form_data['cms_link'] ?? ''),
            'cms_username' => sanitize_text_field($form_data['cms_username'] ?? ''),
            'cms_password' => sanitize_text_field($form_data['cms_password'] ?? ''),
            'current_crm' => sanitize_text_field($form_data['current_crm'] ?? ''),
            'third_party_integrations' => sanitize_text_field($form_data['third_party_integrations'] ?? ''),
            'third_party_name' => sanitize_text_field($form_data['third_party_name'] ?? ''),
            'third_party_contact_number' => sanitize_text_field($form_data['third_party_contact_number'] ?? ''),
            'third_party_contact_email' => sanitize_email($form_data['third_party_contact_email'] ?? ''),
            'booking_engine_name' => sanitize_text_field($form_data['booking_engine_name'] ?? ''),
            'booking_engine_username' => sanitize_text_field($form_data['booking_engine_username'] ?? ''),
            'booking_engine_password' => sanitize_text_field($form_data['booking_engine_password'] ?? ''),
            'booking_engine_contact_email' => sanitize_email($form_data['booking_engine_contact_email'] ?? ''),
            'technical_objective' => sanitize_textarea_field($form_data['technical_objective'] ?? ''),
            
            // Step 3: Reporting Information
            'google_analytics_account' => sanitize_text_field($form_data['google_analytics_account'] ?? ''),
            'google_analytics_account_id' => sanitize_text_field($form_data['google_analytics_account_id'] ?? ''),
            'google_tag_manager_account' => sanitize_text_field($form_data['google_tag_manager_account'] ?? ''),
            'google_tag_manager_admin' => sanitize_text_field($form_data['google_tag_manager_admin'] ?? ''),
            'google_ads_account' => sanitize_text_field($form_data['google_ads_account'] ?? ''),
            'google_ads_admin' => sanitize_text_field($form_data['google_ads_admin'] ?? ''),
            'google_ads_customer_id' => sanitize_text_field($form_data['google_ads_customer_id'] ?? ''),
            'meta_business_manager_account' => sanitize_text_field($form_data['meta_business_manager_account'] ?? ''),
            'meta_business_manager_admin' => sanitize_text_field($form_data['meta_business_manager_admin'] ?? ''),
            'meta_business_manager_id' => sanitize_text_field($form_data['meta_business_manager_id'] ?? ''),
            'paid_media_history' => $this->safe_array_implode($form_data['paid_media_history'] ?? []),
            'paid_media_history_other' => sanitize_text_field($form_data['paid_media_history_other'] ?? ''),
            'current_paid_media' => $this->safe_array_implode($form_data['current_paid_media'] ?? []),
            'current_paid_media_other' => sanitize_text_field($form_data['current_paid_media_other'] ?? ''),
            
            // Step 4: Marketing Information
            'main_objective' => sanitize_textarea_field($form_data['main_objective'] ?? ''),
            'brand_focus' => sanitize_textarea_field($form_data['brand_focus'] ?? ''),
            'commercial_objective' => sanitize_textarea_field($form_data['commercial_objective'] ?? ''),
            'push_impact' => sanitize_textarea_field($form_data['push_impact'] ?? ''),
            'founder_inspiration' => sanitize_textarea_field($form_data['founder_inspiration'] ?? ''),
            'brand_tone_mission' => sanitize_textarea_field($form_data['brand_tone_mission'] ?? ''),
            'brand_perception' => sanitize_textarea_field($form_data['brand_perception'] ?? ''),
            'global_team_introduction' => sanitize_textarea_field($form_data['global_team_introduction'] ?? ''),
            'service_introduction' => sanitize_textarea_field($form_data['service_introduction'] ?? ''),
            'brand_line_1' => sanitize_text_field($form_data['brand_line_1'] ?? ''),
            'mission_1' => sanitize_textarea_field($form_data['mission_1'] ?? ''),
            'brand_line_2' => sanitize_text_field($form_data['brand_line_2'] ?? ''),
            'mission_2' => sanitize_textarea_field($form_data['mission_2'] ?? ''),
            'brand_line_3' => sanitize_text_field($form_data['brand_line_3'] ?? ''),
            'mission_3' => sanitize_textarea_field($form_data['mission_3'] ?? ''),
            'brand_guidelines_upload' => sanitize_text_field($form_data['brand_guidelines_upload'] ?? ''),
            'brand_guidelines_files' => sanitize_text_field($form_data['brand_guidelines_files'] ?? ''),
            
            // File Upload Fields
            'logo_file_url' => esc_url_raw($form_data['logo_file_url'] ?? ''),
            'logo_file_name' => sanitize_text_field($form_data['logo_file_name'] ?? ''),
            'logo_file_id' => intval($form_data['logo_file_id'] ?? 0),
            'brand_guidelines_url' => esc_url_raw($form_data['brand_guidelines_url'] ?? ''),
            'brand_guidelines_name' => sanitize_text_field($form_data['brand_guidelines_name'] ?? ''),
            'brand_guidelines_id' => intval($form_data['brand_guidelines_id'] ?? 0),
            'brand_guidelines_upload_url' => esc_url_raw($form_data['brand_guidelines_upload_url'] ?? ''),
            'brand_guidelines_upload_name' => sanitize_text_field($form_data['brand_guidelines_upload_name'] ?? ''),
            'brand_guidelines_upload_id' => intval($form_data['brand_guidelines_upload_id'] ?? 0),
            'communication_tone' => sanitize_text_field($form_data['communication_tone'] ?? ''),
            'casual_tone_explanation' => sanitize_textarea_field($form_data['casual_tone_explanation'] ?? ''),
            'formal_tone_explanation' => sanitize_textarea_field($form_data['formal_tone_explanation'] ?? ''),
            'brand_accounts' => sanitize_text_field($form_data['brand_accounts'] ?? ''),
            'facebook_page' => sanitize_text_field($form_data['facebook_page'] ?? ''),
            'instagram_username' => sanitize_text_field($form_data['instagram_username'] ?? ''),
            'industry_entities' => $this->safe_array_implode($form_data['industry_entities'] ?? []),
            'industry_entities_other' => sanitize_text_field($form_data['industry_entities_other'] ?? ''),
            'industry_status' => sanitize_textarea_field($form_data['industry_status'] ?? ''),
            'market_insights' => sanitize_text_field($form_data['market_insights'] ?? ''),
            'content_social_media' => sanitize_text_field($form_data['content_social_media'] ?? ''),
            'business_focus_elements' => sanitize_text_field($form_data['business_focus_elements'] ?? ''),
            'social_media_accounts' => sanitize_text_field($form_data['social_media_accounts'] ?? ''),
            'facebook_accounts_url' => esc_url_raw($form_data['facebook_accounts_url'] ?? ''),
            'facebook_page_url' => esc_url_raw($form_data['facebook_page_url'] ?? ''),
            'twitter_accounts_url' => esc_url_raw($form_data['twitter_accounts_url'] ?? ''),
            'instagram_page_url' => esc_url_raw($form_data['instagram_page_url'] ?? ''),
            'ideal_customer_description' => sanitize_textarea_field($form_data['ideal_customer_description'] ?? ''),
            'potential_client_view' => sanitize_textarea_field($form_data['potential_client_view'] ?? ''),
            'target_age_range' => $this->safe_array_implode($form_data['target_age_range'] ?? []),
            'problems_solved' => sanitize_textarea_field($form_data['problems_solved'] ?? ''),
            'business_challenges' => sanitize_text_field($form_data['business_challenges'] ?? ''),
            'tracking_accounting' => sanitize_text_field($form_data['tracking_accounting'] ?? ''),
            'additional_information' => sanitize_textarea_field($form_data['additional_information'] ?? ''),
            
            // System Fields
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

    private function test_admin_notification($submission_id, $submission_data) {
        // This is a placeholder for testing admin notifications.
        // In a real scenario, you would trigger an email or other admin notification here.
        // For now, we'll just log that it was called.
        error_log('COB: Admin notification test triggered for submission ID: ' . $submission_id);
        error_log('COB: Submission data for admin notification: ' . print_r($submission_data, true));

        // Example: Send an email to the admin
        // $admin_email = get_option('admin_email'); // Get admin email from settings
        // if ($admin_email) {
        //     $subject = 'New Client Onboarding Submission';
        //     $message = 'A new client onboarding submission has been received. Submission ID: ' . $submission_id . "\n\n";
        //     $message .= 'Business Name: ' . ($submission_data['business_name'] ?? 'N/A') . "\n";
        //     $message .= 'Project Name: ' . ($submission_data['project_name'] ?? 'N/A') . "\n";
        //     $message .= 'Primary Contact: ' . ($submission_data['primary_contact_name'] ?? 'N/A') . "\n";
        //     $message .= 'Email: ' . ($submission_data['primary_contact_email'] ?? 'N/A') . "\n";
        //     $message .= 'Website: ' . ($submission_data['current_website'] ?? 'N/A') . "\n";
        //     $message .= 'Status: ' . ($submission_data['status'] ?? 'N/A') . "\n";
        //     $message .= 'Submitted at: ' . ($submission_data['submitted_at'] ?? 'N/A') . "\n";

        //     wp_mail($admin_email, $subject, $message);
        //     error_log('COB: Admin email sent successfully to ' . $admin_email);
        // } else {
        //     error_log('COB: Admin email not configured. Cannot send test notification.');
        // }
    }

}