<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/connect.php';

try {
    $pdo = campusnestDatabase();
    $session = campusnestRequireRole(['student', 'employer', 'admin']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($session['user_role'] !== 'student') { http_response_code(403); echo json_encode(['error'=>'Only students can apply.']); exit; }
        $jobId = filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT);
        if (!$jobId) { http_response_code(400); echo json_encode(['error'=>'Valid job required.']); exit; }
        $statement = $pdo->prepare("INSERT IGNORE INTO applications (job_id, student_id, message) VALUES (:job, :student, :message)");
        $statement->execute(['job'=>$jobId,'student'=>$session['user_id'],'message'=>trim($_POST['message'] ?? '')]);
        if (!$statement->rowCount()) { http_response_code(409); echo json_encode(['error'=>'You have already applied for this job.']); exit; }
        echo json_encode(['success'=>true]); exit;
    }
    if ($session['user_role'] === 'student') {
        $statement = $pdo->prepare('SELECT j.id,j.title,j.location,j.job_type,j.status,a.created_at,a.message FROM applications a JOIN jobs j ON j.id=a.job_id WHERE a.student_id=:id ORDER BY a.created_at DESC');
        $statement->execute(['id' => $session['user_id']]);
    } else {
        $statement = $pdo->prepare('SELECT a.*,j.title,u.full_name,u.email FROM applications a JOIN jobs j ON j.id=a.job_id JOIN users u ON u.id=a.student_id WHERE j.employer_id=:id OR :admin=1 ORDER BY a.created_at DESC');
        $statement->execute(['id' => $session['user_id'], 'admin' => $session['user_role'] === 'admin' ? 1 : 0]);
    }
    echo json_encode(['results'=>$statement->fetchAll(),'count'=>$statement->rowCount()]);
} catch (Throwable $error) { http_response_code(500); echo json_encode(['error'=>'Unable to process application.']); }
