# Database Deadlock Fixes

This document outlines the fixes implemented to resolve database deadlock issues in the Client Onboarding Form plugin.

## Problem Description

The plugin was experiencing database deadlock errors when multiple users tried to save drafts simultaneously. The error message was:

```
WordPress database error Deadlock found when trying to get lock; try restarting transaction
```

## Root Causes

1. **Concurrent Access**: Multiple users saving drafts at the same time
2. **Table Locks**: Database table locks preventing concurrent operations
3. **Transaction Conflicts**: Database transactions conflicting with each other
4. **Missing Retry Logic**: No fallback mechanism when deadlocks occur

## Implemented Solutions

### 1. Enhanced Database Operations

- **Replaced `REPLACE INTO` with `INSERT ... ON DUPLICATE KEY UPDATE`**: This approach is more efficient and less likely to cause deadlocks
- **Added Transaction Management**: Proper transaction handling with START/COMMIT/ROLLBACK
- **Implemented Retry Logic**: Automatic retry with exponential backoff when deadlocks occur

### 2. Deadlock Prevention

- **Random Delays**: Added small random delays to reduce concurrent access conflicts
- **Connection Checks**: Verify database connection before operations
- **Table Lock Detection**: Check for and resolve table locks before operations

### 3. Fallback Mechanisms

- **Alternative Save Method**: If the main method fails, use a fallback approach
- **Process Killing**: Automatically kill locked database processes
- **Table Optimization**: Regular table optimization to reduce deadlock likelihood

### 4. Frontend Improvements

- **Retry Logic**: JavaScript retry mechanism for failed requests
- **Better Error Handling**: User-friendly error messages with retry suggestions
- **Progress Indicators**: Visual feedback during save operations

## Database Maintenance Tools

The admin panel now includes tools to:

- **Optimize Tables**: Run `OPTIMIZE TABLE` and `ANALYZE TABLE` commands
- **Check Table Locks**: Detect and resolve table locks
- **Cleanup Old Drafts**: Remove old drafts to reduce table size
- **Monitor Performance**: Track database operation success rates

## Configuration Options

- **Retry Attempts**: Configurable number of retry attempts (default: 3)
- **Retry Delays**: Exponential backoff delays (100ms, 200ms, 400ms)
- **Draft Retention**: Configurable draft cleanup schedule (default: 30 days)
- **Maintenance Schedule**: Daily database maintenance tasks

## Usage Instructions

### For Administrators

1. Go to **Client Onboarding Form > Drafts** in the WordPress admin
2. Use the **Database Maintenance** section to:
   - Optimize tables
   - Check for table locks
   - Clean up old drafts

### For Developers

The enhanced `COB_Database::save_draft()` method now includes:

```php
// Automatic retry with exponential backoff
$result = COB_Database::save_draft($session_id, $form_data, $current_step, $client_email);

// Manual table optimization
COB_Database::optimize_tables();

// Check for table locks
COB_Database::check_table_locks();
```

## Monitoring and Logging

All database operations are logged with:

- **Success/Failure Status**: Track operation success rates
- **Error Details**: Detailed error messages for debugging
- **Performance Metrics**: Operation timing and retry counts
- **Lock Detection**: Automatic detection of table locks

## Best Practices

1. **Regular Maintenance**: Run database optimization weekly
2. **Monitor Logs**: Check for recurring deadlock patterns
3. **Update Settings**: Adjust retry attempts and delays based on usage patterns
4. **Backup Data**: Always backup before running maintenance operations

## Troubleshooting

### If Deadlocks Persist

1. **Check Table Locks**: Use the admin tool to detect locks
2. **Optimize Tables**: Run table optimization
3. **Review Logs**: Check for patterns in deadlock occurrences
4. **Adjust Settings**: Increase retry attempts or delays
5. **Contact Host**: Some hosting providers have specific database configurations

### Performance Tuning

- **Reduce Concurrent Users**: Limit simultaneous form submissions
- **Increase Retry Delays**: Add longer delays between retry attempts
- **Optimize Database**: Regular table maintenance and optimization
- **Monitor Resources**: Check server CPU and memory usage

## Future Improvements

- **Queue System**: Implement a job queue for draft saves
- **Connection Pooling**: Better database connection management
- **Caching Layer**: Add Redis/Memcached for frequently accessed data
- **Load Balancing**: Distribute database load across multiple servers

## Support

If you continue to experience deadlock issues:

1. Check the plugin logs for detailed error information
2. Run the database maintenance tools
3. Contact your hosting provider about database configuration
4. Submit an issue with detailed error logs and system information
