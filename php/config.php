<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

// ── MySQL ──────────────────────────────────────────────────────────────────

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'internship_db');

// ── MongoDB ────────────────────────────────────────────────────────────────

define('MONGO_URI', 'mongodb://localhost:27017');
define('MONGO_DB', 'internship_profiles');
define('MONGO_COLL', 'profiles');

// ── Redis ──────────────────────────────────────────────────────────────────

define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
define('SESSION_TTL', 3600);

// ── JSON Response Helper ───────────────────────────────────────────────────

function jsonResponse(array $data): void
{
    header('Content-Type: application/json');

    echo json_encode($data);

    exit;
}

// ── MySQL Connection ───────────────────────────────────────────────────────

function getMySQLConn(): mysqli
{
    $conn = new mysqli(
        DB_HOST,
        DB_USER,
        DB_PASS,
        DB_NAME
    );

    if ($conn->connect_error) {
        jsonResponse([
            'success' => false,
            'message' => 'MySQL Connection Failed: ' . $conn->connect_error
        ]);
    }

    return $conn;
}

function useRedis(): bool
{
    return class_exists('Redis');
}

function useMongo(): bool
{
    return class_exists('MongoDB\\Client');
}

function initSessionFallback(): void
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
    if (useRedis()) {
        $redis = getRedis();
        $redis->setex("session:$token", SESSION_TTL, json_encode($payload));
        return;
    }

    initSessionFallback();
    $_SESSION['sessions'][$token] = $payload;
}

function deleteSessionByToken(string $token): void
{
    if (useRedis()) {
        $redis = getRedis();
        $redis->del("session:$token");
        return;
    }

    initSessionFallback();
    unset($_SESSION['sessions'][$token]);
}

function getSessionByToken(string $token): ?array
{
    if (useRedis()) {
        $redis = getRedis();
        $data = $redis->get("session:$token");

        return $data ? json_decode($data, true) : null;
    }

    initSessionFallback();

    return $_SESSION['sessions'][$token] ?? null;
}

// ── MongoDB Collection ─────────────────────────────────────────────────────

function getMongoCollection(): MongoDB\Collection
{
    if (!useMongo()) {
        jsonResponse([
            'success' => false,
            'message' => 'MongoDB extension is not installed.'
        ]);
    }

    $client = new MongoDB\Client(MONGO_URI);

    return $client->selectCollection(
        MONGO_DB,
        MONGO_COLL
    );
}

// ── Redis Connection ───────────────────────────────────────────────────────

function getRedis(): Redis
{
    if (!useRedis()) {
        jsonResponse([
            'success' => false,
            'message' => 'Redis extension is not installed.'
        ]);
    }

    $redis = new Redis();
    $redis->connect(
        REDIS_HOST,
        REDIS_PORT
    );

    return $redis;
}

// ── Generate Session Token ─────────────────────────────────────────────────

function generateToken(): string
{
    return bin2hex(random_bytes(32));
}

// ── Validate Token ─────────────────────────────────────────────────────────

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