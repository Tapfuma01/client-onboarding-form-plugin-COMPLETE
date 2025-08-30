<?php
/**
 * Debug script for AJAX functionality
 * Place this in your plugin directory and access it directly to test
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if we can access the database
if (class_exists('COB_Database')) {
    echo "✅ COB_Database class exists<br>";
    
    // Test getting a draft by token
    if (method_exists('COB_Database', 'get_draft_by_token')) {
        echo "✅ get_draft_by_token method exists<br>";
        
        // Test with a sample token
        $test_token = 'test_token_123';
        $draft_data = COB_Database::get_draft_by_token($test_token);
        
        if ($draft_data) {
            echo "✅ Found draft data for test token<br>";
            echo "Draft data: " . print_r($draft_data, true) . "<br>";
        } else {
            echo "ℹ️ No draft found for test token (this is expected)<br>";
        }
    } else {
        echo "❌ get_draft_by_token method does not exist<br>";
    }
    
    // Test database connection
    global $wpdb;
    if ($wpdb->check_connection()) {
        echo "✅ Database connection is working<br>";
    } else {
        echo "❌ Database connection failed<br>";
    }
    
    // Check drafts table
    $table_name = $wpdb->prefix . 'cob_drafts';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    
    if ($table_exists) {
        echo "✅ Drafts table exists<br>";
        
        // Check share_token column
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'share_token'");
        if (!empty($column_exists)) {
            echo "✅ share_token column exists<br>";
        } else {
            echo "❌ share_token column does not exist<br>";
        }
        
        // Count drafts
        $draft_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        echo "📊 Total drafts in table: $draft_count<br>";
        
        // Show sample draft
        $sample_draft = $wpdb->get_row("SELECT * FROM $table_name LIMIT 1");
        if ($sample_draft) {
            echo "📋 Sample draft:<br>";
            echo "Session ID: " . $sample_draft->session_id . "<br>";
            echo "Share Token: " . ($sample_draft->share_token ?: 'NULL') . "<br>";
            echo "Current Step: " . $sample_draft->current_step . "<br>";
            echo "Last Saved: " . $sample_draft->last_saved . "<br>";
        } else {
            echo "ℹ️ No drafts found in table<br>";
        }
    } else {
        echo "❌ Drafts table does not exist<br>";
    }
    
} else {
    echo "❌ COB_Database class does not exist<br>";
}

// Test nonce creation
$form_nonce = wp_create_nonce('cob_form_nonce');
$admin_nonce = wp_create_nonce('cob_admin_nonce');

echo "<br><strong>Nonce Test:</strong><br>";
echo "Form nonce: $form_nonce<br>";
echo "Admin nonce: $admin_nonce<br>";

// Test nonce verification
if (wp_verify_nonce($form_nonce, 'cob_form_nonce')) {
    echo "✅ Form nonce verification works<br>";
} else {
    echo "❌ Form nonce verification failed<br>";
}

if (wp_verify_nonce($admin_nonce, 'cob_admin_nonce')) {
    echo "✅ Admin nonce verification works<br>";
} else {
    echo "❌ Admin nonce verification failed<br>";
}

echo "<br><strong>Plugin Constants:</strong><br>";
echo "COB_PLUGIN_PATH: " . (defined('COB_PLUGIN_PATH') ? COB_PLUGIN_PATH : 'NOT DEFINED') . "<br>";
echo "COB_PLUGIN_URL: " . (defined('COB_PLUGIN_URL') ? COB_PLUGIN_URL : 'NOT DEFINED') . "<br>";

echo "<br><strong>AJAX URL:</strong><br>";
echo "admin_url('admin-ajax.php'): " . admin_url('admin-ajax.php') . "<br>";

echo "<br><strong>Debug Complete!</strong>";
?>
