<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$userFunctions = new UserFunctions();
$userId = getCurrentUserId();

$added = $userFunctions->checkAndAddPassiveCredits($userId);
$userData = $userFunctions->getUserCredits($userId);

echo json_encode([
    'success' => true,
    'credits' => $userData['credits'],
    'added' => $added
]);
?>
