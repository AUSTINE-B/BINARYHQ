<?php
require_once __DIR__ . '/connect.php';

if (PHP_SAPI !== 'cli' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Content-Type: text/plain; charset=utf-8');
    exit("CampusNest database setup is ready. Send a POST request to run it.");
}

try {
    $pdo = campusnestDatabase();

    $adminEmail = 'admin@campusnest.cm';
    $statement = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $statement->execute(['email' => $adminEmail]);

    if (!$statement->fetch()) {
        $statement = $pdo->prepare("INSERT INTO users(role,full_name,email,phone,password_hash,active,approved) VALUES('admin','CampusNest Administrator',:email,'',:password,1,1)");
        $statement->execute([
            'email' => $adminEmail,
            'password' => password_hash('Admin123!', PASSWORD_DEFAULT),
        ]);
    }

    $tables = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'campusnest'")->fetchColumn();
    header('Content-Type: text/plain; charset=utf-8');
    echo "CampusNest database is ready with {$tables} tables.\n";
    echo "Admin login: {$adminEmail} / Admin123!\n";
} catch (Throwable $error) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Database setup failed: ' . $error->getMessage());
}
