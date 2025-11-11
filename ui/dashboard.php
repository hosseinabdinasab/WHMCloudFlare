<?php
/**
 * داشبورد آماری WHMCloudFlare
 */

require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/WHMCloudFlare.php';
require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/Config.php';
require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/Statistics.php';
require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/AuditLogger.php';
require_once '/usr/local/cpanel/whm/addons/WHMCloudFlare/lib/Language.php';

if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'root') {
    die('دسترسی غیرمجاز');
}

// بارگذاری سیستم زبان
$lang = Language::getInstance();

// پردازش تغییر زبان
if (isset($_GET['lang']) && in_array($_GET['lang'], ['fa', 'en'])) {
    $lang->setLanguage($_GET['lang']);
    header('Location: ' . str_replace('?lang=' . $_GET['lang'], '', $_SERVER['REQUEST_URI']));
    exit;
}

$statistics = new Statistics();
$auditLogger = new AuditLogger();

$summary = $statistics->getSummary(30);
$operationStats = $statistics->getOperationStats(null, 30);
$recentAudits = $auditLogger->readLogs(20);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLanguage(); ?>" dir="<?php echo $lang->getDirection(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('dashboard'); ?> - <?php echo __('app_name'); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 14px;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        
        .stat-card .value {
            font-size: 36px;
            font-weight: bold;
        }
        
        .chart-container {
            padding: 30px;
            margin: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .audit-log {
            padding: 30px;
            max-height: 500px;
            overflow-y: auto;
        }
        
        .audit-entry {
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border-right: 4px solid #667eea;
        }
        
        .audit-entry.success {
            border-right-color: #28a745;
        }
        
        .audit-entry.failed {
            border-right-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="position: absolute; top: 20px; left: 20px;">
                <select id="languageSelect" onchange="changeLanguage(this.value)" style="padding: 8px; border-radius: 5px; border: none; background: rgba(255,255,255,0.2); color: white;">
                    <option value="fa" <?php echo $lang->getCurrentLanguage() === 'fa' ? 'selected' : ''; ?>>فارسی</option>
                    <option value="en" <?php echo $lang->getCurrentLanguage() === 'en' ? 'selected' : ''; ?>>English</option>
                </select>
            </div>
            <h1>📊 <?php echo __('dashboard'); ?> <?php echo __('app_name'); ?></h1>
            <p><?php echo __('statistics'); ?></p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo __('total_operations'); ?> (30 <?php echo $lang->getCurrentLanguage() === 'fa' ? 'روز' : 'days'; ?>)</h3>
                <div class="value"><?php echo number_format($summary['total_operations']); ?></div>
            </div>
            
            <div class="stat-card">
                <h3><?php echo __('successful_operations'); ?></h3>
                <div class="value"><?php echo number_format($summary['successful_operations']); ?></div>
            </div>
            
            <div class="stat-card">
                <h3><?php echo __('failed_operations'); ?></h3>
                <div class="value"><?php echo number_format($summary['failed_operations']); ?></div>
            </div>
            
            <div class="stat-card">
                <h3><?php echo __('success_rate'); ?></h3>
                <div class="value"><?php echo $summary['success_rate']; ?>%</div>
            </div>
            
            <div class="stat-card">
                <h3><?php echo __('daily_average'); ?></h3>
                <div class="value"><?php echo number_format($summary['daily_average'], 1); ?></div>
            </div>
            
            <div class="stat-card">
                <h3><?php echo __('domains_managed'); ?></h3>
                <div class="value"><?php echo number_format($summary['domains_managed']); ?></div>
            </div>
        </div>
        
        <div class="chart-container">
            <h2><?php echo __('operations_chart'); ?></h2>
            <canvas id="operationsChart"></canvas>
        </div>
        
        <div class="audit-log">
            <h2><?php echo __('audit_log'); ?> <?php echo $lang->getCurrentLanguage() === 'fa' ? 'اخیر' : 'Recent'; ?></h2>
            <?php foreach ($recentAudits as $audit): ?>
                <div class="audit-entry <?php echo $audit['status']; ?>">
                    <strong><?php echo htmlspecialchars($audit['action']); ?></strong>
                    <span style="float: left;"><?php echo htmlspecialchars($audit['timestamp']); ?></span>
                    <br>
                    <small><?php echo $lang->getCurrentLanguage() === 'fa' ? 'کاربر' : 'User'; ?>: <?php echo htmlspecialchars($audit['user']); ?> | IP: <?php echo htmlspecialchars($audit['ip']); ?></small>
                    <?php if (!empty($audit['details'])): ?>
                        <pre style="margin-top: 10px; font-size: 12px;"><?php echo htmlspecialchars(json_encode($audit['details'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script>
        const ctx = document.getElementById('operationsChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($operationStats)); ?>,
                datasets: [{
                    label: '<?php echo __('successful_operations'); ?>',
                    data: <?php echo json_encode(array_column($operationStats, 'success')); ?>,
                    backgroundColor: '#28a745'
                }, {
                    label: '<?php echo __('failed_operations'); ?>',
                    data: <?php echo json_encode(array_column($operationStats, 'failed')); ?>,
                    backgroundColor: '#dc3545'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        function changeLanguage(lang) {
            window.location.href = '?lang=' + lang;
        }
    </script>
</body>
</html>

