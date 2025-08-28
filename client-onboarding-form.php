<?php
/**
 * Plugin Name: Client Onboarding Form
 * Plugin URI: https://flux.agency
 * Description: Professional WordPress plugin for streamlined client onboarding through a modern, multi-step form interface.
 * Version: 1.0.0
 * Author: FLUX Agency
 * License: GPL2+
 * Text Domain: client-onboarding-form
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('COB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('COB_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('COB_PLUGIN_VERSION', '1.0.0');

/**
 * Main plugin class
 */
class ClientOnboardingForm {

    public function __construct() {
        // Define plugin constants
        $this->define_constants();
        
        // Add hooks
        add_action('init', [$this, 'init']);
        add_action('wp_loaded', [$this, 'wp_loaded']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        // Add shortcode
        add_shortcode('client_onboarding_form', [$this, 'render_form_shortcode']);
        
        // Add cron hooks
        add_action('cob_database_maintenance', [$this, 'perform_database_maintenance']);
        add_action('cob_cleanup_drafts', [$this, 'cleanup_old_drafts']);
        
        // Add AJAX actions for both logged-in and non-logged-in users
        add_action('wp_ajax_cob_generate_share_token', [$this, 'handle_generate_share_token']);
        add_action('wp_ajax_nopriv_cob_generate_share_token', [$this, 'handle_generate_share_token']);
        add_action('wp_ajax_cob_load_shared_draft', [$this, 'handle_load_shared_draft']);
        add_action('wp_ajax_nopriv_cob_load_shared_draft', [$this, 'handle_load_shared_draft']);
        
        // Handle share token redirects
        add_action('template_redirect', [$this, 'handle_share_token_redirect']);
        
        // Load admin functionality
        if (is_admin()) {
            require_once COB_PLUGIN_PATH . 'includes/class-admin.php';
            new COB_Admin();
        }
        
        // Load form handler
        require_once COB_PLUGIN_PATH . 'includes/class-form-handler.php';
        new COB_Form_Handler();
        
        // Add AJAX action for manual database update
        add_action('wp_ajax_cob_update_database', [$this, 'handle_database_update']);
        add_action('wp_ajax_nopriv_cob_update_database', [$this, 'handle_database_update']);
    }

    /**
     * Define plugin constants
     */
    private function define_constants() {
        if (!defined('COB_PLUGIN_PATH')) {
            define('COB_PLUGIN_PATH', plugin_dir_path(__FILE__));
        }
        if (!defined('COB_PLUGIN_URL')) {
            define('COB_PLUGIN_URL', plugin_dir_url(__FILE__));
        }
        if (!defined('COB_PLUGIN_VERSION')) {
            define('COB_PLUGIN_VERSION', '1.0.0');
        }
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Load plugin textdomain
        load_plugin_textdomain('client-onboarding-form', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * WordPress loaded hook
     */
    public function wp_loaded() {
        // Additional initialization after WordPress is fully loaded
    }

    public function activate() {
        // Create database tables
        if (class_exists('COB_Database')) {
            COB_Database::create_tables();
            COB_Database::ensure_share_token_column();
            COB_Database::update_database_schema();
        }
        
        // Schedule cron jobs
        if (!wp_next_scheduled('cob_database_maintenance')) {
            wp_schedule_event(time(), 'daily', 'cob_database_maintenance');
        }
        if (!wp_next_scheduled('cob_cleanup_drafts')) {
            wp_schedule_event(time(), 'daily', 'cob_cleanup_drafts');
        }
        
        // Optimize tables
        if (method_exists('COB_Database', 'optimize_tables')) {
            COB_Database::optimize_tables();
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    public function deactivate() {
        // Clean up scheduled events
        wp_clear_scheduled_hook('cob_cleanup_drafts');
        wp_clear_scheduled_hook('cob_database_maintenance');
    }

    public function render_form_shortcode($atts) {
        // Ensure share_token column exists
        if (class_exists('COB_Database') && method_exists('COB_Database', 'ensure_share_token_column')) {
            COB_Database::ensure_share_token_column();
        }
        
        // Ensure database schema is up to date
        if (class_exists('COB_Database') && method_exists('COB_Database', 'update_database_schema')) {
            COB_Database::update_database_schema();
        }
        
        $atts = shortcode_atts([
            'theme' => 'dark',
            'show_progress' => true,
            'auto_save' => true
        ], $atts, 'client_onboarding_form');

        ob_start();
        include COB_PLUGIN_PATH . 'templates/form-template.php';
        return ob_get_clean();
    }
    
    /**
     * Handle AJAX request to update database schema
     */
    public function handle_database_update() {
        // Check nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'cob_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        if (class_exists('COB_Database') && method_exists('COB_Database', 'update_database_schema')) {
            try {
                COB_Database::update_database_schema();
                wp_send_json_success('Database schema updated successfully');
            } catch (Exception $e) {
                wp_send_json_error('Database update failed: ' . $e->getMessage());
            }
        } else {
            wp_send_json_error('Database class not available');
        }
    }

    /**
     * Handle AJAX request to generate share token
     */
    public function handle_generate_share_token() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cob_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        
        error_log('COB: Generating share token for session: ' . $session_id);
        
        if (empty($session_id)) {
            wp_send_json_error('Session ID is required');
        }
        
        // Generate share token
        if (class_exists('COB_Database')) {
            error_log('COB: Database class exists, calling generate_share_token');
            $token = COB_Database::generate_share_token($session_id);
            
            if ($token) {
                error_log('COB: Share token generated successfully: ' . $token);
                wp_send_json_success(['token' => $token]);
            } else {
                error_log('COB: Failed to generate share token for session: ' . $session_id);
                wp_send_json_error('Failed to generate share token');
            }
        } else {
            error_log('COB: Database class not available');
            wp_send_json_error('Database class not available');
        }
    }

    /**
     * Handle AJAX request to load a shared draft
     */
    public function handle_load_shared_draft() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cob_admin_nonce')) {
            wp_send_json_error('Security check failed');
        }

        $token = sanitize_text_field($_POST['share_token'] ?? '');
        
        error_log('COB: Loading shared draft with token: ' . $token);

        if (empty($token)) {
            wp_send_json_error('Share token is required');
        }

        // Load the draft data
        if (class_exists('COB_Database')) {
            error_log('COB: Database class exists, calling get_draft_by_token');
            $draft_data = COB_Database::get_draft_by_token($token);
            
            if ($draft_data) {
                error_log('COB: Draft data found: ' . print_r($draft_data, true));
                wp_send_json_success(['draft' => $draft_data]);
            } else {
                error_log('COB: No draft found for token: ' . $token);
                wp_send_json_error('Draft not found or expired');
            }
        } else {
            error_log('COB: Database class not available');
            wp_send_json_error('Database class not available');
        }
    }

    /**
     * Handle share token redirects to the form page
     */
    public function handle_share_token_redirect() {
        if (isset($_GET['cob_share']) && !empty($_GET['cob_share'])) {
            $token = sanitize_text_field($_GET['cob_share']);
            
            // Check if token exists in database
            if (class_exists('COB_Database')) {
                $draft_data = COB_Database::get_draft_by_token($token);
                if ($draft_data) {
                    // Get the page with the form shortcode
                    $form_page = $this->get_form_page_url();
                    if ($form_page) {
                        wp_redirect($form_page . '?cob_share=' . urlencode($token));
                        exit;
                    }
                }
            }
        }
    }

    /**
     * Get the URL of the page containing the form shortcode
     */
    private function get_form_page_url() {
        // Look for pages with the shortcode
        $pages = get_pages([
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_cob_has_form',
                    'value' => '1',
                    'compare' => '='
                ]
            ]
        ]);
        
