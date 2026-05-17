<?php
// php/login.php
require_once __DIR__ . '/config.php';

$input  = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? 'login';

// ── LOGOUT ────────────────────────────────────────────────────────────────
if ($action === 'logout') {
    $token = $input['token'] ?? '';
    if ($token) {
        deleteSessionByToken($token);
    }
    jsonResponse(['success' => true]);
}

// ── LOGIN ─────────────────────────────────────────────────────────────────
$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? '');

if (!$username || !$password) {
    jsonResponse(['success' => false, 'message' => 'Username and password are required.']);
}

$conn = getMySQLConn();

// Fetch user — prepared statement
$stmt = $conn->prepare('SELECT id, name, email, username, password_hash FROM users WHERE username = ? LIMIT 1');
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$user || !password_verify($password, $user['password_hash'])) {
    jsonResponse(['success' => false, 'message' => 'Invalid username or password.']);
}

// Store session
$token = generateToken();
saveSessionByToken($token, [
    'id'       => $user['id'],
    'username' => $user['username'],
]);

jsonResponse([
    'success' => true,
    'token'   => $token,
    'user'    => [
        'id'       => $user['id'],
        'name'     => $user['name'],
        'email'    => $user['email'],
        'username' => $user['username'],
    ],
]);
