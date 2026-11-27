<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/connect.php';

try {
    $pdo = campusnestDatabase();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $session = campusnestRequireRole(['employer', 'admin']);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $location = trim($_POST['location'] ?? '');
        if ($title === '' || $description === '' || $location === '') {
            http_response_code(400); echo json_encode(['error'=>'Complete all required job fields.']); exit;
        }
        $statement = $pdo->prepare("INSERT INTO jobs (employer_id,title,description,location,job_type,requirements,compensation,application_instructions,status) VALUES (:employer_id,:title,:description,:location,:job_type,:requirements,:compensation,:instructions,'pending')");
        $statement->execute(['employer_id'=>$session['user_id'],'title'=>$title,'description'=>$description,'location'=>$location,'job_type'=>trim($_POST['job_type'] ?? 'Other'),'requirements'=>trim($_POST['requirements'] ?? ''),'compensation'=>trim($_POST['compensation'] ?? ''),'instructions'=>trim($_POST['application_instructions'] ?? '')]);
        echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]); exit;
    }

    $q = trim($_GET['q'] ?? ''); $location = trim($_GET['location'] ?? ''); $type = trim($_GET['type'] ?? '');
    $sql = "SELECT * FROM jobs WHERE status='approved'"; $params=[];
    if ($q !== '') { $sql .= ' AND (title LIKE :q OR description LIKE :q OR requirements LIKE :q)'; $params['q']='%' . $q . '%'; }
    if ($location !== '') { $sql .= ' AND location LIKE :location'; $params['location']='%' . $location . '%'; }
    if ($type !== '') { $sql .= ' AND job_type=:type'; $params['type']=$type; }
    $sql .= ' ORDER BY created_at DESC';
    $statement=$pdo->prepare($sql); $statement->execute($params);
    echo json_encode(['results'=>$statement->fetchAll(),'count'=>$statement->rowCount()]);
} catch (Throwable $error) { http_response_code(500); echo json_encode(['error'=>'Unable to process job listings.']); }
