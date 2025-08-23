<?php
/**
 * Database management class
 */

if (!defined('ABSPATH')) {
    exit;
}

class COB_Database {

    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();

        // Submissions table
        $table_name = $wpdb->prefix . 'cob_submissions';
        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            session_id varchar(100) NOT NULL,
            business_name varchar(255) NOT NULL,
            project_name varchar(255) NOT NULL,
            primary_contact_name varchar(255) NOT NULL,
            primary_contact_email varchar(255) NOT NULL,
            primary_contact_phone varchar(50),
            milestone_approver varchar(255),
            billing_email varchar(255),
            vat_number varchar(100),
            preferred_contact_method varchar(20) DEFAULT 'email',
            billing_address_line1 varchar(255),
            billing_address_line2 varchar(255),
            billing_address_city varchar(100),
            billing_address_country varchar(100),
            billing_address_postal_code varchar(20),
            current_website varchar(255),
            hosting_provider varchar(255),
            domain_provider varchar(255),
            technical_contact_name varchar(255),
            technical_contact_email varchar(255),
            preferred_cms varchar(100),
            integration_requirements text,
            technology_stack text,
            reporting_frequency varchar(50),
            reporting_format varchar(50),
            key_metrics text,
            reporting_contact_name varchar(255),
            reporting_contact_email varchar(255),
            dashboard_access varchar(50),
            additional_reporting_requirements text,
            target_audience text,
            marketing_goals text,
            marketing_budget varchar(50),
            current_marketing_channels text,
            brand_guidelines text,
            competitor_analysis text,
            marketing_challenges text,
            success_metrics text,
            status varchar(20) DEFAULT 'submitted',
            submitted_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY status (status),
            KEY submitted_at (submitted_at)
        ) $charset_collate;";

        // Drafts table
        $drafts_table = $wpdb->prefix . 'cob_drafts';
        $drafts_sql = "CREATE TABLE $drafts_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            session_id varchar(100) NOT NULL,
            form_data longtext NOT NULL,
            share_token varchar(100) DEFAULT NULL,
            current_step int(2) DEFAULT 1,
            progress_percentage int(3) DEFAULT 0,
            client_email varchar(255) DEFAULT NULL,
            last_saved datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY session_id (session_id),
            UNIQUE KEY share_token (share_token),
            KEY last_saved (last_saved),
            KEY client_email (client_email)
        ) $charset_collate;";

        // Activity logs table
        $logs_table = $wpdb->prefix . 'cob_logs';
        $logs_sql = "CREATE TABLE $logs_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            action varchar(100) NOT NULL,
            submission_id int(11),
            session_id varchar(100),
            details text,
            ip_address varchar(45),
            user_agent text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY action (action),
            KEY submission_id (submission_id),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        dbDelta($drafts_sql);
        dbDelta($logs_sql);
        
        // Optimize tables after creation to reduce deadlock likelihood
        self::optimize_tables();
    }

    /**
     * Optimize database tables to reduce deadlock likelihood
     */
    public static function optimize_tables() {
        global $wpdb;
        
        try {
            // Optimize drafts table
            $drafts_table = $wpdb->prefix . 'cob_drafts';
            $wpdb->query("OPTIMIZE TABLE $drafts_table");
            
            // Analyze table to update statistics
            $wpdb->query("ANALYZE TABLE $drafts_table");
            
            // Check and repair table if needed
            $wpdb->query("CHECK TABLE $drafts_table");
            
            self::log_activity('table_optimization', null, null, 'Tables optimized successfully');
            
        } catch (Exception $e) {
            self::log_activity('table_optimization_error', null, null, 'Table optimization failed: ' . $e->getMessage());
        }
    }

    /**
     * Check for and resolve table locks
     */
    public static function check_table_locks() {
        global $wpdb;
        
        try {
            // Check for running processes that might be causing locks
            $processes = $wpdb->get_results("SHOW PROCESSLIST");
            $locked_processes = [];
            
            foreach ($processes as $process) {
                if (strpos($process->Info ?? '', 'wp_cob_drafts') !== false && 
                    $process->State === 'Locked') {
                    $locked_processes[] = $process->Id;
                }
            }
            
            if (!empty($locked_processes)) {
                self::log_activity('table_locks_detected', null, null, 'Locks detected on processes: ' . implode(', ', $locked_processes));
                
                // Kill locked processes (use with caution)
                foreach ($locked_processes as $process_id) {
                    $wpdb->query("KILL $process_id");
                }
                
                self::log_activity('table_locks_resolved', null, null, 'Locks resolved by killing processes');
            }
            
        } catch (Exception $e) {
            self::log_activity('table_lock_check_error', null, null, 'Lock check failed: ' . $e->getMessage());
        }
    }

    public static function save_submission($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cob_submissions';
        
        $result = $wpdb->insert(
            $table_name,
            $data,
            [
                '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
                '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
                '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
                '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
            ]
        );

        if ($result !== false) {
            $submission_id = $wpdb->insert_id;
            self::log_activity('submission_created', $submission_id, $data['session_id']);
            return $submission_id;
        }

        return false;
    }

    public static function save_draft($session_id, $form_data, $current_step = 1, $client_email = null) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cob_drafts';
        
        // Calculate progress percentage based on completed fields
        $progress = self::calculate_progress($form_data, $current_step);
        
        // Use INSERT ... ON DUPLICATE KEY UPDATE to avoid deadlocks
        // Add retry logic for deadlock situations
        $max_retries = 3;
        $retry_count = 0;
        
        while ($retry_count < $max_retries) {
            try {
                // Start transaction for better consistency
                $wpdb->query('START TRANSACTION');
                
                $result = $wpdb->query($wpdb->prepare(
                    "INSERT INTO $table_name
                    (session_id, form_data, current_step, progress_percentage, client_email, last_saved)
                    VALUES (%s, %s, %d, %d, %s, %s)
                    ON DUPLICATE KEY UPDATE
                    form_data = VALUES(form_data),
                    current_step = VALUES(current_step),
                    progress_percentage = VALUES(progress_percentage),
                    client_email = VALUES(client_email),
                    last_saved = VALUES(last_saved)",
                    $session_id,
                    wp_json_encode($form_data),
                    $current_step,
                    $progress,
                    $client_email,
                    current_time('mysql')
                ));
                
                if ($result !== false) {
                    // Commit transaction
                    $wpdb->query('COMMIT');
                    self::log_activity('draft_saved', null, $session_id);
                    return true;
                } else {
                    // Rollback transaction
                    $wpdb->query('ROLLBACK');
                    $retry_count++;
                    
                    if ($retry_count >= $max_retries) {
                        self::log_activity('draft_save_failed', null, $session_id, 'Max retries exceeded');
                        return false;
                    }
                    
                    // Wait before retry (exponential backoff)
                    usleep(pow(2, $retry_count) * 100000); // 100ms, 200ms, 400ms
                    continue;
                }
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $wpdb->query('ROLLBACK');
                
                // Check if it's a deadlock error
                if (strpos($e->getMessage(), 'Deadlock') !== false || 
                    strpos($e->getMessage(), 'try restarting transaction') !== false) {
                    
                    $retry_count++;
                    
                    if ($retry_count >= $max_retries) {
                        self::log_activity('draft_save_deadlock', null, $session_id, 'Deadlock after ' . $retry_count . ' retries');
                        return false;
                    }
                    
                    // Wait before retry (exponential backoff)
                    usleep(pow(2, $retry_count) * 100000);
                    continue;
                } else {
                    // Non-deadlock error, log and return false
                    self::log_activity('draft_save_error', null, $session_id, 'Exception: ' . $e->getMessage());
                    return false;
                }
            }
        }
        
        // If all retries failed, try fallback method
        return self::save_draft_fallback($session_id, $form_data, $current_step, $client_email, $progress);
    }

    /**
     * Fallback method for saving drafts when the main method fails
     */
    private static function save_draft_fallback($session_id, $form_data, $current_step, $client_email, $progress) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cob_drafts';
        
        try {
            // Check if draft exists
            $existing_draft = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE session_id = %s",
                $session_id
            ));
            
            if ($existing_draft) {
                // Update existing draft
                $result = $wpdb->update(
                    $table_name,
                    [
                        'form_data' => wp_json_encode($form_data),
                        'current_step' => $current_step,
                        'progress_percentage' => $progress,
                        'client_email' => $client_email,
                        'last_saved' => current_time('mysql')
                    ],
                    ['session_id' => $session_id],
                    ['%s', '%d', '%d', '%s', '%s'],
                    ['%s']
                );
            } else {
                // Insert new draft
                $result = $wpdb->insert(
                    $table_name,
                    [
                        'session_id' => $session_id,
                        'form_data' => wp_json_encode($form_data),
                        'current_step' => $current_step,
                        'progress_percentage' => $progress,
                        'client_email' => $client_email,
                        'last_saved' => current_time('mysql')
                    ],
                    ['%s', '%s', '%d', '%d', '%s', '%s']
                );
            }
            
            if ($result !== false) {
                self::log_activity('draft_saved_fallback', null, $session_id, 'Draft saved using fallback method');
                return true;
            }
            
        } catch (Exception $e) {
            self::log_activity('draft_save_fallback_error', null, $session_id, 'Fallback method failed: ' . $e->getMessage());
        }
        
        return false;
    }

    public static function get_draft($session_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cob_drafts';
        
        $draft = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE session_id = %s",
            $session_id
        ));

        if ($draft) {
            return [
                'form_data' => json_decode($draft->form_data, true),
                'current_step' => $draft->current_step,
                'progress_percentage' => $draft->progress_percentage,
                'client_email' => $draft->client_email,
                'share_token' => $draft->share_token
            ];
        }

        return [];
    }

    public static function delete_draft($session_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cob_drafts';
        
        return $wpdb->delete(
            $table_name,
            ['session_id' => $session_id],
            ['%s']
        );
    }

    public static function get_submissions($limit = 20, $offset = 0, $status = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cob_submissions';
        
        $where = '';
        if (!empty($status)) {
            $where = $wpdb->prepare(" WHERE status = %s", $status);
        }

        $sql = "SELECT * FROM $table_name $where ORDER BY submitted_at DESC LIMIT %d OFFSET %d";
        
        return $wpdb->get_results($wpdb->prepare($sql, $limit, $offset));
    }

    public static function get_submission($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cob_submissions';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $id
        ));
    }

    public static function delete_submission($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cob_submissions';
        
        $result = $wpdb->delete(
            $table_name,
            ['id' => $id],
            ['%d']
        );

        if ($result) {
            self::log_activity('submission_deleted', $id);
        }

        return $result;
    }

    public static function log_activity($action, $submission_id = null, $session_id = null, $details = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cob_logs';
        
        return $wpdb->insert(
            $table_name,
            [
                'action' => $action,
                'submission_id' => $submission_id,
                'session_id' => $session_id,
                'details' => $details,
                'ip_address' => self::get_client_ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => current_time('mysql')
            ],
            ['%s', '%d', '%s', '%s', '%s', '%s', '%s']
        );
    }

    private static function get_client_ip() {
        $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    public static function cleanup_old_drafts($days = 30) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cob_drafts';
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE last_saved < %s",
            $cutoff_date
        ));
    }

    public static function generate_share_token($session_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cob_drafts';
        $token = 'share_' . wp_generate_password(32, false);
        
        $result = $wpdb->update(
            $table_name,
            ['share_token' => $token],
            ['session_id' => $session_id],
            ['%s'],
            ['%s']
        );
        
        if ($result !== false) {
            self::log_activity('share_token_generated', null, $session_id);
            return $token;
        }
        
        return false;
    }

    public static function get_draft_by_token($token) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cob_drafts';
        
        $draft = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE share_token = %s",
            $token
        ));

        if ($draft) {
            return [
                'session_id' => $draft->session_id,
                'form_data' => json_decode($draft->form_data, true),
                'current_step' => $draft->current_step,
                'progress_percentage' => $draft->progress_percentage,
                'client_email' => $draft->client_email,
                'last_saved' => $draft->last_saved
            ];
        }

        return false;
    }

    public static function get_all_drafts($limit = 50, $offset = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cob_drafts';
        
        $sql = "SELECT * FROM $table_name ORDER BY last_saved DESC LIMIT %d OFFSET %d";
        
        return $wpdb->get_results($wpdb->prepare($sql, $limit, $offset));
    }

    public static function calculate_progress($form_data, $current_step) {
        // Define required fields for each step
        $required_fields = [
            1 => ['project_name', 'business_name', 'primary_contact_name', 'primary_contact_email'],
            2 => ['current_website', 'technical_contact_name', 'technical_contact_email'],
            3 => ['reporting_frequency', 'reporting_contact_name', 'reporting_contact_email'],
            4 => ['target_audience', 'marketing_goals']
        ];
        
        $total_required = 0;
        $completed = 0;
        
        // Count completed fields up to current step
        for ($step = 1; $step <= $current_step; $step++) {
            if (isset($required_fields[$step])) {
                foreach ($required_fields[$step] as $field) {
                    $total_required++;
                    if (isset($form_data[$field]) && !empty(trim($form_data[$field]))) {
                        $completed++;
                    }
                }
            }
        }
        
        // Add step completion bonus
        $step_bonus = ($current_step - 1) * 5; // 5% bonus per completed step
        
        $percentage = $total_required > 0 ? round(($completed / $total_required) * 70) + $step_bonus : 0;
        
        return min(100, max(0, $percentage));
    }
}