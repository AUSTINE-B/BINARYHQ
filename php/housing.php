<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/connect.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['user_id'], $_SESSION['user_role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please log in to continue.']);
    exit;
}

$session = $_SESSION;
$userId = (int)$session['user_id'];
$role = $session['user_role'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

function housingAudit(PDO $pdo, array $session, string $action, string $targetType, ?int $targetId, string $details = ''): void
{
    $statement = $pdo->prepare('INSERT INTO audit_log (actor_id,action,target_type,target_id,details) VALUES (:actor,:action,:target,:id,:details)');
    $statement->execute([
        'actor' => $session['user_id'],
        'action' => $action,
        'target' => $targetType,
        'id' => $targetId,
        'details' => $details,
    ]);
}

try {
    $pdo = campusnestDatabase();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'choose_house') {
        if ($role !== 'student') {
            http_response_code(403);
            echo json_encode(['error' => 'Only student accounts can choose a house.']);
            exit;
        }
        $listingId = filter_input(INPUT_POST, 'listing_id', FILTER_VALIDATE_INT);
        if (!$listingId) {
            http_response_code(400);
            echo json_encode(['error' => 'Choose a valid house.']);
            exit;
        }

        $pdo->beginTransaction();
        $pdo->prepare('SELECT id FROM users WHERE id=:id FOR UPDATE')->execute(['id' => $userId]);
        $active = $pdo->prepare("SELECT id FROM tenancies WHERE tenant_id=:tenant AND status='active' LIMIT 1 FOR UPDATE");
        $active->execute(['tenant' => $userId]);
        if ($active->fetch()) {
            $pdo->rollBack();
            http_response_code(409);
            echo json_encode(['error' => 'You already have a current house. Contact the admin to change it.']);
            exit;
        }

        $listingQuery = $pdo->prepare("SELECT id,landlord_id,price,title FROM listings WHERE id=:id AND status='approved' AND available=1 FOR UPDATE");
        $listingQuery->execute(['id' => $listingId]);
        $listing = $listingQuery->fetch();
        if (!$listing) {
            $pdo->rollBack();
            http_response_code(409);
            echo json_encode(['error' => 'This house is no longer available.']);
            exit;
        }

        $statement = $pdo->prepare("INSERT INTO tenancies (listing_id,tenant_id,rent_amount,status) VALUES (:listing,:tenant,:rent,'active')");
        $statement->execute(['listing' => $listingId, 'tenant' => $userId, 'rent' => $listing['price']]);
        $tenancyId = (int)$pdo->lastInsertId();
        $pdo->prepare('UPDATE listings SET available=0 WHERE id=:id')->execute(['id' => $listingId]);
        $pdo->prepare('INSERT INTO notifications (user_id,message) VALUES (:user,:message)')->execute([
            'user' => $listing['landlord_id'],
            'message' => ($session['full_name'] ?? 'A tenant') . ' selected your house: ' . $listing['title'],
        ]);
        housingAudit($pdo, $session, 'choose_house', 'tenancy', $tenancyId, 'Listing ' . $listingId);
        $pdo->commit();
        echo json_encode(['success' => true, 'tenancy_id' => $tenancyId]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'submit_payment') {
        if ($role !== 'student') {
            http_response_code(403);
            echo json_encode(['error' => 'Only tenants can submit a payment.']);
            exit;
        }
        $type = $_POST['payment_type'] ?? '';
        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_INT);
        $period = trim($_POST['period'] ?? date('Y-m'));
        if (!in_array($type, ['rent', 'electricity', 'water'], true) || !$amount || $amount < 1 || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
            http_response_code(400);
            echo json_encode(['error' => 'Enter a valid payment type, amount, and month.']);
            exit;
        }
        $tenancyQuery = $pdo->prepare("SELECT t.id,l.landlord_id FROM tenancies t JOIN listings l ON l.id=t.listing_id WHERE t.tenant_id=:tenant AND t.status='active'");
        $tenancyQuery->execute(['tenant' => $userId]);
        $tenancy = $tenancyQuery->fetch();
        if (!$tenancy) {
            http_response_code(409);
            echo json_encode(['error' => 'Choose a house before making a payment.']);
            exit;
        }
        $statement = $pdo->prepare("INSERT INTO payments (tenancy_id,payer_id,amount,payment_type,period,status) VALUES (:tenancy,:payer,:amount,:type,:period,'pending')");
        $statement->execute([
            'tenancy' => $tenancy['id'],
            'payer' => $userId,
            'amount' => $amount,
            'type' => $type,
            'period' => $period,
        ]);
        $paymentId = (int)$pdo->lastInsertId();
        $pdo->prepare('INSERT INTO notifications (user_id,message) VALUES (:user,:message)')->execute([
            'user' => $tenancy['landlord_id'],
            'message' => 'A tenant submitted a ' . $type . ' payment for confirmation.',
        ]);
        housingAudit($pdo, $session, 'submit_payment', 'payment', $paymentId, $type . ' ' . $amount . ' FCFA');
        echo json_encode(['success' => true, 'payment_id' => $paymentId, 'status' => 'pending']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_rent') {
        if (!in_array($role, ['landlord', 'admin'], true)) {
            http_response_code(403);
            echo json_encode(['error' => 'Only a landlord or admin can update rent.']);
            exit;
        }
        $tenancyId = filter_input(INPUT_POST, 'tenancy_id', FILTER_VALIDATE_INT);
        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_INT);
        if (!$tenancyId || !$amount || $amount < 1) {
            http_response_code(400);
            echo json_encode(['error' => 'Enter a valid rent amount.']);
            exit;
        }
        $statement = $pdo->prepare("SELECT t.id,t.tenant_id,t.listing_id FROM tenancies t JOIN listings l ON l.id=t.listing_id WHERE t.id=:id AND t.status='active' AND (l.landlord_id=:landlord OR :admin=1)");
        $statement->execute(['id' => $tenancyId, 'landlord' => $userId, 'admin' => $role === 'admin' ? 1 : 0]);
        $tenancy = $statement->fetch();
        if (!$tenancy) {
            http_response_code(404);
            echo json_encode(['error' => 'Current tenancy not found.']);
            exit;
        }
        $pdo->prepare('UPDATE tenancies SET rent_amount=:amount WHERE id=:id')->execute(['amount' => $amount, 'id' => $tenancyId]);
        $pdo->prepare('UPDATE listings SET price=:amount WHERE id=:id')->execute(['amount' => $amount, 'id' => $tenancy['listing_id']]);
        $pdo->prepare('INSERT INTO notifications (user_id,message) VALUES (:user,:message)')->execute([
            'user' => $tenancy['tenant_id'],
            'message' => 'Your monthly rent was updated to ' . number_format($amount) . ' FCFA.',
        ]);
        housingAudit($pdo, $session, 'update_rent', 'tenancy', $tenancyId, (string)$amount);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'record_payment') {
        if (!in_array($role, ['landlord', 'admin'], true)) {
            http_response_code(403);
            echo json_encode(['error' => 'Only a landlord or admin can record a collected payment.']);
            exit;
        }
        $tenancyId = filter_input(INPUT_POST, 'tenancy_id', FILTER_VALIDATE_INT);
        $type = $_POST['payment_type'] ?? '';
        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_INT);
        $period = trim($_POST['period'] ?? date('Y-m'));
        if (!$tenancyId || !in_array($type, ['rent', 'electricity', 'water'], true) || !$amount || $amount < 1 || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
            http_response_code(400);
            echo json_encode(['error' => 'Enter a valid tenant, payment type, amount, and month.']);
            exit;
        }
        $tenancyQuery = $pdo->prepare("SELECT t.id,t.tenant_id,l.landlord_id,l.title FROM tenancies t JOIN listings l ON l.id=t.listing_id WHERE t.id=:id AND t.status='active' AND (l.landlord_id=:landlord OR :admin=1)");
        $tenancyQuery->execute(['id' => $tenancyId, 'landlord' => $userId, 'admin' => $role === 'admin' ? 1 : 0]);
        $tenancy = $tenancyQuery->fetch();
        if (!$tenancy) {
            http_response_code(404);
            echo json_encode(['error' => 'Current tenant not found.']);
            exit;
        }
        $statement = $pdo->prepare("INSERT INTO payments (tenancy_id,payer_id,amount,payment_type,period,status,verified_by,paid_at) VALUES (:tenancy,:payer,:amount,:type,:period,'paid',:verifier,CURRENT_TIMESTAMP)");
        $statement->execute([
            'tenancy' => $tenancyId,
            'payer' => $tenancy['tenant_id'],
            'amount' => $amount,
            'type' => $type,
            'period' => $period,
            'verifier' => $userId,
        ]);
        $paymentId = (int)$pdo->lastInsertId();
        $pdo->prepare('INSERT INTO notifications (user_id,message) VALUES (:user,:message)')->execute([
            'user' => $tenancy['tenant_id'],
            'message' => 'A ' . $type . ' payment for ' . number_format($amount) . ' FCFA was recorded for ' . $tenancy['title'] . '.',
        ]);
        housingAudit($pdo, $session, 'record_payment', 'payment', $paymentId, $type . ' ' . $amount . ' FCFA');
        echo json_encode(['success' => true, 'payment_id' => $paymentId]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'collect_payment') {
        if (!in_array($role, ['landlord', 'admin'], true)) {
            http_response_code(403);
            echo json_encode(['error' => 'Only a landlord or admin can confirm a payment.']);
            exit;
        }
        $paymentId = filter_input(INPUT_POST, 'payment_id', FILTER_VALIDATE_INT);
        $decision = $_POST['decision'] ?? 'paid';
        if (!$paymentId || !in_array($decision, ['paid', 'rejected'], true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Choose a valid payment decision.']);
            exit;
        }
        $paymentQuery = $pdo->prepare("SELECT p.id,t.tenant_id FROM payments p JOIN tenancies t ON t.id=p.tenancy_id JOIN listings l ON l.id=t.listing_id WHERE p.id=:id AND p.status='pending' AND (l.landlord_id=:landlord OR :admin=1)");
        $paymentQuery->execute(['id' => $paymentId, 'landlord' => $userId, 'admin' => $role === 'admin' ? 1 : 0]);
        $payment = $paymentQuery->fetch();
        if (!$payment) {
            http_response_code(404);
            echo json_encode(['error' => 'Pending payment not found.']);
            exit;
        }
        $update = $pdo->prepare('UPDATE payments SET status=:status,verified_by=:verifier,paid_at=:paid_at WHERE id=:id AND status=\'pending\'');
        $update->execute([
            'status' => $decision,
            'verifier' => $userId,
            'paid_at' => $decision === 'paid' ? date('Y-m-d H:i:s') : null,
            'id' => $paymentId,
        ]);
        if (!$update->rowCount()) {
            http_response_code(409);
            echo json_encode(['error' => 'This payment has already been processed.']);
            exit;
        }
        $pdo->prepare('INSERT INTO notifications (user_id,message) VALUES (:user,:message)')->execute([
            'user' => $payment['tenant_id'],
            'message' => 'Your payment was ' . $decision . '.',
        ]);
        housingAudit($pdo, $session, 'collect_payment', 'payment', $paymentId, $decision);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'send_message') {
        if (!in_array($role, ['student', 'admin'], true)) {
            http_response_code(403);
            echo json_encode(['error' => 'Messaging is available to tenants and admins.']);
            exit;
        }
        $tenancyId = filter_input(INPUT_POST, 'tenancy_id', FILTER_VALIDATE_INT);
        $body = trim($_POST['body'] ?? '');
        if (!$tenancyId || $body === '' || strlen($body) > 4000) {
            http_response_code(400);
            echo json_encode(['error' => 'Enter a message of up to 4,000 characters.']);
            exit;
        }
        $tenancyQuery = $pdo->prepare('SELECT t.id,t.tenant_id,l.title FROM tenancies t JOIN listings l ON l.id=t.listing_id WHERE t.id=:id');
        $tenancyQuery->execute(['id' => $tenancyId]);
        $tenancy = $tenancyQuery->fetch();
        if (!$tenancy || $role === 'student' && (int)$tenancy['tenant_id'] !== $userId) {
            http_response_code(404);
            echo json_encode(['error' => 'House conversation not found.']);
            exit;
        }
        $pdo->prepare('INSERT IGNORE INTO conversations (tenancy_id) VALUES (:id)')->execute(['id' => $tenancyId]);
        $conversationQuery = $pdo->prepare('SELECT id FROM conversations WHERE tenancy_id=:id');
        $conversationQuery->execute(['id' => $tenancyId]);
        $conversationId = (int)$conversationQuery->fetchColumn();
        $pdo->prepare('INSERT INTO messages (conversation_id,sender_id,body) VALUES (:conversation,:sender,:body)')->execute([
            'conversation' => $conversationId,
            'sender' => $userId,
            'body' => $body,
        ]);
        if ($role === 'student') {
            $admins = $pdo->query("SELECT id FROM users WHERE role='admin' AND active=1")->fetchAll(PDO::FETCH_COLUMN);
            $notify = $pdo->prepare('INSERT INTO notifications (user_id,message) VALUES (:user,:message)');
            foreach ($admins as $adminId) {
                $notify->execute(['user' => $adminId, 'message' => 'A tenant sent a message about ' . $tenancy['title'] . '.']);
            }
        } else {
            $pdo->prepare('INSERT INTO notifications (user_id,message) VALUES (:user,:message)')->execute([
                'user' => $tenancy['tenant_id'],
                'message' => 'The admin replied about ' . $tenancy['title'] . '.',
            ]);
        }
        housingAudit($pdo, $session, 'send_message', 'conversation', $conversationId);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'mark_messages_read') {
        $tenancyId = filter_input(INPUT_POST, 'tenancy_id', FILTER_VALIDATE_INT);
        $statement = $pdo->prepare('SELECT c.id,t.tenant_id FROM conversations c JOIN tenancies t ON t.id=c.tenancy_id WHERE c.tenancy_id=:id');
        $statement->execute(['id' => $tenancyId]);
        $conversation = $statement->fetch();
        if (!$conversation || $role !== 'admin' && (int)$conversation['tenant_id'] !== $userId) {
            http_response_code(404);
            echo json_encode(['error' => 'Conversation not found.']);
            exit;
        }
        $pdo->prepare('UPDATE messages SET read_at=CURRENT_TIMESTAMP WHERE conversation_id=:conversation AND sender_id<>:sender AND read_at IS NULL')->execute([
            'conversation' => $conversation['id'],
            'sender' => $userId,
        ]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'submit_review') {
        if ($role !== 'student') {
            http_response_code(403);
            echo json_encode(['error' => 'Only tenants can review their house.']);
            exit;
        }
        $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
        $comment = trim($_POST['comment'] ?? '');
        $tenancyQuery = $pdo->prepare("SELECT t.id,l.landlord_id,l.title FROM tenancies t JOIN listings l ON l.id=t.listing_id WHERE t.tenant_id=:tenant AND t.status='active'");
        $tenancyQuery->execute(['tenant' => $userId]);
        $tenancy = $tenancyQuery->fetch();
        if (!$tenancy || !$rating || $rating < 1 || $rating > 5 || strlen($comment) > 2000) {
            http_response_code(400);
            echo json_encode(['error' => 'A current house and a rating from 1 to 5 are required.']);
            exit;
        }
        $pdo->prepare('INSERT INTO reviews (tenancy_id,rating,comment) VALUES (:tenancy,:rating,:comment) ON DUPLICATE KEY UPDATE rating=VALUES(rating),comment=VALUES(comment)')->execute([
            'tenancy' => $tenancy['id'],
            'rating' => $rating,
            'comment' => $comment,
        ]);
        $pdo->prepare('INSERT INTO notifications (user_id,message) VALUES (:user,:message)')->execute([
            'user' => $tenancy['landlord_id'],
            'message' => 'A tenant rated ' . $tenancy['title'] . '.',
        ]);
        housingAudit($pdo, $session, 'submit_review', 'tenancy', (int)$tenancy['id'], (string)$rating);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'mark_notifications_read') {
        $pdo->prepare('UPDATE notifications SET is_read=1 WHERE user_id=:user')->execute(['user' => $userId]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'end_tenancy') {
        if (!in_array($role, ['landlord', 'admin'], true)) {
            http_response_code(403);
            echo json_encode(['error' => 'Only a landlord or admin can end a tenancy.']);
            exit;
        }
        $tenancyId = filter_input(INPUT_POST, 'tenancy_id', FILTER_VALIDATE_INT);
        if (!$tenancyId) {
            http_response_code(400);
            echo json_encode(['error' => 'Enter a valid tenancy.']);
            exit;
        }

        $pdo->beginTransaction();
        $statement = $pdo->prepare("SELECT t.id,t.tenant_id,t.listing_id,l.title,l.landlord_id FROM tenancies t JOIN listings l ON l.id=t.listing_id WHERE t.id=:id AND t.status='active' FOR UPDATE");
        $statement->execute(['id' => $tenancyId]);
        $tenancy = $statement->fetch();
        if (!$tenancy || $role === 'landlord' && (int)$tenancy['landlord_id'] !== $userId) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['error' => 'Current tenancy not found.']);
            exit;
        }

        $update = $pdo->prepare("UPDATE tenancies SET status='ended',ended_at=CURRENT_TIMESTAMP WHERE id=:id AND status='active'");
        $update->execute(['id' => $tenancyId]);
        if (!$update->rowCount()) {
            $pdo->rollBack();
            http_response_code(409);
            echo json_encode(['error' => 'The tenancy has already ended.']);
            exit;
        }
        $pdo->prepare('UPDATE listings SET available=1 WHERE id=:id')->execute(['id' => $tenancy['listing_id']]);
        $pdo->prepare('INSERT INTO notifications (user_id,message) VALUES (:user,:message)')->execute([
            'user' => $tenancy['tenant_id'],
            'message' => 'Your tenancy for ' . $tenancy['title'] . ' was ended.',
        ]);
        housingAudit($pdo, $session, 'end_tenancy', 'tenancy', $tenancyId);
        $pdo->commit();
        echo json_encode(['success' => true]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'conversation') {
        $tenancyId = filter_input(INPUT_GET, 'tenancy_id', FILTER_VALIDATE_INT);
        $statement = $pdo->prepare('SELECT c.id,t.tenant_id FROM conversations c JOIN tenancies t ON t.id=c.tenancy_id WHERE c.tenancy_id=:id');
        $statement->execute(['id' => $tenancyId]);
        $conversation = $statement->fetch();
        if (!$conversation || $role !== 'admin' && (int)$conversation['tenant_id'] !== $userId) {
            http_response_code(404);
            echo json_encode(['error' => 'Conversation not found.']);
            exit;
        }
        $statement = $pdo->prepare('SELECT m.id,m.sender_id,m.body,m.read_at,m.created_at,u.full_name AS sender_name,u.role AS sender_role FROM messages m JOIN users u ON u.id=m.sender_id WHERE m.conversation_id=:conversation ORDER BY m.created_at ASC LIMIT 100');
        $statement->execute(['conversation' => $conversation['id']]);
        echo json_encode(['results' => $statement->fetchAll()]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'GET' || $action !== 'overview') {
        http_response_code(400);
        echo json_encode(['error' => 'Unknown housing action.']);
        exit;
    }

    $response = ['role' => $role, 'tenancy' => null, 'properties' => [], 'tenants' => [], 'payments' => [], 'repairs' => [], 'cleaning_requests' => [], 'reviews' => [], 'messages' => [], 'notifications' => [], 'services' => []];
    if ($role === 'student') {
        $statement = $pdo->prepare("SELECT t.id AS tenancy_id,t.rent_amount,t.started_at,l.id AS listing_id,l.title,l.city,l.neighborhood,l.listing_type,l.description,l.photo_url,u.full_name AS landlord_name,u.email AS landlord_email,u.phone AS landlord_phone FROM tenancies t JOIN listings l ON l.id=t.listing_id JOIN users u ON u.id=l.landlord_id WHERE t.tenant_id=:tenant AND t.status='active' LIMIT 1");
        $statement->execute(['tenant' => $userId]);
        $response['tenancy'] = $statement->fetch() ?: null;
        $tenancy = $response['tenancy'];
        if (is_array($tenancy)) {
            $interiorPhotos = campusnestInteriorPhotosByListing($pdo, [(int)$tenancy['listing_id']]);
            $tenancy['interior_photos'] = $interiorPhotos[(int)$tenancy['listing_id']] ?? [];
            campusnestApplyHousePhotoFallback($tenancy);
            $response['tenancy'] = $tenancy;
            $tenancyId = (int)$tenancy['tenancy_id'];
            $statement = $pdo->prepare('SELECT id,amount,payment_type,period,status,created_at FROM payments WHERE tenancy_id=:tenancy ORDER BY created_at DESC LIMIT 20');
            $statement->execute(['tenancy' => $tenancyId]);
            $response['payments'] = $statement->fetchAll();
            $statement = $pdo->prepare('SELECT rating,comment,created_at FROM reviews WHERE tenancy_id=:tenancy');
            $statement->execute(['tenancy' => $tenancyId]);
            $response['review'] = $statement->fetch() ?: null;
            $statement = $pdo->prepare('SELECT c.id AS conversation_id,m.id AS message_id,m.sender_id,m.body,m.read_at,m.created_at,u.full_name AS sender_name,u.role AS sender_role FROM conversations c JOIN messages m ON m.conversation_id=c.id JOIN users u ON u.id=m.sender_id WHERE c.tenancy_id=:tenancy ORDER BY m.created_at ASC LIMIT 100');
            $statement->execute(['tenancy' => $tenancyId]);
            $response['messages'] = $statement->fetchAll();
        }
        $statement = $pdo->prepare('SELECT r.id,r.status,r.category,r.description,r.created_at,l.title AS house_title FROM repairs r JOIN listings l ON l.id=r.listing_id WHERE r.student_id=:student ORDER BY r.created_at DESC LIMIT 20');
        $statement->execute(['student' => $userId]);
        $response['repairs'] = $statement->fetchAll();
    } elseif ($role === 'landlord') {
        $statement = $pdo->prepare('SELECT id,title,city,neighborhood,listing_type,price,status,available,photo_url FROM listings WHERE landlord_id=:landlord ORDER BY created_at DESC');
        $statement->execute(['landlord' => $userId]);
        $response['properties'] = $statement->fetchAll();
        $interiorPhotos = campusnestInteriorPhotosByListing($pdo, array_column($response['properties'], 'id'));
        foreach ($response['properties'] as &$property) {
            $property['interior_photos'] = $interiorPhotos[(int)$property['id']] ?? [];
            campusnestApplyHousePhotoFallback($property);
        }
        unset($property);
        $statement = $pdo->prepare("SELECT t.id AS tenancy_id,t.tenant_id,t.rent_amount,t.started_at,l.id AS listing_id,l.title AS house_title,l.city,u.full_name AS tenant_name,u.email,u.phone FROM tenancies t JOIN listings l ON l.id=t.listing_id JOIN users u ON u.id=t.tenant_id WHERE l.landlord_id=:landlord AND t.status='active' ORDER BY t.started_at DESC");
        $statement->execute(['landlord' => $userId]);
        $response['tenants'] = $statement->fetchAll();
        $statement = $pdo->prepare("SELECT p.id,p.amount,p.payment_type,p.period,p.status,p.created_at,u.full_name AS tenant_name,l.title AS house_title FROM payments p JOIN tenancies t ON t.id=p.tenancy_id JOIN listings l ON l.id=t.listing_id JOIN users u ON u.id=p.payer_id WHERE l.landlord_id=:landlord ORDER BY p.created_at DESC LIMIT 50");
        $statement->execute(['landlord' => $userId]);
        $response['payments'] = $statement->fetchAll();
        $statement = $pdo->prepare('SELECT r.id,r.status,r.category,r.description,r.created_at,l.title AS house_title,u.full_name AS tenant_name FROM repairs r JOIN listings l ON l.id=r.listing_id JOIN users u ON u.id=r.student_id WHERE l.landlord_id=:landlord ORDER BY r.created_at DESC LIMIT 50');
        $statement->execute(['landlord' => $userId]);
        $response['repairs'] = $statement->fetchAll();
    } elseif ($role === 'repairer') {
        $statement = $pdo->prepare('SELECT r.id,r.status,r.category,r.description,r.created_at,l.title AS house_title FROM repairs r JOIN listings l ON l.id=r.listing_id WHERE r.repairer_id=:id ORDER BY r.created_at DESC LIMIT 50');
        $statement->execute(['id' => $userId]);
        $response['repairs'] = $statement->fetchAll();
    } elseif ($role === 'cleaner') {
        $statement = $pdo->prepare('SELECT id,location,preferred_date,service_type,notes,status,created_at FROM cleaning_requests WHERE cleaner_id=:id ORDER BY preferred_date DESC LIMIT 50');
        $statement->execute(['id' => $userId]);
        $response['cleaning_requests'] = $statement->fetchAll();
    }
    $statement = $pdo->prepare('SELECT id,message,is_read,created_at FROM notifications WHERE user_id=:user ORDER BY created_at DESC LIMIT 20');
    $statement->execute(['user' => $userId]);
    $response['notifications'] = $statement->fetchAll();
    $response['services'] = $pdo->query('SELECT id,name,description,phone FROM service_catalog WHERE active=1 ORDER BY name')->fetchAll();
    if ($role === 'student') {
        $providers = $pdo->query("SELECT id,full_name AS name,role AS description,phone FROM users WHERE role IN ('repairer','cleaner') AND active=1 AND approved=1 AND phone IS NOT NULL AND phone<>'' ORDER BY role,full_name")->fetchAll();
        foreach ($providers as $provider) {
            $provider['name'] .= ' · ' . ucfirst($provider['description']);
            $response['services'][] = $provider;
        }
    }

    echo json_encode($response);
} catch (Throwable $error) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Housing endpoint failed: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Unable to process housing request.']);
}
