<?php
// php/register.php

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

try {

    $input = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!$input) {

        jsonResponse([
            'success' => false,
            'message' => 'Invalid request.'
        ]);
    }

    // Clean values
    $name = sanitizeText($input['name'] ?? '');

    $email = sanitizeText($input['email'] ?? '');

    $username = sanitizeText($input['username'] ?? '');

    $password = trim($input['password'] ?? '');

    // Validation
    if (!$name || !$email || !$username || !$password) {

        jsonResponse([
            'success' => false,
            'message' => 'All fields are required.'
        ]);
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        jsonResponse([
            'success' => false,
            'message' => 'Invalid email address.'
        ]);
    }

    // Password validation
    if (!validatePasswordStrength($password)) {

        jsonResponse([
            'success' => false,
            'message' =>
                'Password must be at least '
                . PASSWORD_MIN_LENGTH .
                ' characters long.'
        ]);
    }

    // Duplicate check
    if (!isUniqueUserIdentity($username, $email)) {

        jsonResponse([
            'success' => false,
            'message' => 'Username or email already exists.'
        ]);
    }

    // Database connection
    $conn = getMySQLConn();

    // Hash password
    $hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    // Insert user
    $stmt = $conn->prepare("
        INSERT INTO users (
            name,
            email,
            username,
            password_hash,
            created_at,
            updated_at
        )
        VALUES (?, ?, ?, ?, NOW(), NOW())
    ");

    $stmt->bind_param(
        'ssss',
        $name,
        $email,
        $username,
        $hash
    );

    $stmt->execute();

    $userId = $stmt->insert_id;

    $stmt->close();

    $conn->close();

    // Sync admin dashboard
    // syncProfileToAdminDatabase([

    //     'user_id'  => $userId,

    //     'name'     => $name,

    //     'email'    => $email,

    //     'username' => $username,

    //     'age'      => null,

    //     'dob'      => null,

    //     'country'  => null,

    //     'state'    => null,

    //     'city'     => null,

    //     'pincode'  => null,

    //     'contact'  => null,

    //     'bio'      => null,
    // ]);

    // Success
    jsonResponse([
        'success' => true,
        'message' => 'Registration successful.'
    ]);

} catch (Exception $e) {

    jsonResponse([
        'success' => false,
        'message' => 'Server error.',
        'error'   => $e->getMessage()
    ]);
}