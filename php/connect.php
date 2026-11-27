<?php
function campusnestDatabase(): PDO
{
    $host = 'localhost';
    $dbname = 'campusnest';
    $username = 'root';
    $password = '';

    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbname`");

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

        $pdo->exec("CREATE TABLE IF NOT EXISTS listings (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            landlord_id INT UNSIGNED NOT NULL,
            title VARCHAR(180) NOT NULL,
            institution VARCHAR(180),
            city VARCHAR(100) NOT NULL,
            neighborhood VARCHAR(100),
            listing_type VARCHAR(30) NOT NULL,
            price INT UNSIGNED NOT NULL,
            description TEXT,
            furnished TINYINT(1) DEFAULT 0,
            available TINYINT(1) DEFAULT 1,
            latitude DECIMAL(10,7),
            longitude DECIMAL(10,7),
            photo_url VARCHAR(255),
            status ENUM('pending','approved','rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS listing_photos (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            listing_id INT UNSIGNED NOT NULL,
            photo_url VARCHAR(1000) NOT NULL,
            sort_order SMALLINT UNSIGNED NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY idx_listing_photo_order (listing_id,sort_order),
            FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS inquiries (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            listing_id INT UNSIGNED NOT NULL,
            student_id INT UNSIGNED NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
            FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS repairs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            student_id INT UNSIGNED NOT NULL,
            listing_id INT UNSIGNED NOT NULL,
            category VARCHAR(50) NOT NULL,
            description TEXT NOT NULL,
            photo_url VARCHAR(255),
            status VARCHAR(40) NOT NULL DEFAULT 'Submitted',
            landlord_decision VARCHAR(20),
            repairer_id INT UNSIGNED,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS cleaning_requests (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            student_id INT UNSIGNED NOT NULL,
            location VARCHAR(180) NOT NULL,
            preferred_date DATE NOT NULL,
            service_type VARCHAR(50) NOT NULL,
            notes TEXT,
            cleaner_id INT UNSIGNED,
            status VARCHAR(30) NOT NULL DEFAULT 'Submitted',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS jobs (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employer_id INT UNSIGNED NOT NULL,
            title VARCHAR(180) NOT NULL,
            description TEXT NOT NULL,
            location VARCHAR(180) NOT NULL,
            job_type VARCHAR(40) NOT NULL,
            requirements TEXT,
            compensation VARCHAR(100),
            application_instructions TEXT,
            status ENUM('pending','approved','rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS applications (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            job_id INT UNSIGNED NOT NULL,
            student_id INT UNSIGNED NOT NULL,
            message TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_application (job_id, student_id),
            FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
            FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            message VARCHAR(255) NOT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS tenancies (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            listing_id INT UNSIGNED NOT NULL,
            tenant_id INT UNSIGNED NOT NULL,
            rent_amount INT UNSIGNED NOT NULL,
            status ENUM('active','ended') NOT NULL DEFAULT 'active',
            started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            ended_at TIMESTAMP NULL,
            INDEX idx_tenancies_tenant (tenant_id,status),
            INDEX idx_tenancies_listing (listing_id,status),
            FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
            FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            tenancy_id INT UNSIGNED NOT NULL,
            payer_id INT UNSIGNED NOT NULL,
            amount INT UNSIGNED NOT NULL,
            payment_type ENUM('rent','electricity','water') NOT NULL,
            period CHAR(7) NOT NULL,
            status ENUM('pending','paid','rejected') NOT NULL DEFAULT 'pending',
            verified_by INT UNSIGNED NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            paid_at TIMESTAMP NULL,
            INDEX idx_payments_tenancy (tenancy_id,status),
            INDEX idx_payments_payer (payer_id),
            FOREIGN KEY (tenancy_id) REFERENCES tenancies(id) ON DELETE CASCADE,
            FOREIGN KEY (payer_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            tenancy_id INT UNSIGNED NOT NULL UNIQUE,
            rating TINYINT UNSIGNED NOT NULL,
            comment TEXT,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (tenancy_id) REFERENCES tenancies(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS conversations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            tenancy_id INT UNSIGNED NOT NULL UNIQUE,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (tenancy_id) REFERENCES tenancies(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            conversation_id INT UNSIGNED NOT NULL,
            sender_id INT UNSIGNED NOT NULL,
            body TEXT NOT NULL,
            read_at TIMESTAMP NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_messages_conversation (conversation_id,created_at),
            FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS service_catalog (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            description TEXT,
            phone VARCHAR(30),
            active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS audit_log (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            actor_id INT UNSIGNED,
            action VARCHAR(100) NOT NULL,
            target_type VARCHAR(50),
            target_id INT UNSIGNED,
            details TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        return $pdo;
    } catch (PDOException $error) {
        http_response_code(500);
        exit('Database connection failed.');
    }
}

function campusnestRequireLogin(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: /BINARYHQ/BINARYHQ/pages/login.html');
        exit;
    }
    return $_SESSION;
}

function campusnestRequireRole(array $roles): array
{
    $session = campusnestRequireLogin();
    if (!in_array($session['user_role'] ?? '', $roles, true)) {
        http_response_code(403);
        exit('You do not have permission to access this page.');
    }
    return $session;
}

function campusnestParseInteriorPhotoUrls(string $input): array
{
    $urls = preg_split('/\R/u', trim($input)) ?: [];
    $urls = array_values(array_unique(array_filter(array_map('trim', $urls), static fn(string $url): bool => $url !== '')));
    if (count($urls) > 12) {
        throw new InvalidArgumentException('Add no more than 12 interior photo URLs.');
    }

    foreach ($urls as $url) {
        $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
        if (strlen($url) > 1000 || !filter_var($url, FILTER_VALIDATE_URL) || !in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException('Each interior photo must be a valid HTTP or HTTPS URL.');
        }
    }

    return $urls;
}

function campusnestReplaceInteriorPhotos(PDO $pdo, int $listingId, array $urls): void
{
    $pdo->prepare('DELETE FROM listing_photos WHERE listing_id=:listing_id')->execute(['listing_id' => $listingId]);
    $statement = $pdo->prepare('INSERT INTO listing_photos (listing_id,photo_url,sort_order) VALUES (:listing_id,:photo_url,:sort_order)');
    foreach ($urls as $index => $url) {
        $statement->execute([
            'listing_id' => $listingId,
            'photo_url' => $url,
            'sort_order' => $index + 1,
        ]);
    }
}

function campusnestInteriorPhotosByListing(PDO $pdo, array $listingIds): array
{
    $listingIds = array_values(array_unique(array_filter(array_map('intval', $listingIds), static fn(int $id): bool => $id > 0)));
    if (!$listingIds) return [];

    $placeholders = implode(',', array_fill(0, count($listingIds), '?'));
    $statement = $pdo->prepare("SELECT listing_id,photo_url FROM listing_photos WHERE listing_id IN ($placeholders) ORDER BY listing_id,sort_order");
    $statement->execute($listingIds);
    $photos = [];
    foreach ($statement->fetchAll() as $photo) {
        $photos[(int)$photo['listing_id']][] = $photo['photo_url'];
    }
    return $photos;
}

function campusnestApplyHousePhotoFallback(array &$listing): void
{
    $defaultPhoto = 'assets/images/house-elevation.jpg';
    if (trim((string)($listing['photo_url'] ?? '')) === '') {
        $listing['photo_url'] = $defaultPhoto;
    }
    if (empty($listing['interior_photos'])) {
        $listing['interior_photos'] = [
            $defaultPhoto . '#front',
            $defaultPhoto . '#aerial',
            $defaultPhoto . '#left',
            $defaultPhoto . '#right',
        ];
    }
}
