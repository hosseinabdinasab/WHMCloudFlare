<?php
/**
 * ترجمه‌های فارسی
 */

return [
    // عمومی
    'app_name' => 'WHMCloudFlare',
    'app_description' => 'مدیریت خودکار رکوردهای DNS در Cloudflare',
    'settings' => 'تنظیمات',
    'dashboard' => 'داشبورد',
    'advanced' => 'پیشرفته',
    'save' => 'ذخیره',
    'cancel' => 'لغو',
    'delete' => 'حذف',
    'edit' => 'ویرایش',
    'view' => 'مشاهده',
    'search' => 'جستجو',
    'filter' => 'فیلتر',
    'export' => 'خروجی',
    'import' => 'وارد کردن',
    'test' => 'تست',
    'success' => 'موفقیت',
    'error' => 'خطا',
    'warning' => 'هشدار',
    'info' => 'اطلاعات',
    'loading' => 'در حال بارگذاری...',
    'save_success' => 'ذخیره شد',
    'save_error' => 'خطا در ذخیره',
    'delete_success' => 'حذف شد',
    'delete_error' => 'خطا در حذف',
    
    // تنظیمات اتصال
    'connection_settings' => 'تنظیمات اتصال به Cloudflare',
    'api_token' => 'API Token',
    'api_token_help' => 'برای دریافت API Token به <a href="https://dash.cloudflare.com/profile/api-tokens" target="_blank">این لینک</a> مراجعه کنید',
    'api_email' => 'API Email',
    'api_email_help' => 'ایمیل حساب Cloudflare (اگر از API Key استفاده می‌کنید)',
    'api_key' => 'API Key',
    'api_key_help' => 'API Key Cloudflare (اگر از API Key استفاده می‌کنید)',
    'zone_id' => 'Zone ID',
    'zone_id_help' => 'Zone ID را می‌توانید از تنظیمات دامنه در Cloudflare دریافت کنید',
    'load_zones' => 'بارگذاری Zone ها',
    'test_connection' => 'تست اتصال',
    'connection_success' => 'اتصال به Cloudflare موفقیت‌آمیز بود',
    'connection_error' => 'خطا در اتصال',
    
    // تنظیمات خودکارسازی
    'automation_settings' => 'تنظیمات خودکارسازی',
    'enable_module' => 'فعال کردن ماژول',
    'auto_create_a' => 'ایجاد خودکار رکورد A',
    'auto_create_aaaa' => 'ایجاد خودکار رکورد AAAA (IPv6)',
    'auto_create_www' => 'ایجاد خودکار رکورد www (CNAME)',
    'auto_create_mx' => 'ایجاد خودکار رکوردهای MX',
    'auto_create_txt' => 'ایجاد خودکار رکوردهای TXT',
    'proxied' => 'فعال کردن Cloudflare Proxy (Orange Cloud)',
    'ttl' => 'TTL (Time To Live)',
    'ttl_help' => '1 = Auto (پیش‌فرض Cloudflare)',
    
    // SSL/TLS
    'ssl_settings' => 'تنظیمات SSL/TLS',
    'ssl_auto_manage' => 'مدیریت خودکار SSL/TLS',
    'ssl_mode' => 'SSL Mode',
    'ssl_mode_off' => 'غیرفعال',
    'ssl_mode_flexible' => 'Flexible',
    'ssl_mode_full' => 'Full',
    'ssl_mode_strict' => 'Full Strict',
    'always_use_https' => 'همیشه از HTTPS استفاده کن',
    'min_tls_version' => 'حداقل نسخه TLS',
    
    // Zone Management
    'zone_management' => 'مدیریت Zone ها',
    'zone_mapping' => 'Zone Mapping',
    'add_zone_mapping' => 'افزودن Zone Mapping',
    'zone_pattern' => 'الگوی دامنه',
    'zone_pattern_help' => 'مثال: *.example.com یا example.com',
    'zone_id_mapping' => 'Zone ID',
    
    // Cache
    'cache_settings' => 'تنظیمات Cache',
    'cache_enabled' => 'فعال کردن Cache',
    'cache_ttl' => 'Cache TTL (ثانیه)',
    'clear_cache' => 'پاک کردن Cache',
    
    // Audit & Logs
    'audit_log' => 'Audit Log',
    'recent_logs' => 'لاگ‌های اخیر',
    'view_logs' => 'مشاهده لاگ‌ها',
    'audit_enabled' => 'فعال کردن Audit Log',
    
    // Notifications
    'notification_settings' => 'تنظیمات اعلان‌ها',
    'notification_enabled' => 'فعال کردن اعلان‌ها',
    'notification_email' => 'ایمیل اعلان',
    
    // Statistics
    'statistics' => 'آمار',
    'total_operations' => 'کل عملیات',
    'successful_operations' => 'عملیات موفق',
    'failed_operations' => 'عملیات ناموفق',
    'success_rate' => 'نرخ موفقیت',
    'daily_average' => 'میانگین روزانه',
    'domains_managed' => 'دامنه‌های مدیریت شده',
    'operations_chart' => 'نمودار عملیات',
    
    // Export/Import
    'export_settings' => 'Export تنظیمات',
    'export_description' => 'ذخیره تنظیمات فعلی به صورت فایل JSON',
    'download_settings' => 'دانلود تنظیمات',
    'import_settings' => 'Import تنظیمات',
    'import_description' => 'وارد کردن تنظیمات از فایل JSON',
    'select_file' => 'انتخاب فایل تنظیمات',
    'import_success' => 'تنظیمات با موفقیت وارد شد',
    'import_error' => 'خطا در وارد کردن تنظیمات',
    'invalid_file' => 'فایل تنظیمات نامعتبر است',
    
    // Theme
    'theme' => 'تم',
    'dark_mode' => 'حالت تاریک',
    'light_mode' => 'حالت روشن',
    'toggle_theme' => 'تغییر تم',
    
    // Language
    'language' => 'زبان',
    'persian' => 'فارسی',
    'english' => 'English',
    'change_language' => 'تغییر زبان',
    
    // Messages
    'settings_saved' => 'تنظیمات با موفقیت ذخیره شد',
    'settings_save_error' => 'خطا در ذخیره تنظیمات',
    'operation_success' => 'عملیات با موفقیت انجام شد',
    'operation_failed' => 'عملیات ناموفق بود',
    'confirm_delete' => 'آیا مطمئن هستید؟',
    
    // Errors
    'error_connection' => 'خطا در اتصال به Cloudflare',
    'error_zone_not_found' => 'Zone یافت نشد',
    'error_invalid_credentials' => 'اعتبارنامه‌ها نامعتبر است',
    'error_permission_denied' => 'دسترسی غیرمجاز',
    'error_invalid_domain' => 'دامنه نامعتبر است',
    'error_dns_record_exists' => 'رکورد DNS از قبل وجود دارد',
    
    // Success
    'success_dns_created' => 'رکورد DNS با موفقیت ایجاد شد',
    'success_dns_updated' => 'رکورد DNS با موفقیت به‌روزرسانی شد',
    'success_dns_deleted' => 'رکورد DNS با موفقیت حذف شد',
    'success_ssl_configured' => 'SSL/TLS با موفقیت پیکربندی شد',
    
    // API Messages
    'api_token_required' => 'API Token یا API Key + Email الزامی است',
    'zone_id_not_set' => 'Zone ID تنظیم نشده است',
    'cloudflare_connection_error' => 'خطا در ارتباط با Cloudflare',
    'cloudflare_unknown_error' => 'خطای نامشخص از Cloudflare',
    'cloudflare_settings_incomplete' => 'تنظیمات Cloudflare کامل نیست',
    'connection_successful' => 'اتصال موفقیت‌آمیز بود',
    
    // Logger Messages
    'log_start_dns_creation' => 'شروع ایجاد رکوردهای DNS برای دامنه: :domain',
    'log_dns_created_success' => 'رکوردهای DNS برای دامنه :domain با موفقیت ایجاد شدند',
    'log_dns_creation_error' => 'خطا در ایجاد رکوردهای DNS برای :domain: :error',
    'log_start_dns_deletion' => 'شروع حذف رکوردهای DNS برای دامنه: :domain',
    'log_dns_deleted_success' => 'رکوردهای DNS برای دامنه :domain با موفقیت حذف شدند',
    'log_dns_deletion_error' => 'خطا در حذف رکوردهای DNS برای :domain: :error',
    'log_record_created' => 'رکورد :type با نام :name ایجاد شد',
    'log_record_exists' => 'رکورد :type با نام :name از قبل وجود دارد',
    'log_record_creation_error' => 'خطا در ایجاد رکورد :type برای :name: :error',
    'log_ip_update' => 'به‌روزرسانی IP برای دامنه: :domain به :ip',
    'log_ip_updated' => 'رکورد A برای :domain به‌روزرسانی شد',
    'log_ip_update_error' => 'خطا در به‌روزرسانی IP برای :domain: :error',
    
    // SSL Messages
    'ssl_mode_invalid' => 'SSL Mode نامعتبر: :mode',
    'tls_version_invalid' => 'TLS Version نامعتبر: :version',
    
    // Zone Messages
    'zone_not_found_for_domain' => 'Zone ID برای دامنه :domain یافت نشد',
    
    // Permission
    'permission_denied' => 'دسترسی غیرمجاز',
    
    // Notification Email
    'notification_sent' => 'ایمیل اعلان ارسال شد به: :email',
    'notification_send_error' => 'خطا در ارسال ایمیل اعلان به: :email',
    'notification_error_subject' => 'خطا در WHMCloudFlare',
    'notification_success_subject' => 'عملیات موفق - WHMCloudFlare',
    'notification_important_subject' => 'اعلان مهم - WHMCloudFlare: :title',
    'details' => 'جزئیات',
];

