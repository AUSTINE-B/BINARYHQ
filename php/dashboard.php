<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/connect.php';

try {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'], $_SESSION['user_role'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Please log in to view your dashboard.']);
        exit;
    }
    $session = $_SESSION;
    $pdo = campusnestDatabase();
    $role = $session['user_role'] ?? '';
    $userId = (int)$session['user_id'];
    $counts = [];
    $items = [];
    $activities = [];

    if ($role === 'student') {
        $statement = $pdo->prepare('SELECT COUNT(*) FROM applications WHERE student_id=:id');
        $statement->execute(['id' => $userId]);
        $counts['applications'] = (int)$statement->fetchColumn();
        $statement = $pdo->prepare("SELECT COUNT(*) FROM repairs WHERE student_id=:id AND status NOT IN ('Completed','Rejected','Cancelled')");
        $statement->execute(['id' => $userId]);
        $counts['requests'] = (int)$statement->fetchColumn();
        $statement = $pdo->prepare("SELECT COUNT(*) FROM tenancies WHERE tenant_id=:id AND status='active'");
        $statement->execute(['id' => $userId]);
        $counts['current_house'] = (int)$statement->fetchColumn();
        $statement = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0");
        $statement->execute(['id' => $userId]);
        $counts['notifications'] = (int)$statement->fetchColumn();
        $statement = $pdo->prepare("SELECT COUNT(*) FROM payments p JOIN tenancies t ON t.id=p.tenancy_id WHERE t.tenant_id=:id AND t.status='active' AND p.status='pending'");
        $statement->execute(['id' => $userId]);
        $counts['pending_payments'] = (int)$statement->fetchColumn();
        $statement = $pdo->prepare('SELECT r.id,r.status,l.title FROM repairs r JOIN listings l ON l.id=r.listing_id WHERE r.student_id=:id ORDER BY r.created_at DESC LIMIT 6');
        $statement->execute(['id' => $userId]);
        $items = $statement->fetchAll();
        $activities = $pdo->query("SELECT id,title,location,job_type,status FROM jobs WHERE status='approved' ORDER BY created_at DESC LIMIT 6")->fetchAll();
    } elseif ($role === 'landlord') {
        $statement = $pdo->prepare('SELECT COUNT(*) FROM listings WHERE landlord_id=:id');
        $statement->execute(['id' => $userId]);
        $counts['properties'] = (int)$statement->fetchColumn();
        $statement = $pdo->prepare('SELECT COUNT(*) FROM inquiries i JOIN listings l ON l.id=i.listing_id WHERE l.landlord_id=:id');
        $statement->execute(['id' => $userId]);
        $counts['inquiries'] = (int)$statement->fetchColumn();
        $statement = $pdo->prepare("SELECT COUNT(*) FROM tenancies t JOIN listings l ON l.id=t.listing_id WHERE l.landlord_id=:id AND t.status='active'");
        $statement->execute(['id' => $userId]);
        $counts['tenants'] = (int)$statement->fetchColumn();
        $statement = $pdo->prepare("SELECT COUNT(*) FROM payments p JOIN tenancies t ON t.id=p.tenancy_id JOIN listings l ON l.id=t.listing_id WHERE l.landlord_id=:id AND p.status='pending'");
        $statement->execute(['id' => $userId]);
        $counts['pending_payments'] = (int)$statement->fetchColumn();
        $statement = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0");
        $statement->execute(['id' => $userId]);
        $counts['notifications'] = (int)$statement->fetchColumn();
        $statement = $pdo->prepare('SELECT r.id,r.status,l.title FROM repairs r JOIN listings l ON l.id=r.listing_id WHERE l.landlord_id=:id ORDER BY r.created_at DESC LIMIT 6');
        $statement->execute(['id' => $userId]);
        $items = $statement->fetchAll();
        $statement = $pdo->prepare('SELECT id,title,city,status,price FROM listings WHERE landlord_id=:id ORDER BY created_at DESC LIMIT 6');
        $statement->execute(['id' => $userId]);
        $activities = $statement->fetchAll();
    } elseif ($role === 'admin') {
        foreach (['users', 'listings', 'tenancies', 'payments', 'jobs'] as $table) {
            $counts[$table] = (int)$pdo->query('SELECT COUNT(*) FROM ' . $table)->fetchColumn();
        }
        $statement = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=:id AND is_read=0");
        $statement->execute(['id' => $userId]);
        $counts['notifications'] = (int)$statement->fetchColumn();
    } elseif ($role === 'repairer') {
        $statement = $pdo->prepare('SELECT COUNT(*) FROM repairs WHERE repairer_id=:id');
        $statement->execute(['id' => $userId]);
        $counts['assigned'] = (int)$statement->fetchColumn();
        $statement = $pdo->prepare("SELECT COUNT(*) FROM repairs WHERE repairer_id=:id AND status IN ('In progress','Approved','Repairer assigned')");
        $statement->execute(['id' => $userId]);
        $counts['active'] = (int)$statement->fetchColumn();
        $statement = $pdo->prepare("SELECT COUNT(*) FROM repairs WHERE repairer_id=:id AND status='Completed'");
        $statement->execute(['id' => $userId]);
        $counts['completed'] = (int)$statement->fetchColumn();
        $statement = $pdo->prepare('SELECT id,status,created_at FROM repairs WHERE repairer_id=:id ORDER BY created_at DESC LIMIT 6');
        $statement->execute(['id' => $userId]);
        $items = $statement->fetchAll();
        $activities = $items;
    } elseif ($role === 'cleaner') {
        $statement = $pdo->prepare('SELECT COUNT(*) FROM cleaning_requests WHERE cleaner_id=:id');
        $statement->execute(['id' => $userId]);
        $counts['assigned'] = (int)$statement->fetchColumn();
        $statement = $pdo->prepare('SELECT COUNT(*) FROM cleaning_requests WHERE cleaner_id=:id AND preferred_date=CURDATE()');
        $statement->execute(['id' => $userId]);
        $counts['today'] = (int)$statement->fetchColumn();
        $statement = $pdo->prepare("SELECT COUNT(*) FROM cleaning_requests WHERE cleaner_id=:id AND status='Completed'");
        $statement->execute(['id' => $userId]);
        $counts['completed'] = (int)$statement->fetchColumn();
        $statement = $pdo->prepare('SELECT id,location,status,preferred_date FROM cleaning_requests WHERE cleaner_id=:id ORDER BY created_at DESC LIMIT 6');
        $statement->execute(['id' => $userId]);
        $items = $statement->fetchAll();
        $activities = $items;
    } elseif ($role === 'employer') {
        $statement = $pdo->prepare('SELECT COUNT(*) FROM jobs WHERE employer_id=:id');
        $statement->execute(['id' => $userId]);
        $counts['jobs'] = (int)$statement->fetchColumn();
        $statement = $pdo->prepare('SELECT COUNT(*) FROM applications a JOIN jobs j ON j.id=a.job_id WHERE j.employer_id=:id');
        $statement->execute(['id' => $userId]);
        $counts['applications'] = (int)$statement->fetchColumn();
        $statement = $pdo->prepare("SELECT COUNT(*) FROM jobs WHERE employer_id=:id AND status='pending'");
        $statement->execute(['id' => $userId]);
        $counts['pending'] = (int)$statement->fetchColumn();
        $statement = $pdo->prepare('SELECT j.id,j.title,j.location,j.status,COUNT(a.id) AS applications FROM jobs j LEFT JOIN applications a ON a.job_id=j.id WHERE j.employer_id=:id GROUP BY j.id ORDER BY j.created_at DESC LIMIT 6');
        $statement->execute(['id' => $userId]);
        $items = $statement->fetchAll();
        $activities = $items;
    }

    echo json_encode([
        'role' => $role,
        'user_id' => $userId,
        'name' => $session['full_name'] ?? 'CampusNest user',
        'counts' => $counts,
        'items' => $items,
        'activities' => $activities,
    ]);
} catch (Throwable $error) {
    error_log('Dashboard endpoint failed: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Unable to load dashboard data.']);
}
