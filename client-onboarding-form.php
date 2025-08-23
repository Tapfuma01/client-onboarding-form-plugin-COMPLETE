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
        add_action('plugins_loaded', [$this, 'init']);
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }

    public function init() {
        // Load plugin textdomain
        load_plugin_textdomain('client-onboarding-form', false, dirname(plugin_basename(__FILE__)) . '/languages');

        // Include required files
        $this->include_files();

        // Initialize classes
        new COB_Form_Handler();
        new COB_Admin();
        
        // Add shortcode
        add_shortcode('client_onboarding_form', [$this, 'render_form_shortcode']);
    }

    private function include_files() {
        require_once COB_PLUGIN_PATH . 'includes/class-form-handler.php';
        require_once COB_PLUGIN_PATH . 'includes/class-admin.php';
        require_once COB_PLUGIN_PATH . 'includes/class-database.php';
        require_once COB_PLUGIN_PATH . 'includes/class-email-notifications.php';
    }

    public function activate() {
        // Include required files for activation
        require_once COB_PLUGIN_PATH . 'includes/class-database.php';
        
        // Create database tables
        COB_Database::create_tables();
        
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
    }

    public function deactivate() {
        // Clean up scheduled events if any
        wp_clear_scheduled_hook('cob_cleanup_drafts');
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
}

// Initialize the plugin
new ClientOnboardingForm();