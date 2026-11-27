<?php
require_once __DIR__ . '/connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/signup.html');
    exit;
}

$allowedRoles = ['student', 'landlord', 'repairer', 'cleaner', 'employer'];
$fullName = trim($_POST['full_name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$phone = trim($_POST['phone'] ?? '');
$institution = trim($_POST['institution'] ?? '');
$password = $_POST['password'] ?? '';
$confirmation = $_POST['confirm_password'] ?? '';
$role = $_POST['role'] ?? 'student';

if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || $password !== $confirmation || !in_array($role, $allowedRoles, true) || !isset($_POST['terms'])) {
    http_response_code(400);
    exit('Please complete the form correctly.');
}

try {
    $pdo = campusnestDatabase();
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        role VARCHAR(20) NOT NULL,
        full_name VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL UNIQUE,
        phone VARCHAR(30),
        institution VARCHAR(190),
        password_hash VARCHAR(255) NOT NULL,
        active TINYINT(1) NOT NULL DEFAULT 1,
        approved TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $approved = in_array($role, ['student', 'employer'], true) ? 1 : 0;
    $statement = $pdo->prepare('INSERT INTO users (role, full_name, email, phone, institution, password_hash, approved) VALUES (:role, :full_name, :email, :phone, :institution, :password_hash, :approved)');
    $statement->execute([
        'role' => $role,
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone,
        'institution' => $institution,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'approved' => $approved,
    ]);

    session_start();
    $_SESSION['user_id'] = $pdo->lastInsertId();
    $_SESSION['user_role'] = $role;
    $_SESSION['full_name'] = $fullName;

    header('Location: ../pages/dashboard.html');
    exit;
} catch (PDOException $error) {
    if ($error->errorInfo[1] === 1062) {
        http_response_code(409);
        exit('An account already exists for this email.');
    }

    http_response_code(500);
    exit('Unable to create the account.');
}