        if (!empty($pages)) {
            return get_permalink($pages[0]->ID);
        }
        
        // Fallback: look for pages with the shortcode in content
        $pages = get_pages(['post_status' => 'publish']);
        foreach ($pages as $page) {
            if (strpos($page->post_content, '[client_onboarding_form]') !== false) {
                // Cache this for future use
                update_post_meta($page->ID, '_cob_has_form', '1');
                return get_permalink($page->ID);
            }
        }
        
        return false;
    }

    /**
     * Perform regular database maintenance to prevent deadlocks
     */
    public function perform_database_maintenance() {
        if (class_exists('COB_Database')) {
            // Check for table locks
            if (method_exists('COB_Database', 'check_table_locks')) {
                COB_Database::check_table_locks();
            }
            
            // Optimize tables
            if (method_exists('COB_Database', 'optimize_tables')) {
                COB_Database::optimize_tables();
            }
            
            // Clean up old drafts
            $this->cleanup_old_drafts();
        }
    }

    /**
     * Clean up old drafts to reduce table size and deadlock likelihood
     */
    public function cleanup_old_drafts() {
        if (class_exists('COB_Database')) {
            $days = get_option('cob_settings')['draft_retention_days'] ?? 30;
            COB_Database::cleanup_old_drafts($days);
        }
    }

    /**
     * Enqueue scripts and styles for the form
     */
    public function enqueue_scripts() {
        wp_enqueue_script('cob-form-script', COB_PLUGIN_URL . 'assets/js/form-script.js', ['jquery'], COB_PLUGIN_VERSION, true);
        wp_enqueue_style('cob-form-style', COB_PLUGIN_URL . 'assets/css/form-style.css', [], COB_PLUGIN_VERSION);
        
        // Localize script with AJAX variables
        wp_localize_script('cob-form-script', 'cob_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cob_admin_nonce'),
            'messages' => [
                'draft_saved' => 'Draft saved successfully!',
                'draft_load_failed' => 'Failed to load draft. Please try again.',
                'form_submitted' => 'Form submitted successfully!',
                'form_submit_failed' => 'Failed to submit form. Please try again.',
                'submit_error' => 'Failed to submit form. Please try again.'
            ]
        ]);
    }
}

// Initialize the plugin
new ClientOnboardingForm();