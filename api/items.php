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
        $items = readJson('items.json');
        $users = readJson('users.json');
        $trades = readJson('trades.json');
        $borrows = readJson('borrows.json');

        $tradeLocked = [];
        foreach($trades as $t) {
            if ($t['status'] !== 'cancelled') {
                $tradeLocked[] = $t['offered_item_id'];
                $tradeLocked[] = $t['wanted_item_id'];
            }
        }
        $borrowMap = [];
        foreach($borrows as $b) {
            if (in_array($b['status'], ['pending', 'accepted'])) {
                if (!isset($borrowMap[$b['item_id']]) || $b['status'] === 'accepted') {
                    $borrowMap[$b['item_id']] = [
                        'status' => $b['status'],
                        'due' => $b['due_date'] ?? null
                    ];
                }
            }
        }

        $localItems = [];
        foreach ($items as $item) {
            if ($item['pincode'] === $user['pincode']) {
                $owner = null;
                foreach($users as $u) {
                    if ($u['id'] === $item['owner_id']) {
                        $owner = $u;
                        break;
                    }
                }

                $owner_pos = $owner ? ($owner['positive_reviews'] ?? 0) : 0;
                $owner_neg = $owner ? ($owner['negative_reviews'] ?? 0) : 0;
                $inTrade = in_array($item['id'], $tradeLocked);
                $binfo = $borrowMap[$item['id']] ?? null;

                $item['owner_pos'] = $owner_pos;
                $item['owner_neg'] = $owner_neg;
                $item['in_trade'] = $inTrade;
                $item['borrow_status'] = $binfo ? $binfo['status'] : null;
                $item['borrow_due'] = $binfo ? $binfo['due'] : null;
                $item['is_locked'] = $inTrade || ($binfo !== null);

                $localItems[] = $item;
            }
        }

        echo json_encode(['success' => true, 'items' => $localItems]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $tags = $_POST['tags'] ?? '';
        $lookingForTags = $_POST['looking_for_tags'] ?? '';

        if (!$title) {
            echo json_encode(['success' => false, 'error' => 'Title is required']);
            exit;
        }

        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = uniqid() . '-' . basename($_FILES['image']['name']);
            $dest = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $imagePath = 'uploads/' . $filename;
            }
        }

        $items = readJson('items.json');
        $newItem = [
            'id' => uniqid(),
            'owner_id' => $user['id'],
            'owner_username' => $user['username'],
            'pincode' => $user['pincode'],
            'title' => trim($title),
            'description' => trim($description),
            'tags' => array_filter(array_map('trim', explode(',', $tags))),
            'looking_for_tags' => array_filter(array_map('trim', explode(',', $lookingForTags))),
            'image' => $imagePath,
            'created_at' => time()
        ];

        $items[] = $newItem;
        writeJson('items.json', $items);

        echo json_encode(['success' => true, 'item' => $newItem]);
        exit;
    }

    if ($action === 'edit') {
        $itemId = $_POST['item_id'] ?? '';
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $tags = $_POST['tags'] ?? '';
        $lookingForTags = $_POST['looking_for_tags'] ?? '';

        if (!$itemId || (!$title && $action !== 'delete')) {
            echo json_encode(['success' => false, 'error' => 'Title and Item ID are required']);
            exit;
        }

        $items = readJson('items.json');
        $found = false;

        foreach ($items as &$item) {
            if ($item['id'] === $itemId && $item['owner_id'] === $user['id']) {
                $item['title'] = trim($title);
                $item['description'] = trim($description);
                $item['tags'] = array_filter(array_map('trim', explode(',', $tags)));
                $item['looking_for_tags'] = array_filter(array_map('trim', explode(',', $lookingForTags)));

                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $filename = uniqid() . '-' . basename($_FILES['image']['name']);
                    $dest = $uploadDir . $filename;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                        $item['image'] = 'uploads/' . $filename;
                    }
                }
                $found = true;
                break;
            }
        }

        if ($found) {
            writeJson('items.json', $items);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Item not found or unauthorized']);
        }
        exit;
    }

    if ($action === 'delete') {
        $input = json_decode(file_get_contents('php://input'), true);
        $itemId = $input['item_id'] ?? '';

        if (!$itemId) {
            echo json_encode(['success' => false, 'error' => 'Item ID is required']);
            exit;
        }

        $items = readJson('items.json');
        $filtered = [];
        $deleted = false;

        foreach ($items as $item) {
            if ($item['id'] === $itemId && $item['owner_id'] === $user['id']) {
                $deleted = true;
                continue;
            }
            $filtered[] = $item;
        }

        if ($deleted) {
            writeJson('items.json', $filtered);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Unauthorized or not found']);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid action.']);
