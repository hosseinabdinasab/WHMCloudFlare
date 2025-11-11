<?php
/**
 * کلاس مدیریت اعلان‌ها
 */

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/Language.php';

class Notification {
    private $config;
    private $logger;
    
    public function __construct() {
        $this->config = new Config();
        $this->logger = new Logger();
    }
    
    /**
     * ارسال اعلان ایمیل
     */
    public function sendEmail($subject, $message, $to = null) {
        $settings = $this->config->getSettings();
        
        if (!($settings['notification_enabled'] ?? false)) {
            return false;
        }
        
        $to = $to ?? $settings['notification_email'] ?? null;
        if (empty($to)) {
            return false;
        }
        
        $headers = [
            'From: WHMCloudFlare <noreply@' . $_SERVER['SERVER_NAME'] . '>',
            'Content-Type: text/html; charset=UTF-8',
            'X-Mailer: WHMCloudFlare'
        ];
        
        $lang = Language::getInstance();
        $direction = $lang->getDirection();
        
        $htmlMessage = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; direction: $direction; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 5px 5px 0 0; }
                .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 5px 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>" . $lang->translate('app_name') . "</h2>
                </div>
                <div class='content'>
                    $message
                </div>
            </div>
        </body>
        </html>
        ";
        
        $result = mail($to, $subject, $htmlMessage, implode("\r\n", $headers));
        
        if ($result) {
            $lang = Language::getInstance();
            $this->logger->info($lang->translate('notification_sent', ['email' => $to]));
        } else {
            $lang = Language::getInstance();
            $this->logger->error($lang->translate('notification_send_error', ['email' => $to]));
        }
        
        return $result;
    }
    
    /**
     * اعلان خطا
     */
    public function notifyError($error, $details = []) {
        $lang = Language::getInstance();
        $subject = $lang->translate('notification_error_subject');
        $errorLabel = $lang->translate('error');
        $detailsLabel = $lang->translate('details');
        $message = "<h3>$errorLabel:</h3><p>$error</p>";
        
        if (!empty($details)) {
            $message .= "<h4>$detailsLabel:</h4><pre>" . print_r($details, true) . "</pre>";
        }
        
        return $this->sendEmail($subject, $message);
    }
    
    /**
     * اعلان موفقیت
     */
    public function notifySuccess($message, $details = []) {
        $lang = Language::getInstance();
        $subject = $lang->translate('notification_success_subject');
        $detailsLabel = $lang->translate('details');
        $content = "<h3>$message</h3>";
        
        if (!empty($details)) {
            $content .= "<h4>$detailsLabel:</h4><pre>" . print_r($details, true) . "</pre>";
        }
        
        return $this->sendEmail($subject, $content);
    }
    
    /**
     * اعلان تغییرات مهم
     */
    public function notifyImportant($title, $message, $details = []) {
        $lang = Language::getInstance();
        $subject = $lang->translate('notification_important_subject', ['title' => $title]);
        $detailsLabel = $lang->translate('details');
        $content = "<h3>$title</h3><p>$message</p>";
        
        if (!empty($details)) {
            $content .= "<h4>$detailsLabel:</h4><pre>" . print_r($details, true) . "</pre>";
        }
        
        return $this->sendEmail($subject, $content);
    }
}

