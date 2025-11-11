<?php
/**
 * English Translations
 */

return [
    // General
    'app_name' => 'WHMCloudFlare',
    'app_description' => 'Automated DNS Record Management for Cloudflare',
    'settings' => 'Settings',
    'dashboard' => 'Dashboard',
    'advanced' => 'Advanced',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'delete' => 'Delete',
    'edit' => 'Edit',
    'view' => 'View',
    'search' => 'Search',
    'filter' => 'Filter',
    'export' => 'Export',
    'import' => 'Import',
    'test' => 'Test',
    'success' => 'Success',
    'error' => 'Error',
    'warning' => 'Warning',
    'info' => 'Information',
    'loading' => 'Loading...',
    'save_success' => 'Saved successfully',
    'save_error' => 'Error saving',
    'delete_success' => 'Deleted successfully',
    'delete_error' => 'Error deleting',
    
    // Connection Settings
    'connection_settings' => 'Cloudflare Connection Settings',
    'api_token' => 'API Token',
    'api_token_help' => 'Get your API Token from <a href="https://dash.cloudflare.com/profile/api-tokens" target="_blank">here</a>',
    'api_email' => 'API Email',
    'api_email_help' => 'Cloudflare account email (if using API Key)',
    'api_key' => 'API Key',
    'api_key_help' => 'Cloudflare API Key (if using API Key)',
    'zone_id' => 'Zone ID',
    'zone_id_help' => 'You can find Zone ID in your domain settings in Cloudflare',
    'load_zones' => 'Load Zones',
    'test_connection' => 'Test Connection',
    'connection_success' => 'Connection to Cloudflare successful',
    'connection_error' => 'Connection error',
    
    // Automation Settings
    'automation_settings' => 'Automation Settings',
    'enable_module' => 'Enable Module',
    'auto_create_a' => 'Auto Create A Record',
    'auto_create_aaaa' => 'Auto Create AAAA Record (IPv6)',
    'auto_create_www' => 'Auto Create www Record (CNAME)',
    'auto_create_mx' => 'Auto Create MX Records',
    'auto_create_txt' => 'Auto Create TXT Records',
    'proxied' => 'Enable Cloudflare Proxy (Orange Cloud)',
    'ttl' => 'TTL (Time To Live)',
    'ttl_help' => '1 = Auto (Cloudflare default)',
    
    // SSL/TLS
    'ssl_settings' => 'SSL/TLS Settings',
    'ssl_auto_manage' => 'Auto Manage SSL/TLS',
    'ssl_mode' => 'SSL Mode',
    'ssl_mode_off' => 'Off',
    'ssl_mode_flexible' => 'Flexible',
    'ssl_mode_full' => 'Full',
    'ssl_mode_strict' => 'Full Strict',
    'always_use_https' => 'Always Use HTTPS',
    'min_tls_version' => 'Minimum TLS Version',
    
    // Zone Management
    'zone_management' => 'Zone Management',
    'zone_mapping' => 'Zone Mapping',
    'add_zone_mapping' => 'Add Zone Mapping',
    'zone_pattern' => 'Domain Pattern',
    'zone_pattern_help' => 'Example: *.example.com or example.com',
    'zone_id_mapping' => 'Zone ID',
    
    // Cache
    'cache_settings' => 'Cache Settings',
    'cache_enabled' => 'Enable Cache',
    'cache_ttl' => 'Cache TTL (seconds)',
    'clear_cache' => 'Clear Cache',
    
    // Audit & Logs
    'audit_log' => 'Audit Log',
    'recent_logs' => 'Recent Logs',
    'view_logs' => 'View Logs',
    'audit_enabled' => 'Enable Audit Log',
    
    // Notifications
    'notification_settings' => 'Notification Settings',
    'notification_enabled' => 'Enable Notifications',
    'notification_email' => 'Notification Email',
    
    // Statistics
    'statistics' => 'Statistics',
    'total_operations' => 'Total Operations',
    'successful_operations' => 'Successful Operations',
    'failed_operations' => 'Failed Operations',
    'success_rate' => 'Success Rate',
    'daily_average' => 'Daily Average',
    'domains_managed' => 'Managed Domains',
    'operations_chart' => 'Operations Chart',
    
    // Export/Import
    'export_settings' => 'Export Settings',
    'export_description' => 'Save current settings as JSON file',
    'download_settings' => 'Download Settings',
    'import_settings' => 'Import Settings',
    'import_description' => 'Import settings from JSON file',
    'select_file' => 'Select Settings File',
    'import_success' => 'Settings imported successfully',
    'import_error' => 'Error importing settings',
    'invalid_file' => 'Invalid settings file',
    
    // Theme
    'theme' => 'Theme',
    'dark_mode' => 'Dark Mode',
    'light_mode' => 'Light Mode',
    'toggle_theme' => 'Toggle Theme',
    
    // Language
    'language' => 'Language',
    'persian' => 'فارسی',
    'english' => 'English',
    'change_language' => 'Change Language',
    
    // Messages
    'settings_saved' => 'Settings saved successfully',
    'settings_save_error' => 'Error saving settings',
    'operation_success' => 'Operation completed successfully',
    'operation_failed' => 'Operation failed',
    'confirm_delete' => 'Are you sure?',
    
    // Errors
    'error_connection' => 'Error connecting to Cloudflare',
    'error_zone_not_found' => 'Zone not found',
    'error_invalid_credentials' => 'Invalid credentials',
    'error_permission_denied' => 'Permission denied',
    'error_invalid_domain' => 'Invalid domain',
    'error_dns_record_exists' => 'DNS record already exists',
    
    // Success
    'success_dns_created' => 'DNS record created successfully',
    'success_dns_updated' => 'DNS record updated successfully',
    'success_dns_deleted' => 'DNS record deleted successfully',
    'success_ssl_configured' => 'SSL/TLS configured successfully',
    
    // API Messages
    'api_token_required' => 'API Token or API Key + Email is required',
    'zone_id_not_set' => 'Zone ID is not set',
    'cloudflare_connection_error' => 'Error connecting to Cloudflare',
    'cloudflare_unknown_error' => 'Unknown error from Cloudflare',
    'cloudflare_settings_incomplete' => 'Cloudflare settings are incomplete',
    'connection_successful' => 'Connection successful',
    
    // Logger Messages
    'log_start_dns_creation' => 'Starting DNS record creation for domain: :domain',
    'log_dns_created_success' => 'DNS records for domain :domain created successfully',
    'log_dns_creation_error' => 'Error creating DNS records for :domain: :error',
    'log_start_dns_deletion' => 'Starting DNS record deletion for domain: :domain',
    'log_dns_deleted_success' => 'DNS records for domain :domain deleted successfully',
    'log_dns_deletion_error' => 'Error deleting DNS records for :domain: :error',
    'log_record_created' => 'Record :type with name :name created',
    'log_record_exists' => 'Record :type with name :name already exists',
    'log_record_creation_error' => 'Error creating record :type for :name: :error',
    'log_ip_update' => 'Updating IP for domain: :domain to :ip',
    'log_ip_updated' => 'A record for :domain updated',
    'log_ip_update_error' => 'Error updating IP for :domain: :error',
    
    // SSL Messages
    'ssl_mode_invalid' => 'Invalid SSL Mode: :mode',
    'tls_version_invalid' => 'Invalid TLS Version: :version',
    
    // Zone Messages
    'zone_not_found_for_domain' => 'Zone ID not found for domain :domain',
    
    // Permission
    'permission_denied' => 'Permission denied',
    
    // Notification Email
    'notification_sent' => 'Notification email sent to: :email',
    'notification_send_error' => 'Error sending notification email to: :email',
    'notification_error_subject' => 'Error in WHMCloudFlare',
    'notification_success_subject' => 'Operation Successful - WHMCloudFlare',
    'notification_important_subject' => 'Important Notification - WHMCloudFlare: :title',
    'details' => 'Details',
];

