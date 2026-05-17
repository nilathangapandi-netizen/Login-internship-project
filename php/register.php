<?php
// php/register.php
require_once __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);

$name     = sanitizeText($input['name'] ?? '');
$email    = sanitizeText($input['email'] ?? '');
$username = sanitizeText($input['username'] ?? '');
$password = trim($input['password'] ?? '');

if (!$name || !$email || !$username || !$password) {
    jsonResponse(['success' => false, 'message' => 'All fields are required.'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Invalid email address.'], 400);
}

if (!validatePasswordStrength($password)) {
    jsonResponse(['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.'], 400);
}

if (!isUniqueUserIdentity($username, $email)) {
    jsonResponse(['success' => false, 'message' => 'Username or email already exists.'], 409);
}

$conn = getMySQLConn();
$stmt = $conn->prepare(
    'INSERT INTO users (name, email, username, password_hash, created_at, updated_at)
     VALUES (?, ?, ?, ?, NOW(), NOW())'
);
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt->bind_param('ssss', $name, $email, $username, $hash);

if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    jsonResponse(['success' => false, 'message' => 'Registration failed. Please try again.'], 500);
}

$userId = $stmt->insert_id;
$stmt->close();
$conn->close();

syncProfileToAdminDatabase([
    'user_id'  => $userId,
    'name'     => $name,
    'email'    => $email,
    'username' => $username,
    'age'      => null,
    'dob'      => null,
    'country'  => null,
    'state'    => null,
    'city'     => null,
    'pincode'  => null,
    'contact'  => null,
    'bio'      => null,
]);

jsonResponse(['success' => true]);
