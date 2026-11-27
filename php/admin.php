<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/connect.php';

try {
    $session = campusnestRequireRole(['admin']);
    $pdo = campusnestDatabase();
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($action === 'add_listing') {
            $landlordId = filter_input(INPUT_POST, 'landlord_id', FILTER_VALIDATE_INT);
            $title = trim($_POST['title'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $type = trim($_POST['listing_type'] ?? '');
            $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_INT);
            $landlordQuery = $pdo->prepare("SELECT id FROM users WHERE id=:id AND role='landlord' AND active=1");
            $landlordQuery->execute(['id' => $landlordId]);
            if (!$landlordId || !$landlordQuery->fetch() || $title === '' || $city === '' || $type === '' || !$price || $price < 1) {
                http_response_code(400);
                echo json_encode(['error' => 'Select an active landlord and enter a title, city, accommodation type, and valid rent.']);
                exit;
            }
            try {
                $interiorPhotos = campusnestParseInteriorPhotoUrls($_POST['interior_photo_urls'] ?? '');
            } catch (InvalidArgumentException $error) {
                http_response_code(400);
                echo json_encode(['error' => $error->getMessage()]);
                exit;
            }

            $statement = $pdo->prepare("INSERT INTO listings (landlord_id,title,institution,city,neighborhood,listing_type,price,description,furnished,available,photo_url,status) VALUES (:landlord,:title,:institution,:city,:neighborhood,:type,:price,:description,:furnished,1,:photo,'approved')");
            $pdo->beginTransaction();
            try {
                $statement->execute([
                    'landlord' => $landlordId,
                    'title' => $title,
                    'institution' => trim($_POST['institution'] ?? ''),
                    'city' => $city,
                    'neighborhood' => trim($_POST['neighborhood'] ?? ''),
                    'type' => $type,
                    'price' => $price,
                    'description' => trim($_POST['description'] ?? ''),
                    'furnished' => isset($_POST['furnished']) ? 1 : 0,
                    'photo' => trim($_POST['photo_url'] ?? '') ?: 'assets/images/house-elevation.jpg',
                ]);
                $listingId = (int)$pdo->lastInsertId();
                campusnestReplaceInteriorPhotos($pdo, $listingId, $interiorPhotos);
                $pdo->prepare('INSERT INTO notifications (user_id,message) VALUES (:user,:message)')->execute([
                    'user' => $landlordId,
                    'message' => 'The admin added a house to your listings: ' . $title,
                ]);
                adminAudit($pdo, $session, 'add_listing', 'listing', $listingId);
                $pdo->commit();
            } catch (Throwable $error) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $error;
            }
            echo json_encode(['success' => true, 'id' => $listingId]);
            exit;
        }

        if ($action === 'update_listing_price') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_INT);
            if (!$id || !$price || $price < 1) {
                http_response_code(400);
                echo json_encode(['error' => 'Enter a valid house and rent amount.']);
                exit;
            }
            $listingQuery = $pdo->prepare('SELECT id FROM listings WHERE id=:id');
            $listingQuery->execute(['id' => $id]);
            if (!$listingQuery->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'House not found.']);
                exit;
            }
            $pdo->prepare('UPDATE listings SET price=:price WHERE id=:id')->execute(['price' => $price, 'id' => $id]);
            $pdo->prepare('UPDATE tenancies SET rent_amount=:price WHERE listing_id=:id AND status=\'active\'')->execute(['price' => $price, 'id' => $id]);
            $tenants = $pdo->prepare("SELECT tenant_id FROM tenancies WHERE listing_id=:id AND status='active'");
            $tenants->execute(['id' => $id]);
            $notify = $pdo->prepare('INSERT INTO notifications (user_id,message) VALUES (:user,:message)');
            foreach ($tenants->fetchAll(PDO::FETCH_COLUMN) as $tenantId) {
                $notify->execute(['user' => $tenantId, 'message' => 'The admin updated your monthly rent to ' . number_format($price) . ' FCFA.']);
            }
            adminAudit($pdo, $session, 'update_listing_price', 'listing', $id, (string)$price);
            echo json_encode(['success' => true]);
            exit;
        }

        if ($action === 'update_interior_photos') {
            $id = filter_input(INPUT_POST, 'listing_id', FILTER_VALIDATE_INT);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Valid house ID required.']);
                exit;
            }
            $listingQuery = $pdo->prepare('SELECT id FROM listings WHERE id=:id');
            $listingQuery->execute(['id' => $id]);
            if (!$listingQuery->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'House not found.']);
                exit;
            }
            try {
                $interiorPhotos = campusnestParseInteriorPhotoUrls($_POST['interior_photo_urls'] ?? '');
            } catch (InvalidArgumentException $error) {
                http_response_code(400);
                echo json_encode(['error' => $error->getMessage()]);
                exit;
            }
            $pdo->beginTransaction();
            try {
                campusnestReplaceInteriorPhotos($pdo, (int)$id, $interiorPhotos);
                adminAudit($pdo, $session, 'update_interior_photos', 'listing', (int)$id, count($interiorPhotos) . ' interior photos');
                $pdo->commit();
            } catch (Throwable $error) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $error;
            }
            echo json_encode(['success' => true]);
            exit;
        }

        if ($action === 'delete_listing') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Valid house ID required.']);
                exit;
            }
            $active = $pdo->prepare("SELECT COUNT(*) FROM tenancies WHERE listing_id=:id AND status='active'");
            $active->execute(['id' => $id]);
            if ((int)$active->fetchColumn() > 0) {
                http_response_code(409);
                echo json_encode(['error' => 'This house has an active tenant. End the tenancy before deleting it.']);
                exit;
            }
            $statement = $pdo->prepare('DELETE FROM listings WHERE id=:id');
            $statement->execute(['id' => $id]);
            if (!$statement->rowCount()) {
                http_response_code(404);
                echo json_encode(['error' => 'House not found.']);
                exit;
            }
            adminAudit($pdo, $session, 'delete_listing', 'listing', $id);
            echo json_encode(['success' => true]);
            exit;
        }

        if ($action === 'end_tenancy') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Valid tenancy ID required.']);
                exit;
            }
            $pdo->beginTransaction();
            $statement = $pdo->prepare("SELECT t.tenant_id,t.listing_id,l.title FROM tenancies t JOIN listings l ON l.id=t.listing_id WHERE t.id=:id AND t.status='active' FOR UPDATE");
            $statement->execute(['id' => $id]);
            $tenancy = $statement->fetch();
            if (!$tenancy) {
                $pdo->rollBack();
                http_response_code(404);
                echo json_encode(['error' => 'Current tenancy not found.']);
                exit;
            }
            $update = $pdo->prepare("UPDATE tenancies SET status='ended',ended_at=CURRENT_TIMESTAMP WHERE id=:id AND status='active'");
            $update->execute(['id' => $id]);
            if (!$update->rowCount()) {
                $pdo->rollBack();
                http_response_code(409);
                echo json_encode(['error' => 'The tenancy has already ended.']);
                exit;
            }
            $pdo->prepare('UPDATE listings SET available=1 WHERE id=:id')->execute(['id' => $tenancy['listing_id']]);
            $pdo->prepare('INSERT INTO notifications (user_id,message) VALUES (:user,:message)')->execute([
                'user' => $tenancy['tenant_id'],
                'message' => 'Your tenancy for ' . $tenancy['title'] . ' was ended by the admin.',
            ]);
            adminAudit($pdo, $session, 'end_tenancy', 'tenancy', $id);
            $pdo->commit();
            echo json_encode(['success' => true]);
            exit;
        }

        if ($action === 'add_service') {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                http_response_code(400);
                echo json_encode(['error' => 'Service name is required.']);
                exit;
            }
            $statement = $pdo->prepare('INSERT INTO service_catalog (name,description,phone) VALUES (:name,:description,:phone)');
            $statement->execute([
                'name' => $name,
                'description' => trim($_POST['description'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
            ]);
            $serviceId = (int)$pdo->lastInsertId();
            adminAudit($pdo, $session, 'add_service', 'service', $serviceId);
            echo json_encode(['success' => true, 'id' => $serviceId]);
            exit;
        }

        if ($action === 'deactivate_service') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Valid service ID required.']);
                exit;
            }
            $statement = $pdo->prepare('UPDATE service_catalog SET active=0 WHERE id=:id AND active=1');
            $statement->execute(['id' => $id]);
            if (!$statement->rowCount()) {
                http_response_code(404);
                echo json_encode(['error' => 'Active service not found.']);
                exit;
            }
            adminAudit($pdo, $session, 'deactivate_service', 'service', $id);
            echo json_encode(['success' => true]);
            exit;
        }

        if ($action === 'announcement') {
            $message = trim($_POST['message'] ?? '');
            if ($message === '' || strlen($message) > 250) {
                http_response_code(400);
                echo json_encode(['error' => 'Enter an announcement of up to 250 characters.']);
                exit;
            }
            $statement = $pdo->prepare("INSERT INTO notifications (user_id,message) SELECT id,:message FROM users WHERE role='student' AND active=1");
            $statement->execute(['message' => $message]);
            adminAudit($pdo, $session, 'announcement', 'users', null, $message);
            echo json_encode(['success' => true, 'recipients' => $statement->rowCount()]);
            exit;
        }

        if (in_array($action, ['approve_listing', 'reject_listing'], true)) {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Valid house ID required.']);
                exit;
            }
            $status = $action === 'approve_listing' ? 'approved' : 'rejected';
            $statement = $pdo->prepare('UPDATE listings SET status=:status WHERE id=:id');
            $statement->execute(['status' => $status, 'id' => $id]);
            if (!$statement->rowCount()) {
                http_response_code(404);
                echo json_encode(['error' => 'House not found or status is unchanged.']);
                exit;
            }
            adminAudit($pdo, $session, $action, 'listing', $id);
            echo json_encode(['success' => true]);
            exit;
        }

        if (in_array($action, ['approve_job', 'reject_job'], true)) {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Valid job ID required.']);
                exit;
            }
            $status = $action === 'approve_job' ? 'approved' : 'rejected';
            $statement = $pdo->prepare('UPDATE jobs SET status=:status WHERE id=:id');
            $statement->execute(['status' => $status, 'id' => $id]);
            adminAudit($pdo, $session, $action, 'job', $id);
            echo json_encode(['success' => true]);
            exit;
        }

        if (in_array($action, ['approve_user', 'suspend_user', 'activate_user'], true)) {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Valid account ID required.']);
                exit;
            }
            if ($action === 'approve_user') {
                $statement = $pdo->prepare("UPDATE users SET approved=1 WHERE id=:id AND role<>'admin'");
            } else {
                $statement = $pdo->prepare('UPDATE users SET active=:active WHERE id=:id AND role<>\'admin\'');
                $statement->bindValue(':active', $action === 'activate_user' ? 1 : 0, PDO::PARAM_INT);
                $statement->bindValue(':id', $id, PDO::PARAM_INT);
                $statement->execute();
                adminAudit($pdo, $session, $action, 'user', $id);
                echo json_encode(['success' => true]);
                exit;
            }
            $statement->execute(['id' => $id]);
            if (!$statement->rowCount()) {
                http_response_code(404);
                echo json_encode(['error' => 'Account not found or action not allowed.']);
                exit;
            }
            adminAudit($pdo, $session, $action, 'user', $id);
            echo json_encode(['success' => true]);
            exit;
        }

        http_response_code(400);
        echo json_encode(['error' => 'Unknown admin action.']);
        exit;
    }

    $users = $pdo->query("SELECT id,role,full_name,email,phone,active,approved,created_at FROM users WHERE role<>'admin' ORDER BY created_at DESC")->fetchAll();
    $listings = $pdo->query('SELECT l.id,l.landlord_id,l.title,l.city,l.neighborhood,l.listing_type,l.price,l.status,l.available,u.full_name AS landlord_name,(SELECT t.tenant_id FROM tenancies t WHERE t.listing_id=l.id AND t.status=\'active\' LIMIT 1) AS tenant_id FROM listings l JOIN users u ON u.id=l.landlord_id ORDER BY l.created_at DESC')->fetchAll();
    $interiorPhotos = campusnestInteriorPhotosByListing($pdo, array_column($listings, 'id'));
    foreach ($listings as &$listing) {
        $listing['interior_photos'] = $interiorPhotos[(int)$listing['id']] ?? [];
        campusnestApplyHousePhotoFallback($listing);
    }
    unset($listing);
    $tenants = $pdo->query("SELECT t.id AS tenancy_id,t.tenant_id,t.rent_amount,t.started_at,l.id AS listing_id,l.title AS house_title,l.city,u.full_name AS tenant_name,u.email,u.phone FROM tenancies t JOIN listings l ON l.id=t.listing_id JOIN users u ON u.id=t.tenant_id WHERE t.status='active' ORDER BY t.started_at DESC")->fetchAll();
    $payments = $pdo->query('SELECT p.id,p.amount,p.payment_type,p.period,p.status,p.created_at,u.full_name AS tenant_name,l.title AS house_title FROM payments p JOIN tenancies t ON t.id=p.tenancy_id JOIN listings l ON l.id=t.listing_id JOIN users u ON u.id=p.payer_id ORDER BY p.created_at DESC LIMIT 100')->fetchAll();
    $services = $pdo->query('SELECT id,name,description,phone,active FROM service_catalog ORDER BY active DESC,name')->fetchAll();
    $messages = $pdo->query("SELECT c.id AS conversation_id,t.id AS tenancy_id,t.tenant_id,u.full_name AS tenant_name,l.title AS house_title,(SELECT m.body FROM messages m WHERE m.conversation_id=c.id ORDER BY m.id DESC LIMIT 1) AS last_message,(SELECT COUNT(*) FROM messages m JOIN users sender ON sender.id=m.sender_id WHERE m.conversation_id=c.id AND sender.role='student' AND m.read_at IS NULL) AS unread FROM conversations c JOIN tenancies t ON t.id=c.tenancy_id JOIN listings l ON l.id=t.listing_id JOIN users u ON u.id=t.tenant_id ORDER BY c.created_at DESC")->fetchAll();
    $activity = $pdo->query('SELECT a.id,a.actor_id,a.action,a.target_type,a.target_id,a.details,a.created_at,u.full_name AS actor_name FROM audit_log a LEFT JOIN users u ON u.id=a.actor_id ORDER BY a.created_at DESC LIMIT 100')->fetchAll();
    $repairs = $pdo->query("SELECT r.id,r.status,r.landlord_decision,l.title FROM repairs r JOIN listings l ON l.id=r.listing_id ORDER BY r.created_at DESC LIMIT 100")->fetchAll();
    $jobs = $pdo->query('SELECT id,title,location,status,created_at FROM jobs ORDER BY created_at DESC')->fetchAll();

    echo json_encode([
        'users' => $users,
        'landlords' => array_values(array_filter($users, static fn(array $user): bool => $user['role'] === 'landlord' && (bool)$user['active'])),
        'tenants' => $tenants,
        'listings' => $listings,
        'payments' => $payments,
        'services' => $services,
        'messages' => $messages,
        'activity' => $activity,
        'repairs' => $repairs,
        'jobs' => $jobs,
    ]);
} catch (Throwable $error) {
    error_log('Admin endpoint failed: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Unable to process administration request.']);
}

function adminAudit(PDO $pdo, array $session, string $action, string $targetType, ?int $targetId, string $details = ''): void
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
