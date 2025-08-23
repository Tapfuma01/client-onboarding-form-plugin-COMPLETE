<?php
/**
 * Admin interface class for managing plugin settings and submissions
 */

if (!defined('ABSPATH')) {
    exit;
}

class COB_Admin {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'admin_init']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        
        // Add AJAX handlers
        add_action('wp_ajax_cob_generate_share_link', [$this, 'handle_generate_share_link']);
        add_action('wp_ajax_cob_get_draft_details', [$this, 'handle_get_draft_details']);
        add_action('wp_ajax_cob_delete_draft', [$this, 'handle_delete_draft']);
        
        // Load email notifications class
        $this->load_email_notifications();
    }

    private function load_email_notifications() {
        if (!class_exists('COB_Email_Notifications')) {
            require_once COB_PLUGIN_PATH . 'includes/class-email-notifications.php';
        }
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Client Onboarding', 'client-onboarding-form'),
            __('Client Onboarding', 'client-onboarding-form'),
            'manage_options',
            'client-onboarding',
            [$this, 'dashboard_page'],
            'dashicons-clipboard',
            30
        );

        add_submenu_page(
            'client-onboarding',
            __('Dashboard', 'client-onboarding-form'),
            __('Dashboard', 'client-onboarding-form'),
            'manage_options',
            'client-onboarding',
            [$this, 'dashboard_page']
        );

        add_submenu_page(
            'client-onboarding',
            __('Submissions', 'client-onboarding-form'),
            __('Submissions', 'client-onboarding-form'),
            'manage_options',
            'cob-submissions',
            [$this, 'submissions_page']
        );

        add_submenu_page(
            'client-onboarding',
            __('Draft Management', 'client-onboarding-form'),
            __('Draft Management', 'client-onboarding-form'),
            'manage_options',
            'cob-drafts',
            [$this, 'drafts_page']
        );

        add_submenu_page(
            'client-onboarding',
            __('Settings', 'client-onboarding-form'),
            __('Settings', 'client-onboarding-form'),
            'manage_options',
            'cob-settings',
            [$this, 'settings_page']
        );

        add_submenu_page(
            'client-onboarding',
            __('Email Notifications', 'client-onboarding-form'),
            __('Email Notifications', 'client-onboarding-form'),
            'manage_options',
            'cob-email-settings',
            [$this, 'email_settings_page']
        );

        add_submenu_page(
            'client-onboarding',
            __('Activity Logs', 'client-onboarding-form'),
            __('Activity Logs', 'client-onboarding-form'),
            'manage_options',
            'cob-logs',
            [$this, 'logs_page']
        );
    }

    public function admin_init() {
        register_setting('cob_settings_group', 'cob_settings');
    }

    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'client-onboarding') !== false || strpos($hook, 'cob-') !== false) {
            wp_enqueue_style(
                'cob-admin-style', 
                COB_PLUGIN_URL . 'assets/css/admin-style.css', 
                [], 
                COB_PLUGIN_VERSION
            );
            
            wp_enqueue_script(
                'cob-admin-script', 
                COB_PLUGIN_URL . 'assets/js/admin-script.js', 
                ['jquery'], 
                COB_PLUGIN_VERSION, 
                true
            );
            
            // Enqueue WordPress editor for email templates
            if (strpos($hook, 'cob-email-settings') !== false) {
                wp_enqueue_editor();
            }
        }
    }

    public function dashboard_page() {
        global $wpdb;
        
        // Get statistics
        $submissions_table = $wpdb->prefix . 'cob_submissions';
        $total_submissions = $wpdb->get_var("SELECT COUNT(*) FROM $submissions_table");
        $this_month = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $submissions_table WHERE submitted_at >= %s",
            date('Y-m-01')
        ));
        $this_week = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $submissions_table WHERE submitted_at >= %s",
            date('Y-m-d', strtotime('-7 days'))
        ));

        // Get recent submissions
        $recent_submissions = COB_Database::get_submissions(5);

        include COB_PLUGIN_PATH . 'admin/views/dashboard.php';
    }

    public function submissions_page() {
        $action = $_GET['action'] ?? 'list';
        $submission_id = intval($_GET['view'] ?? 0);

        switch ($action) {
            case 'view':
                if ($submission_id) {
                    $submission = COB_Database::get_submission($submission_id);
                    include COB_PLUGIN_PATH . 'admin/views/submission-detail.php';
                } else {
                    $this->list_submissions();
                }
                break;
            case 'delete':
                if ($submission_id && check_admin_referer('delete_submission_' . $submission_id)) {
                    COB_Database::delete_submission($submission_id);
                    echo '<div class="notice notice-success"><p>Submission deleted successfully.</p></div>';
                }
                $this->list_submissions();
                break;
            default:
                $this->list_submissions();
                break;
        }
    }

    private function list_submissions() {
        $page = max(1, intval($_GET['paged'] ?? 1));
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        
        $submissions = COB_Database::get_submissions($per_page, $offset);
        
        include COB_PLUGIN_PATH . 'admin/views/submissions-list.php';
    }

    public function settings_page() {
        if (isset($_POST['submit'])) {
            check_admin_referer('cob_settings_nonce');
            
            $settings = [
                'admin_email' => sanitize_email($_POST['admin_email'] ?? ''),
                'company_name' => sanitize_text_field($_POST['company_name'] ?? ''),
                'enable_notifications' => !empty($_POST['enable_notifications']),
                'auto_save_interval' => intval($_POST['auto_save_interval'] ?? 30),
                'webhook_url' => esc_url_raw($_POST['webhook_url'] ?? ''),
                'enable_webhook' => !empty($_POST['enable_webhook'])
            ];
            
            update_option('cob_settings', $settings);
            echo '<div class="notice notice-success"><p>Settings saved successfully.</p></div>';
        }

        $settings = get_option('cob_settings', [
            'admin_email' => get_option('admin_email'),
            'company_name' => get_bloginfo('name'),
            'enable_notifications' => true,
            'auto_save_interval' => 30,
            'webhook_url' => '',
            'enable_webhook' => false
        ]);

        include COB_PLUGIN_PATH . 'admin/views/settings.php';
    }

    public function email_settings_page() {
        if (isset($_POST['submit'])) {
            check_admin_referer('cob_email_settings_nonce');
            
            $settings = get_option('cob_settings', []);
            
            // Update email settings
            $email_settings = [
                'email_from_name' => sanitize_text_field($_POST['email_from_name'] ?? ''),
                'email_from_email' => sanitize_email($_POST['email_from_email'] ?? ''),
                'enable_admin_notification' => !empty($_POST['enable_admin_notification']),
                'admin_email' => sanitize_email($_POST['admin_email'] ?? ''),
                'additional_admin_emails' => sanitize_textarea_field($_POST['additional_admin_emails'] ?? ''),
                'admin_email_cc' => sanitize_text_field($_POST['admin_email_cc'] ?? ''),
                'admin_email_bcc' => sanitize_text_field($_POST['admin_email_bcc'] ?? ''),
                'admin_email_subject' => sanitize_text_field($_POST['admin_email_subject'] ?? ''),
                'admin_email_body' => wp_kses_post($_POST['admin_email_body'] ?? ''),
                'enable_client_confirmation' => !empty($_POST['enable_client_confirmation']),
                'client_email_subject' => sanitize_text_field($_POST['client_email_subject'] ?? ''),
                'client_email_body' => wp_kses_post($_POST['client_email_body'] ?? ''),
                'notify_technical_contact' => !empty($_POST['notify_technical_contact']),
                'technical_email_subject' => sanitize_text_field($_POST['technical_email_subject'] ?? ''),
                'technical_email_body' => wp_kses_post($_POST['technical_email_body'] ?? ''),
                'notify_reporting_contact' => !empty($_POST['notify_reporting_contact']),
                'reporting_email_subject' => sanitize_text_field($_POST['reporting_email_subject'] ?? ''),
                'reporting_email_body' => wp_kses_post($_POST['reporting_email_body'] ?? ''),
            ];
            
            // Merge with existing settings
            $settings = array_merge($settings, $email_settings);
            
            update_option('cob_settings', $settings);
            echo '<div class="notice notice-success"><p>Email settings saved successfully.</p></div>';
        }

        $settings = get_option('cob_settings', []);
        include COB_PLUGIN_PATH . 'admin/views/email-settings.php';
    }

    public function logs_page() {
        // Ensure database class is loaded
        if (!class_exists('COB_Database')) {
            require_once COB_PLUGIN_PATH . 'includes/class-database.php';
        }

        include COB_PLUGIN_PATH . 'admin/views/logs.php';
    }

    public function drafts_page() {
        include COB_PLUGIN_PATH . 'admin/views/drafts.php';
    }

    public function handle_generate_share_link() {
        check_ajax_referer('cob_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $session_id = sanitize_text_field($_POST['session_id']);
        $token = COB_Database::generate_share_token($session_id);
        
        if ($token) {
            $link = home_url('?cob_share=' . $token);
            wp_send_json_success(['link' => $link, 'token' => $token]);
        } else {
            wp_send_json_error('Failed to generate token');
        }
    }

    public function handle_get_draft_details() {
        check_ajax_referer('cob_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $session_id = sanitize_text_field($_POST['session_id']);
        $draft_data = COB_Database::get_draft($session_id);
        
        if ($draft_data) {
            $html = $this->format_draft_details($draft_data);
            wp_send_json_success(['html' => $html]);
        } else {
            wp_send_json_error('Draft not found');
        }
    }

    public function handle_delete_draft() {
        check_ajax_referer('cob_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $session_id = sanitize_text_field($_POST['session_id']);
        $result = COB_Database::delete_draft($session_id);
        
        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Failed to delete draft');
        }
    }

    private function format_draft_details($draft_data) {
        $form_data = $draft_data['form_data'] ?? [];
        $current_step = $draft_data['current_step'] ?? 1;
        $progress = $draft_data['progress_percentage'] ?? 0;
        
        $html = '<div class="cob-draft-overview">';
        $html .= '<p><strong>Current Step:</strong> ' . $current_step . '/4</p>';
        $html .= '<p><strong>Progress:</strong> ' . $progress . '%</p>';
        $html .= '<p><strong>Client Email:</strong> ' . esc_html($draft_data['client_email'] ?? 'N/A') . '</p>';
        $html .= '</div>';
        
        $html .= '<div class="cob-form-data">';
        $html .= '<h3>Form Data</h3>';
        $html .= '<table class="widefat">';
        
        $field_labels = [
            'project_name' => 'Project Name',
            'business_name' => 'Business Name',
            'primary_contact_name' => 'Primary Contact Name',
            'primary_contact_email' => 'Primary Contact Email',
            'current_website' => 'Current Website',
            'technical_contact_name' => 'Technical Contact Name',
            'reporting_frequency' => 'Reporting Frequency',
            'target_audience' => 'Target Audience',
            'marketing_goals' => 'Marketing Goals'
        ];
        
        foreach ($field_labels as $key => $label) {
            if (!empty($form_data[$key])) {
                $value = is_array($form_data[$key]) ? implode(', ', $form_data[$key]) : $form_data[$key];
                $html .= '<tr><th>' . esc_html($label) . '</th><td>' . esc_html($value) . '</td></tr>';
            }
        }
        
        $html .= '</table>';
        $html .= '</div>';
        
        return $html;
    }
}