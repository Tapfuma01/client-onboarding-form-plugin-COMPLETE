<?php
/**
 * Admin logs page - Activity monitoring and debugging
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current page and filters
$current_page = max(1, intval($_GET['paged'] ?? 1));
$per_page = 50;
$offset = ($current_page - 1) * $per_page;
$action_filter = sanitize_text_field($_GET['action_filter'] ?? '');

// Get logs from database
global $wpdb;
$logs_table = $wpdb->prefix . 'cob_logs';

// Build query with filters
$where_clause = '';
$query_params = [];

if (!empty($action_filter)) {
    $where_clause = ' WHERE action = %s';
    $query_params[] = $action_filter;
}

// Get total count for pagination
$total_query = "SELECT COUNT(*) FROM $logs_table $where_clause";
$total_logs = $wpdb->get_var(!empty($query_params) ? 
    $wpdb->prepare($total_query, $query_params) : $total_query);

// Get logs for current page
$logs_query = "SELECT * FROM $logs_table $where_clause ORDER BY created_at DESC LIMIT %d OFFSET %d";
$final_params = array_merge($query_params, [$per_page, $offset]);
$logs = $wpdb->get_results($wpdb->prepare($logs_query, $final_params));

// Get available actions for filter
$actions_query = "SELECT DISTINCT action FROM $logs_table ORDER BY action";
$available_actions = $wpdb->get_col($actions_query);

$total_pages = ceil($total_logs / $per_page);
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Activity Logs</h1>
    <p class="description">Monitor form submission activity, errors, and debugging information.</p>

    <!-- Filters -->
    <div class="tablenav top">
        <form method="get" action="">
            <input type="hidden" name="page" value="cob-logs">
            
            <label for="action_filter" class="screen-reader-text">Filter by action</label>
            <select name="action_filter" id="action_filter">
                <option value="">All Actions</option>
                <?php foreach ($available_actions as $action): ?>
                    <option value="<?php echo esc_attr($action); ?>" 
                            <?php selected($action_filter, $action); ?>>
                        <?php echo esc_html(ucwords(str_replace('_', ' ', $action))); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <?php submit_button('Filter', 'secondary', 'filter', false); ?>
            
            <?php if (!empty($action_filter)): ?>
                <a href="<?php echo admin_url('admin.php?page=cob-logs'); ?>" class="button">Clear Filter</a>
            <?php endif; ?>
        </form>

        <!-- Statistics -->
        <div class="alignright">
            <span class="displaying-num"><?php echo number_format($total_logs); ?> items</span>
        </div>
    </div>

    <!-- Logs Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="column-date">Date & Time</th>
                <th scope="col" class="column-action">Action</th>
                <th scope="col" class="column-session">Session ID</th>
                <th scope="col" class="column-submission">Submission</th>
                <th scope="col" class="column-details">Details</th>
                <th scope="col" class="column-ip">IP Address</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">
                        No logs found. Activity will appear here when users interact with forms.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="column-date">
                            <strong><?php echo esc_html(date('Y-m-d H:i:s', strtotime($log->created_at))); ?></strong>
                            <br><small><?php echo esc_html(human_time_diff(strtotime($log->created_at))); ?> ago</small>
                        </td>
                        
                        <td class="column-action">
                            <span class="log-action log-action-<?php echo esc_attr(str_replace('_', '-', $log->action)); ?>">
                                <?php echo esc_html(ucwords(str_replace('_', ' ', $log->action))); ?>
                            </span>
                        </td>
                        
                        <td class="column-session">
                            <?php if (!empty($log->session_id)): ?>
                                <code><?php echo esc_html(substr($log->session_id, 0, 20)); ?>...</code>
                            <?php else: ?>
                                <span class="description">—</span>
                            <?php endif; ?>
                        </td>
                        
                        <td class="column-submission">
                            <?php if (!empty($log->submission_id)): ?>
                                <a href="<?php echo admin_url('admin.php?page=cob-submissions&view=' . $log->submission_id); ?>">
                                    #<?php echo esc_html($log->submission_id); ?>
                                </a>
                            <?php else: ?>
                                <span class="description">—</span>
                            <?php endif; ?>
                        </td>
                        
                        <td class="column-details">
                            <?php if (!empty($log->details)): ?>
                                <div class="log-details">
                                    <?php echo esc_html($log->details); ?>
                                </div>
                            <?php else: ?>
                                <span class="description">—</span>
                            <?php endif; ?>
                        </td>
                        
                        <td class="column-ip">
                            <code><?php echo esc_html($log->ip_address); ?></code>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num"><?php echo number_format($total_logs); ?> items</span>
                
                <?php
                $page_links = paginate_links([
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total' => $total_pages,
                    'current' => $current_page,
                    'type' => 'array'
                ]);

                if ($page_links):
                    echo '<span class="pagination-links">';
                    foreach ($page_links as $link) {
                        echo $link;
                    }
                    echo '</span>';
                endif;
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.log-action {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: #fff;
}

.log-action-submission-attempt { background-color: #0073aa; }
.log-action-submission-completed { background-color: #46b450; }
.log-action-submission-validation-failed { background-color: #dc3232; }
.log-action-submission-db-error { background-color: #dc3232; }
.log-action-submission-exception { background-color: #dc3232; }
.log-action-draft-saved { background-color: #00a0d2; }
.log-action-draft-save-failed { background-color: #ffb900; }
.log-action-submission-created { background-color: #46b450; }
.log-action-submission-deleted { background-color: #dc3232; }

.log-details {
    max-width: 300px;
    word-wrap: break-word;
    font-family: 'Courier New', monospace;
    font-size: 12px;
}

.column-date { width: 160px; }
.column-action { width: 150px; }
.column-session { width: 120px; }
.column-submission { width: 80px; }
.column-details { width: auto; }
.column-ip { width: 120px; }
</style>