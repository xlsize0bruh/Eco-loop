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
        $projects = readJson('projects.json');

        // Return projects for user's pincode
        $myProjects = array_filter($projects, function($p) use ($user) {
            return $p['pincode'] === $user['pincode'];
        });

        echo json_encode(['success' => true, 'projects' => array_values($myProjects)]);
        exit;
    }

    if ($action === 'detail') {
        $projectId = $_GET['project_id'] ?? '';
        $projects = readJson('projects.json');

        foreach ($projects as $p) {
            if ($p['id'] === $projectId) {
                echo json_encode(['success' => true, 'project' => $p]);
                exit;
            }
        }

        echo json_encode(['success' => false, 'error' => 'Project not found']);
        exit;
    }

    if ($action === 'my-projects') {
        $projects = readJson('projects.json');

        // Return projects created by user
        $created = array_filter($projects, function($p) use ($user) {
            return $p['creator_id'] === $user['id'];
        });

        echo json_encode(['success' => true, 'projects' => array_values($created)]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if ($action === 'create') {
        $title = trim($input['title'] ?? '');
        $description = trim($input['description'] ?? '');
        $requiredMaterials = $input['required_materials'] ?? [];

        if (!$title) {
            echo json_encode(['success' => false, 'error' => 'Project title is required']);
            exit;
        }

        $projects = readJson('projects.json');

        $newProject = [
            'id' => uniqid(),
            'creator_id' => $user['id'],
            'creator_username' => $user['username'],
            'pincode' => $user['pincode'],
            'title' => $title,
            'description' => $description,
            'required_materials' => $requiredMaterials,
            'collected_materials' => [],
            'status' => 'active', // active, completed, cancelled
            'contributors' => [], // [{ user_id, username, item_id, item_title, contributed_at }, ...]
            'progress' => 0,
            'created_at' => time()
        ];

        $projects[] = $newProject;
        writeJson('projects.json', $projects);

        echo json_encode(['success' => true, 'project' => $newProject]);
        exit;
    }

    if ($action === 'contribute') {
        $projectId = $input['project_id'] ?? '';
        $itemId = $input['item_id'] ?? '';

        if (!$projectId || !$itemId) {
            echo json_encode(['success' => false, 'error' => 'Project ID and Item ID are required']);
            exit;
        }

        $projects = readJson('projects.json');
        $items = readJson('items.json');
        $users = readJson('users.json');

        $project = null;
        foreach ($projects as &$p) {
            if ($p['id'] === $projectId) {
                $project = &$p;
                break;
            }
        }

        if (!$project) {
            echo json_encode(['success' => false, 'error' => 'Project not found']);
            exit;
        }

        // Check if item exists and belongs to project location
        $item = null;
        foreach ($items as $i) {
            if ($i['id'] === $itemId && $i['pincode'] === $user['pincode']) {
                $item = $i;
                break;
            }
        }

        if (!$item) {
            echo json_encode(['success' => false, 'error' => 'Item not found or not in your area']);
            exit;
        }

        // Check if item already contributed
        foreach ($project['collected_materials'] as $cm) {
            if ($cm === $itemId) {
                echo json_encode(['success' => false, 'error' => 'This item already contributed to project']);
                exit;
            }
        }

        // Add to project
        $project['collected_materials'][] = $itemId;

        // Add contributor
        $contributor = [
            'user_id' => $item['owner_id'],
            'username' => $item['owner_username'],
            'item_id' => $itemId,
            'item_title' => $item['title'],
            'contributed_at' => time()
        ];

        $project['contributors'][] = $contributor;

        // Update progress
        $project['progress'] = count($project['collected_materials']) . '/' . count($project['required_materials']);

        // Reward contributor with contribution points
        foreach ($users as &$u) {
            if ($u['id'] === $item['owner_id']) {
                $u['contribution_points'] = ($u['contribution_points'] ?? 0) + 2;
                $u['positive_reviews'] = ($u['positive_reviews'] ?? 0) + 1; // Also give positive review
                break;
            }
        }

        writeJson('projects.json', $projects);
        writeJson('users.json', $users);

        echo json_encode(['success' => true, 'message' => 'Material contributed to project']);
        exit;
    }

    if ($action === 'complete') {
        $projectId = $input['project_id'] ?? '';
        $projects = readJson('projects.json');

        $found = false;
        foreach ($projects as &$p) {
            if ($p['id'] === $projectId && $p['creator_id'] === $user['id']) {
                $p['status'] = 'completed';
                $found = true;
                break;
            }
        }

        if ($found) {
            writeJson('projects.json', $projects);
            echo json_encode(['success' => true, 'message' => 'Project marked as completed']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Project not found or unauthorized']);
        }
        exit;
    }

    if ($action === 'cancel') {
        $projectId = $input['project_id'] ?? '';
        $projects = readJson('projects.json');

        $found = false;
        foreach ($projects as &$p) {
            if ($p['id'] === $projectId && $p['creator_id'] === $user['id']) {
                $p['status'] = 'cancelled';
                $found = true;
                break;
            }
        }

        if ($found) {
            writeJson('projects.json', $projects);
            echo json_encode(['success' => true, 'message' => 'Project cancelled']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Project not found or unauthorized']);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid action.']);
