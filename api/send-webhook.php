<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit();
}

$userFunctions = new UserFunctions();
$userId = getCurrentUserId();

// Check credits only
if (isset($data['check_credits'])) {
    $userData = $userFunctions->getUserCredits($userId);
    $neededCredits = WEBHOOK_CREDIT_COST * ($data['amount'] ?? 1);
    
    echo json_encode([
        'success' => true,
        'has_enough_credits' => $userData['credits'] >= $neededCredits
    ]);
    exit();
}

// Send webhook
if (isset($data['webhook_url']) && isset($data['message'])) {
    // Check if user has enough credits
    $userData = $userFunctions->getUserCredits($userId);
    
    if ($userData['credits'] < WEBHOOK_CREDIT_COST) {
        echo json_encode(['success' => false, 'error' => 'Insufficient credits']);
        exit();
    }
    
    // Send webhook
    $webhook = new WebhookSender();
    $result = $webhook->sendMessage($data['webhook_url'], $data['message']);
    
    if ($result) {
        // Deduct credits
        $userFunctions->deductCredits($userId, WEBHOOK_CREDIT_COST);
        
        // Add to history
        $userFunctions->addToHistory(
            $userId,
            $data['webhook_url'],
            $data['message'],
            'success'
        );
        
        echo json_encode(['success' => true]);
    } else {
        // Add failed attempt to history
        $userFunctions->addToHistory(
            $userId,
            $data['webhook_url'],
            $data['message'],
            'failed'
        );
        
        echo json_encode(['success' => false, 'error' => 'Failed to send webhook']);
    }
    exit();
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>
