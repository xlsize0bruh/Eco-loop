<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user = $_SESSION['user'];
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'list') {
        $tradeId = $_GET['trade_id'] ?? '';
        $messages = readJson('messages.json');

        $tradeMsgs = array_filter($messages, function($m) use ($tradeId) {
            return $m['trade_id'] === $tradeId;
        });

        echo json_encode(['success' => true, 'messages' => array_values($tradeMsgs)]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if ($action === 'send') {
        $tradeId = $input['trade_id'] ?? '';
        $text = trim($input['text'] ?? '');

        if (!$tradeId || !$text) {
            echo json_encode(['success' => false, 'error' => 'Trade ID and text are required']);
            exit;
        }

        $messages = readJson('messages.json');
        $newMsg = [
            'id' => uniqid(),
            'trade_id' => $tradeId,
            'sender_id' => $user['id'],
            'sender_username' => $user['username'],
            'text' => $text,
            'created_at' => time()
        ];

        $messages[] = $newMsg;
        writeJson('messages.json', $messages);

        echo json_encode(['success' => true, 'message' => $newMsg]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid action.']);
