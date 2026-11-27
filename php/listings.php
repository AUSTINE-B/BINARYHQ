<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/connect.php';

try {
    $pdo = campusnestDatabase();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $session = campusnestRequireRole(['landlord', 'admin']);
        $action = $_POST['action'] ?? 'add_listing';
        if ($action === 'update_interior_photos') {
            $listingId = filter_input(INPUT_POST, 'listing_id', FILTER_VALIDATE_INT);
            if (!$listingId) {
                http_response_code(400);
                echo json_encode(['error' => 'A valid house is required.']);
                exit;
            }

            $listingQuery = $session['user_role'] === 'admin'
                ? $pdo->prepare('SELECT id FROM listings WHERE id=:id')
                : $pdo->prepare('SELECT id FROM listings WHERE id=:id AND landlord_id=:landlord_id');
            $listingParams = ['id' => $listingId];
            if ($session['user_role'] !== 'admin') $listingParams['landlord_id'] = $session['user_id'];
            $listingQuery->execute($listingParams);
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
                campusnestReplaceInteriorPhotos($pdo, (int)$listingId, $interiorPhotos);
                $pdo->commit();
            } catch (Throwable $error) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $error;
            }

            echo json_encode(['success' => true]);
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_INT);
        $listingType = trim($_POST['listing_type'] ?? '');

        if ($title === '' || $city === '' || !$price || $listingType === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Title, city, price, and accommodation type are required.']);
            exit;
        }

        try {
            $interiorPhotos = campusnestParseInteriorPhotoUrls($_POST['interior_photo_urls'] ?? '');
        } catch (InvalidArgumentException $error) {
            http_response_code(400);
            echo json_encode(['error' => $error->getMessage()]);
            exit;
        }

        $statement = $pdo->prepare("INSERT INTO listings (landlord_id, title, institution, city, neighborhood, listing_type, price, description, furnished, available, latitude, longitude, photo_url, status) VALUES (:landlord_id, :title, :institution, :city, :neighborhood, :listing_type, :price, :description, :furnished, :available, :latitude, :longitude, :photo_url, 'pending')");
        $pdo->beginTransaction();
        try {
            $statement->execute([
                'landlord_id' => $session['user_id'],
                'title' => $title,
                'institution' => trim($_POST['institution'] ?? ''),
                'city' => $city,
                'neighborhood' => trim($_POST['neighborhood'] ?? ''),
                'listing_type' => $listingType,
                'price' => $price,
                'description' => trim($_POST['description'] ?? ''),
                'furnished' => isset($_POST['furnished']) ? 1 : 0,
                'available' => isset($_POST['available']) ? 1 : 0,
                'latitude' => ($_POST['latitude'] ?? '') !== '' ? (float)$_POST['latitude'] : null,
                'longitude' => ($_POST['longitude'] ?? '') !== '' ? (float)$_POST['longitude'] : null,
                'photo_url' => trim($_POST['photo_url'] ?? '') ?: 'assets/images/house-elevation.jpg',
            ]);
            $listingId = (int)$pdo->lastInsertId();
            campusnestReplaceInteriorPhotos($pdo, $listingId, $interiorPhotos);
            $pdo->commit();
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $error;
        }
        echo json_encode(['success' => true, 'id' => $listingId]);
        exit;
    }

    $query = trim($_GET['q'] ?? '');
    $city = trim($_GET['city'] ?? '');
    $type = trim($_GET['type'] ?? '');
    $maxPrice = filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_INT);
    $sort = $_GET['sort'] ?? 'date';

    $sql = "SELECT * FROM listings WHERE status = 'approved' AND available = 1";
    $params = [];
    if ($query !== '') {
        $sql .= " AND (title LIKE :query OR institution LIKE :query OR city LIKE :query OR neighborhood LIKE :query)";
        $params['query'] = '%' . $query . '%';
    }
    if ($city !== '') { $sql .= ' AND city LIKE :city'; $params['city'] = '%' . $city . '%'; }
    if ($type !== '') { $sql .= ' AND listing_type = :type'; $params['type'] = $type; }
    if ($maxPrice) { $sql .= ' AND price <= :max_price'; $params['max_price'] = $maxPrice; }

    $order = match ($sort) {
        'price_low' => 'price ASC',
        'price_high' => 'price DESC',
        'distance' => 'title ASC',
        default => 'created_at DESC',
    };
    $sql .= ' ORDER BY ' . $order;

    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    $results = $statement->fetchAll();
    if ($results) {
        $interiorPhotos = campusnestInteriorPhotosByListing($pdo, array_column($results, 'id'));
        foreach ($results as &$listing) {
            $listing['interior_photos'] = $interiorPhotos[(int)$listing['id']] ?? [];
            campusnestApplyHousePhotoFallback($listing);
        }
        unset($listing);
    }

    echo json_encode(['results' => $results, 'count' => count($results)]);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to process accommodation listings.']);
}
