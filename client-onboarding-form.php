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
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        // Add shortcode
        add_shortcode('client_onboarding_form', [$this, 'render_form_shortcode']);
        
        // Add scheduled tasks
        add_action('cob_cleanup_drafts', [$this, 'cleanup_old_drafts']);
        add_action('cob_database_maintenance', [$this, 'perform_database_maintenance']);
        
        // Load admin functionality
        if (is_admin()) {
            require_once COB_PLUGIN_PATH . 'includes/class-admin.php';
            new COB_Admin();
        }
        
        // Load form handler
        require_once COB_PLUGIN_PATH . 'includes/class-form-handler.php';
        new COB_Form_Handler();
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
        // Include required files for activation
        require_once COB_PLUGIN_PATH . 'includes/class-database.php';
        
        // Create database tables
        COB_Database::create_tables();
        
        // Optimize tables to reduce deadlock likelihood
        COB_Database::optimize_tables();
        
        // Set default options
        add_option('cob_settings', [
            'admin_email' => get_option('admin_email'),
            'company_name' => get_bloginfo('name'),
            'auto_save_interval' => 30,
            'enable_admin_notification' => true,
            'enable_client_confirmation' => true,
            'email_from_name' => get_bloginfo('name'),
            'email_from_email' => get_option('admin_email')
        ]);
        
        // Schedule regular database maintenance
        if (!wp_next_scheduled('cob_database_maintenance')) {
            wp_schedule_event(time(), 'daily', 'cob_database_maintenance');
        }
        
        // Schedule draft cleanup
        if (!wp_next_scheduled('cob_cleanup_drafts')) {
            wp_schedule_event(time(), 'daily', 'cob_cleanup_drafts');
        }
    }

    public function deactivate() {
        // Clean up scheduled events
        wp_clear_scheduled_hook('cob_cleanup_drafts');
        wp_clear_scheduled_hook('cob_database_maintenance');
    }

    public function render_form_shortcode($atts) {
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
     * Perform regular database maintenance to prevent deadlocks
     */
    public function perform_database_maintenance() {
        if (class_exists('COB_Database')) {
            // Check for table locks
            COB_Database::check_table_locks();
            
            // Optimize tables
            COB_Database::optimize_tables();
            
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
}

// Initialize the plugin
new ClientOnboardingForm();