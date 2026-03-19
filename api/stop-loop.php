<?php
session_start();

header('Content-Type: application/json');

// Simple session-based loop tracking
if (!isset($_SESSION['loop_data'])) {
    $_SESSION['loop_data'] = [
        'is_running' => false,
        'webhook_url' => '',
        'message' => '',
        'amount' => 0,
        'delay' => 0
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['check'])) {
        echo json_encode($_SESSION['loop_data']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['stop'])) {
        $_SESSION['loop_data']['is_running'] = false;
        echo json_encode(['success' => true]);
    } elseif (isset($data['start'])) {
        $_SESSION['loop_data'] = [
            'is_running' => true,
            'webhook_url' => $data['webhook_url'],
            'message' => $data['message'],
            'amount' => $data['amount'],
            'delay' => $data['delay']
        ];
        echo json_encode(['success' => true]);
    }
}
?>
