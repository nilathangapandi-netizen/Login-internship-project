<?php

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

try {

    $method = $_SERVER['REQUEST_METHOD'];

    // ───────────────── GET PROFILE ─────────────────

    if ($method === 'GET') {

        $user_id = (int)($_GET['user_id'] ?? 0);

        if (!$user_id) {

            jsonResponse([
                'success' => false,
                'message' => 'Invalid user ID.'
            ]);
        }

        $conn = getMySQLConn();

        $stmt = $conn->prepare("
            SELECT age, dob, contact, city, bio
            FROM users
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->bind_param('i', $user_id);

        $stmt->execute();

        $result = $stmt->get_result();

        $profile = $result->fetch_assoc();

        $stmt->close();

        $conn->close();

        jsonResponse([
            'success' => true,
            'profile' => $profile
        ]);
    }

    // ───────────────── UPDATE PROFILE ─────────────────

    if ($method === 'POST') {

        $input = json_decode(file_get_contents('php://input'), true);

        $user_id = (int)($input['user_id'] ?? 0);

        $age = $input['age'] ?? null;

        $dob = $input['dob'] ?? null;

        $contact = $input['contact'] ?? null;

        $city = $input['city'] ?? null;

        $bio = $input['bio'] ?? null;

        $conn = getMySQLConn();

        $stmt = $conn->prepare("
            UPDATE users
            SET
                age = ?,
                dob = ?,
                contact = ?,
                city = ?,
                bio = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            'issssi',
            $age,
            $dob,
            $contact,
            $city,
            $bio,
            $user_id
        );

        $stmt->execute();

        $stmt->close();

        $conn->close();

        jsonResponse([
            'success' => true,
            'message' => 'Profile updated successfully.'
        ]);
    }

    jsonResponse([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);

} catch (Exception $e) {

    jsonResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 500);
}