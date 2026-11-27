<?php
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function astraBridgeError(int $status, string $message): void
{
    http_response_code($status);
    echo json_encode(['error' => $message], JSON_UNESCAPED_SLASHES);
    exit;
}

$resource = $_GET['resource'] ?? '';
if (!is_string($resource) || !in_array($resource, ['catalog', 'vacancies'], true)) {
    astraBridgeError(400, 'Unknown ASTRA resource.');
}

$baseUrl = trim((string)(getenv('ASTRA_BASE_URL') ?: 'http://localhost/ASTRA'));
$urlParts = parse_url($baseUrl);
if (!$urlParts || !in_array(strtolower((string)($urlParts['scheme'] ?? '')), ['http', 'https'], true) || empty($urlParts['host']) || isset($urlParts['user']) || isset($urlParts['pass']) || isset($urlParts['query']) || isset($urlParts['fragment'])) {
    error_log('ASTRA bridge has an invalid ASTRA_BASE_URL.');
    astraBridgeError(503, 'ASTRA service URL is not configured correctly.');
}

$baseUrl = rtrim($baseUrl, '/');
$query = [];
if ($resource === 'vacancies') {
    $maxLengths = ['q' => 120, 'location' => 120, 'type' => 60];
    foreach (['q', 'location', 'type'] as $filter) {
        if (isset($_GET[$filter]) && !is_string($_GET[$filter])) {
            astraBridgeError(400, 'Job filters must be text.');
        }
        $value = trim($_GET[$filter] ?? '');
        if (strlen($value) > $maxLengths[$filter]) astraBridgeError(422, 'A job filter is too long.');
        if ($value !== '') $query[$filter] = $value;
    }
}

if (!function_exists('curl_init')) {
    error_log('ASTRA bridge requires the PHP cURL extension.');
    astraBridgeError(503, 'ASTRA service connection is unavailable.');
}

$requestUrl = $baseUrl . '/api/v1/' . $resource;
if ($query) $requestUrl .= '?' . http_build_query($query);
$curl = curl_init($requestUrl);
if ($curl === false) astraBridgeError(503, 'ASTRA service connection is unavailable.');
if (!curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 3,
    CURLOPT_TIMEOUT => 8,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_HTTPHEADER => ['Accept: application/json'],
])) {
    curl_close($curl);
    astraBridgeError(503, 'ASTRA service connection is unavailable.');
}

$responseBody = curl_exec($curl);
$httpStatus = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
$curlError = curl_error($curl);
curl_close($curl);
if ($responseBody === false || $httpStatus < 200 || $httpStatus >= 300 || strlen($responseBody) > 2000000) {
    error_log('ASTRA bridge request failed with HTTP ' . $httpStatus . ($curlError !== '' ? ': ' . $curlError : ''));
    astraBridgeError(502, 'ASTRA is temporarily unavailable. Please try again later.');
}

try {
    $payload = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $error) {
    error_log('ASTRA bridge received invalid JSON: ' . $error->getMessage());
    astraBridgeError(502, 'ASTRA returned an invalid response.');
}

$data = $payload['data'] ?? null;
if (!is_array($data)) astraBridgeError(502, 'ASTRA returned an invalid response.');

if ($resource === 'catalog') {
    if (!isset($data['items']) || !is_array($data['items'])) astraBridgeError(502, 'ASTRA returned an invalid catalog.');
    echo json_encode([
        'items' => $data['items'],
        'notice' => $data['notice'] ?? '',
        'portal_url' => $baseUrl . '/',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!isset($data['vacancies']) || !is_array($data['vacancies'])) astraBridgeError(502, 'ASTRA returned an invalid vacancy list.');
echo json_encode([
    'results' => $data['vacancies'],
    'portal_url' => $baseUrl . '/',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
