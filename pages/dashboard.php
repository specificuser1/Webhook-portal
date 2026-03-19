<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

$userFunctions = new UserFunctions();
$userId = getCurrentUserId();

// Check and add passive credits
$creditsAdded = $userFunctions->checkAndAddPassiveCredits($userId);
$userData = $userFunctions->getUserCredits($userId);
$history = $userFunctions->getHistory($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-brand">
            <a href="dashboard.php"><?php echo SITE_NAME; ?></a>
        </div>
        <ul class="nav-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="webhook-tool.php">Webhook Tool</a></li>
            <li class="credit-display" id="credit-display"><?php echo $userData['credits']; ?> Credits</li>
            <li><a href="#" onclick="logout()">Logout</a></li>
        </ul>
    </nav>
    
    <!-- Main Content -->
    <div class="container">
        <!-- Welcome Card -->
        <div class="card fade-in">
            <div class="card-header">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                <?php if ($creditsAdded > 0): ?>
                    <span style="color: var(--success);">+<?php echo $creditsAdded; ?> credits earned</span>
                <?php endif; ?>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div style="background: rgba(0, 168, 255, 0.1); padding: 1.5rem; border-radius: 10px; text-align: center;">
                    <h3 style="color: var(--accent-blue);">Total Credits</h3>
                    <p style="font-size: 2rem; font-weight: bold;"><?php echo $userData['credits']; ?></p>
                </div>
                
                <div style="background: rgba(157, 78, 221, 0.1); padding: 1.5rem; border-radius: 10px; text-align: center;">
                    <h3 style="color: var(--accent-purple);">Messages Sent</h3>
                    <p style="font-size: 2rem; font-weight: bold;"><?php echo count($history); ?></p>
                </div>
                
                <div style="background: rgba(0, 214, 143, 0.1); padding: 1.5rem; border-radius: 10px; text-align: center;">
                    <h3 style="color: var(--success);">Credit Rate</h3>
                    <p style="font-size: 1.5rem;">+3/min</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card fade-in">
            <div class="card-header">
                <h2>Quick Actions</h2>
            </div>
            
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="webhook-tool.php" class="btn btn-primary">Send Webhook</a>
                <button class="btn btn-success" onclick="updateCredits()">Refresh Credits</button>
            </div>
        </div>
        
        <!-- Recent History -->
        <div class="card fade-in">
            <div class="card-header">
                <h2>Recent Activity</h2>
            </div>
            
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Webhook</th>
                        <th>Message</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="history-body">
                    <?php foreach ($history as $item): ?>
                    <tr>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($item['created_at'])); ?></td>
                        <td><?php echo substr($item['webhook_url'], 0, 30); ?>...</td>
                        <td><?php echo substr($item['message'], 0, 50); ?>...</td>
                        <td class="status-<?php echo $item['status']; ?>"><?php echo $item['status']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
