<?php
require_once __DIR__ . '/connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.html');
    exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    http_response_code(400);
    exit('Please enter a valid email and password.');
}

try {
    $pdo = campusnestDatabase();
    $statement = $pdo->prepare('SELECT id, role, full_name, password_hash, active, approved FROM users WHERE email = :email');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        exit('Email or password is incorrect.');
    }

    if (!(bool)$user['active']) {
        http_response_code(403);
        exit('This account has been suspended.');
    }

    if (in_array($user['role'], ['repairer', 'cleaner', 'landlord'], true) && !(bool)$user['approved']) {
        http_response_code(403);
        exit('This account is awaiting administrator approval.');
    }

    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];

    header('Location: ../pages/dashboard.html');
    exit;
} catch (PDOException $error) {
    http_response_code(500);
    exit('Unable to log in.');
}
