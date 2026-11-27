<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/connect.php';

try {
    $pdo = campusnestDatabase();
    $session = campusnestRequireRole(['student','landlord','repairer','cleaner','employer','admin']);
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'repair') {
        if ($session['user_role'] !== 'student') { http_response_code(403); echo json_encode(['error'=>'Only students can submit repairs.']); exit; }
        $listingId = filter_input(INPUT_POST,'listing_id',FILTER_VALIDATE_INT);
        $category = trim($_POST['category'] ?? ''); $description = trim($_POST['description'] ?? '');
        if (!$listingId || $category === '' || $description === '') { http_response_code(400); echo json_encode(['error'=>'Property, category, and description are required.']); exit; }
        $owned = $pdo->prepare("SELECT id FROM listings WHERE id=:id AND status='approved'"); $owned->execute(['id'=>$listingId]);
        if (!$owned->fetch()) { http_response_code(400); echo json_encode(['error'=>'A valid approved property is required.']); exit; }
        $statement=$pdo->prepare("INSERT INTO repairs (student_id,listing_id,category,description,photo_url,status) VALUES (:student,:listing,:category,:description,:photo,'Submitted')");
        $statement->execute(['student'=>$session['user_id'],'listing'=>$listingId,'category'=>$category,'description'=>$description,'photo'=>trim($_POST['photo_url'] ?? '')]);
        $id=$pdo->lastInsertId();
        $pdo->prepare("INSERT INTO notifications (user_id,message) VALUES ((SELECT landlord_id FROM listings WHERE id=:id),'A new repair request has been submitted.')")->execute(['id'=>$listingId]);
        echo json_encode(['success'=>true,'id'=>$id]); exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'cleaning') {
        if ($session['user_role'] !== 'student') { http_response_code(403); echo json_encode(['error'=>'Only students can request cleaning.']); exit; }
        $location=trim($_POST['location'] ?? ''); $date=trim($_POST['preferred_date'] ?? ''); $service=trim($_POST['service_type'] ?? '');
        if ($location === '' || $date === '' || $service === '') { http_response_code(400); echo json_encode(['error'=>'Location, date, and service type are required.']); exit; }
        $statement=$pdo->prepare("INSERT INTO cleaning_requests (student_id,location,preferred_date,service_type,notes,status) VALUES (:student,:location,:date,:service,:notes,'Submitted')");
        $statement->execute(['student'=>$session['user_id'],'location'=>$location,'date'=>$date,'service'=>$service,'notes'=>trim($_POST['notes'] ?? '')]);
        echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]); exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'repair_decision') {
        campusnestRequireRole(['landlord','admin']);
        $repairId=filter_input(INPUT_POST,'repair_id',FILTER_VALIDATE_INT); $decision=strtolower(trim($_POST['decision'] ?? ''));
        if (!$repairId || !in_array($decision,['approved','rejected'],true)) { http_response_code(400); echo json_encode(['error'=>'Invalid repair decision.']); exit; }
        $pdo->beginTransaction();
        $statement=$pdo->prepare("UPDATE repairs r JOIN listings l ON l.id=r.listing_id SET r.landlord_decision=:decision,r.status=:status WHERE r.id=:id AND (l.landlord_id=:landlord OR :admin=1) AND r.status='Submitted'");
        $statement->execute(['decision'=>$decision,'status'=>$decision==='approved'?'Approved':'Rejected','id'=>$repairId,'landlord'=>$session['user_id'],'admin'=>$session['user_role']==='admin'?1:0]);
        if (!$statement->rowCount()) { $pdo->rollBack(); http_response_code(409); echo json_encode(['error'=>'Repair decision could not be recorded.']); exit; }
        $pdo->prepare("INSERT INTO audit_log (actor_id,action,target_type,target_id,details) VALUES (:actor,'repair_decision','repair',:repair,:details)")->execute(['actor'=>$session['user_id'],'repair'=>$repairId,'details'=>$decision]);
        $pdo->commit(); echo json_encode(['success'=>true]); exit;
    }

    if ($action === 'repairs') {
        if ($session['user_role']==='student') { $sql="SELECT r.*,l.title FROM repairs r JOIN listings l ON l.id=r.listing_id WHERE r.student_id=:id ORDER BY r.created_at DESC"; }
        elseif ($session['user_role']==='landlord') { $sql="SELECT r.*,l.title FROM repairs r JOIN listings l ON l.id=r.listing_id WHERE l.landlord_id=:id ORDER BY r.created_at DESC"; }
        else { $sql="SELECT r.*,l.title FROM repairs r JOIN listings l ON l.id=r.listing_id ORDER BY r.created_at DESC"; }
        $statement=$pdo->prepare($sql); $statement->execute(['id'=>$session['user_id']]); echo json_encode(['results'=>$statement->fetchAll()]); exit;
    }

    echo json_encode(['error'=>'Unknown action.'], JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) { http_response_code(500); echo json_encode(['error'=>'Unable to process service request.']); }
