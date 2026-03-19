<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

$userFunctions = new UserFunctions();
$userId = getCurrentUserId();
$userData = $userFunctions->getUserCredits($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webhook Tool - <?php echo SITE_NAME; ?></title>
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
        <div class="card fade-in">
            <div class="card-header">
                <h2>Discord Webhook Sender</h2>
                <div>
                    <span class="status-indicator status-stopped" id="loop-status"></span>
                    <span id="status-text">Stopped</span>
                    <span style="margin-left: 1rem;">Messages Sent: <span id="sent-count">0</span></span>
                </div>
            </div>
            
            <!-- Credit Info -->
            <div style="background: rgba(0, 168, 255, 0.1); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <p>Each message costs <strong><?php echo WEBHOOK_CREDIT_COST; ?> credits</strong> | Your balance: <strong><?php echo $userData['credits']; ?> credits</strong></p>
                <p style="font-size: 0.9rem; color: var(--text-secondary);">You earn 3 credits every minute just by being online!</p>
            </div>
            
            <!-- Webhook Form -->
            <form id="webhook-form">
                <div class="form-group">
                    <label for="webhook-url">Discord Webhook URL</label>
                    <input type="url" class="form-control" id="webhook-url" placeholder="https://discord.com/api/webhooks/..." required>
                </div>
                
                <div class="form-group">
                    <label for="message">Message</label>
                    <div class="message-toolbar">
                        <button type="button" class="tool-btn" id="bold-btn">Bold</button>
                        <button type="button" class="tool-btn" id="italic-btn">Italic</button>
                        <button type="button" class="tool-btn" id="underline-btn">Underline</button>
                        <button type="button" class="tool-btn" id="code-btn">Code</button>
                        <button type="button" class="tool-btn" id="codeblock-btn">Code Block</button>
                        <button type="button" class="tool-btn" id="spoiler-btn">Spoiler</button>
                    </div>
                    <textarea class="form-control" id="message" placeholder="Enter your message here..." required></textarea>
                </div>
                
                <div class="tool-controls">
                    <div class="control-group">
                        <label for="amount">Number of Messages</label>
                        <input type="number" class="form-control" id="amount" min="1" max="100" value="1">
                    </div>
                    
                    <div class="control-group">
                        <label for="delay">Delay (seconds)</label>
                        <input type="number" class="form-control" id="delay" min="1" max="60" value="1">
                    </div>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-primary" onclick="sendSingleMessage()">Send Single Message</button>
                    <button type="button" class="btn btn-success" id="start-btn" onclick="startLoop()">Start Loop</button>
                    <button type="button" class="btn btn-danger" id="stop-btn" onclick="stopLoop()" disabled>Stop Loop</button>
                </div>
            </form>
        </div>
        
        <!-- Instructions -->
        <div class="card fade-in">
            <div class="card-header">
                <h2>Instructions</h2>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <div>
                    <h3 style="color: var(--accent-blue); margin-bottom: 0.5rem;">1. Get Webhook URL</h3>
                    <p style="color: var(--text-secondary);">Create a webhook in your Discord server settings and copy the URL.</p>
                </div>
                
                <div>
                    <h3 style="color: var(--accent-blue); margin-bottom: 0.5rem;">2. Format Message</h3>
                    <p style="color: var(--text-secondary);">Use the toolbar to add formatting. You can also use Discord markdown.</p>
                </div>
                
                <div>
                    <h3 style="color: var(--accent-blue); margin-bottom: 0.5rem;">3. Set Parameters</h3>
                    <p style="color: var(--text-secondary);">Choose how many messages to send and delay between them.</p>
                </div>
                
                <div>
                    <h3 style="color: var(--accent-blue); margin-bottom: 0.5rem;">4. Credits</h3>
                    <p style="color: var(--text-secondary);">Each message costs <?php echo WEBHOOK_CREDIT_COST; ?> credits. You earn 3 credits every minute.</p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>
