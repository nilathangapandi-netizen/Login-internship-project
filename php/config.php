<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
]);

// ── Database configuration ─────────────────────────────────────────────────

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_MAIN', 'internship_db');
define('DB_ADMIN', 'admin_dashboard_db');

define('PASSWORD_MIN_LENGTH', 8);

function jsonResponse(array $data, int $status = 200): void
{
    if (!headers_sent()) {
        header('Content-Type: application/json', true, $status);
    }
    echo json_encode($data);
    exit;
}

function getMySQLConn(string $database = DB_MAIN): mysqli
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, $database);
    if ($conn->connect_error) {
        jsonResponse([
            'success' => false,
            'message' => 'Database connection failed: ' . $conn->connect_error,
        ], 500);
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

function sanitizeText(string $value): string
{
    return trim($value);
}

function normalizeNullableValue($value)
{
    $value = trim((string) $value);
    return $value === '' ? null : $value;
}

function validatePasswordStrength(string $password): bool
{
    return mb_strlen($password) >= PASSWORD_MIN_LENGTH;
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        jsonResponse(['success' => false, 'message' => 'Authentication required.'], 401);
    }
}

function getCurrentUserId(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function getCurrentUser(): array
{
    $userId = getCurrentUserId();
    if (!$userId) {
        return [];
    }

    $conn = getMySQLConn();
    $stmt = $conn->prepare('SELECT id, name, username, email, age, dob, country, state, city, pincode, contact, bio FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc() ?: [];
    $stmt->close();
    $conn->close();

    return $user;
}

function isUniqueUserIdentity(string $username, string $email, ?int $excludeId = null): bool
{
    $conn = getMySQLConn();
    $query = 'SELECT id FROM users WHERE (username = ? OR email = ?)';
    if ($excludeId !== null) {
        $query .= ' AND id <> ?';
    }
    $query .= ' LIMIT 1';

    $stmt = $conn->prepare($query);
    if ($excludeId !== null) {
        $stmt->bind_param('ssi', $username, $email, $excludeId);
    } else {
        $stmt->bind_param('ss', $username, $email);
    }
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    $conn->close();

    return !$exists;
}

function syncProfileToAdminDatabase(array $profile): void
{
    $conn = getAdminMySQLConn();
    $stmt = $conn->prepare('INSERT INTO user_profiles (user_id, name, email, username, age, dob, country, state, city, pincode, contact, bio, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE name = VALUES(name), email = VALUES(email), username = VALUES(username), age = VALUES(age), dob = VALUES(dob), country = VALUES(country), state = VALUES(state), city = VALUES(city), pincode = VALUES(pincode), contact = VALUES(contact), bio = VALUES(bio), updated_at = NOW()');
    $stmt->bind_param('ississsssss', $profile['user_id'], $profile['name'], $profile['email'], $profile['username'], $profile['age'], $profile['dob'], $profile['country'], $profile['state'], $profile['city'], $profile['pincode'], $profile['contact'], $profile['bio']);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

function getAdminMySQLConn(): mysqli
{
    return getMySQLConn(DB_ADMIN);
}

function initSessionTokenStorage(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['sessions']) || !is_array($_SESSION['sessions'])) {
        $_SESSION['sessions'] = [];
    }
}

function saveSessionByToken(string $token, array $payload): void
{
    initSessionTokenStorage();
    $_SESSION['sessions'][$token] = $payload;
}

function deleteSessionByToken(string $token): void
{
    initSessionTokenStorage();
    unset($_SESSION['sessions'][$token]);
}

function getSessionByToken(string $token): ?array
{
    initSessionTokenStorage();
    return $_SESSION['sessions'][$token] ?? null;
}

function generateToken(): string
{
    return bin2hex(random_bytes(32));
}

function validateToken(string $token): ?array
{
    return getSessionByToken($token);
}





// ── CORS Headers ───────────────────────────────────────────────────────────

if (PHP_SAPI !== 'cli') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    // ── Handle OPTIONS Request ─────────────────────────────────────────────────
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        exit;
    }
}