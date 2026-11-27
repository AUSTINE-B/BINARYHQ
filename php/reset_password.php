<?php
require_once __DIR__ . '/connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/reset-password.html'); exit;
}
$email=strtolower(trim($_POST['email'] ?? ''));
if (!filter_var($email,FILTER_VALIDATE_EMAIL)) { http_response_code(400); exit('Enter a valid email address.'); }
try {
    $pdo=campusnestDatabase();
    $statement=$pdo->prepare('SELECT id FROM users WHERE email=:email');
    $statement->execute(['email'=>$email]);
    $user=$statement->fetch();
    if ($user) {
        $token=bin2hex(random_bytes(32));
        $pdo->exec('CREATE TABLE IF NOT EXISTS password_resets (email VARCHAR(190) PRIMARY KEY, token_hash VARCHAR(64) NOT NULL, expires_at DATETIME NOT NULL) ENGINE=InnoDB');
        $pdo->prepare('REPLACE INTO password_resets(email,token_hash,expires_at) VALUES(:email,:token,DATE_ADD(NOW(),INTERVAL 30 MINUTE))')->execute(['email'=>$email,'token'=>hash('sha256',$token)]);
    }
    exit('If an account exists for that email, reset instructions have been recorded.');
} catch (Throwable $error) { http_response_code(500); exit('Unable to process password reset.'); }
