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
    if ($action === 'leaderboard') {
        $users = readJson('users.json');

        $localUsers = array_values(array_filter($users, function($u) use ($user) {
            return $u['pincode'] === $user['pincode'];
        }));

        usort($localUsers, function($a, $b) {
            $repA = ($a['positive_reviews'] ?? 0) - ($a['negative_reviews'] ?? 0);
            $repB = ($b['positive_reviews'] ?? 0) - ($b['negative_reviews'] ?? 0);
            return $repB <=> $repA;
        });

        $topUsers = array_slice($localUsers, 0, 10);

        foreach ($topUsers as &$u) {
            unset($u['password']);
            $u['positive_reviews'] = $u['positive_reviews'] ?? 0;
            $u['negative_reviews'] = $u['negative_reviews'] ?? 0;
        }
        unset($u);

        echo json_encode(['success' => true, 'topUsers' => $topUsers]);
        exit;
    }

    if ($action === 'profile') {
        $users = readJson('users.json');
        $trades = readJson('trades.json');

        $currentUser = null;
        foreach($users as $u) {
            if($u['id'] === $user['id']) {
                $currentUser = $u;
                break;
            }
        }

        if (!$currentUser) {
            echo json_encode(['success' => false, 'error' => 'User not found']);
            exit;
        }

        $myCompletedTrades = 0;
        foreach ($trades as $t) {
            if ($t['status'] === 'completed' && ($t['proposer_id'] === $user['id'] || $t['receiver_id'] === $user['id'])) {
                $myCompletedTrades++;
            }
        }

        unset($currentUser['password']);
        $currentUser['completed_trades'] = $myCompletedTrades;
        $currentUser['positive_reviews'] = $currentUser['positive_reviews'] ?? 0;
        $currentUser['negative_reviews'] = $currentUser['negative_reviews'] ?? 0;

        echo json_encode(['success' => true, 'profile' => $currentUser]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid action.']);
