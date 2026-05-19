<?php
// php/login.php

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

try {

    // Read JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        jsonResponse([
            'success' => false,
            'message' => 'Invalid JSON request.'
        ], 400);
    }

    $action = $input['action'] ?? 'login';

    // ── LOGOUT ─────────────────────────────────────────────
    if ($action === 'logout') {

        $token = $input['token'] ?? '';

        if ($token) {
            deleteSessionByToken($token);
        }

        session_destroy();

        jsonResponse([
            'success' => true,
            'message' => 'Logout successful.'
        ]);
    }

    // ── LOGIN ──────────────────────────────────────────────

    $username = trim($input['username'] ?? '');
    $password = trim($input['password'] ?? '');

    // Validation
    if (empty($username) || empty($password)) {

        jsonResponse([
            'success' => false,
            'message' => 'Username and password are required.'
        ], 400);
    }

    // Database connection
    $conn = getMySQLConn();

    // Find user
    $stmt = $conn->prepare("
        SELECT 
            id,
            name,
            email,
            username,
            password_hash
        FROM users
        WHERE username = ?
        LIMIT 1
    ");

    $stmt->bind_param('s', $username);

    $stmt->execute();

    $result = $stmt->get_result();

    $user = $result->fetch_assoc();

    $stmt->close();

    // User not found
    if (!$user) {

        $conn->close();

        jsonResponse([
            'success' => false,
            'message' => 'User not found.'
        ], 401);
    }

    // Verify password
    if (!password_verify($password, $user['password_hash'])) {

        $conn->close();

        jsonResponse([
            'success' => false,
            'message' => 'Invalid password.'
        ], 401);
    }

    // Create session token
    $token = generateToken();

    // Save PHP session
    $_SESSION['user_id'] = $user['id'];

    // Save token session
    saveSessionByToken($token, [
        'id'       => $user['id'],
        'username' => $user['username'],
    ]);

    $conn->close();

    // Success response
    jsonResponse([
        'success' => true,
        'message' => 'Login successful.',
        'token'   => $token,
        'user'    => [
            'id'       => $user['id'],
            'name'     => $user['name'],
            'email'    => $user['email'],
            'username' => $user['username'],
        ],
    ]);

} catch (Exception $e) {

    jsonResponse([
        'success' => false,
        'message' => 'Server error.',
        'error'   => $e->getMessage()
    ], 500);
}