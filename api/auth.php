<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if ($action === 'register') {
        $username = trim($input['username'] ?? '');
        $password = trim($input['password'] ?? '');
        $pincode = trim($input['pincode'] ?? '');

        if (!$username || !$password || !$pincode) {
            echo json_encode(['success' => false, 'error' => 'All fields are required.']);
            exit;
        }

        $users = readJson('users.json');

        foreach ($users as $u) {
            if ($u['username'] === $username) {
                echo json_encode(['success' => false, 'error' => 'Username already taken.']);
                exit;
            }
        }

        $newUser = [
            'id' => uniqid(),
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'pincode' => $pincode,
            'positive_reviews' => 0,
            'negative_reviews' => 0
        ];

        $users[] = $newUser;
        writeJson('users.json', $users);

        $_SESSION['user'] = [
            'id' => $newUser['id'],
            'username' => $newUser['username'],
            'pincode' => $newUser['pincode']
        ];

        echo json_encode(['success' => true, 'user' => $_SESSION['user']]);
        exit;
    }

    if ($action === 'login') {
        $username = trim($input['username'] ?? '');
        $password = trim($input['password'] ?? '');

        $users = readJson('users.json');

        foreach ($users as $u) {
            if ($u['username'] === $username && password_verify($password, $u['password'])) {
                $_SESSION['user'] = [
                    'id' => $u['id'],
                    'username' => $u['username'],
                    'pincode' => $u['pincode']
                ];
                echo json_encode(['success' => true, 'user' => $_SESSION['user']]);
                exit;
            }
        }

        echo json_encode(['success' => false, 'error' => 'Invalid credentials.']);
        exit;
    }

    if ($action === 'logout') {
        session_destroy();
        echo json_encode(['success' => true]);
        exit;
    }
}

if ($action === 'session') {
    if (isset($_SESSION['user'])) {
        $users = readJson('users.json');
        $pos = 0;
        $neg = 0;
        foreach($users as $u) {
            if($u['id'] === $_SESSION['user']['id']) {
                $pos = $u['positive_reviews'] ?? 0;
                $neg = $u['negative_reviews'] ?? 0;
                break;
            }
        }
        $userData = $_SESSION['user'];
        $userData['positive_reviews'] = $pos;
        $userData['negative_reviews'] = $neg;

        echo json_encode(['loggedIn' => true, 'user' => $userData]);
    } else {
        echo json_encode(['loggedIn' => false]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action.']);
