<?php
// php/profile.php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

// ── GET profile ───────────────────────────────────────────────────────────
if ($method === 'GET') {
    $token   = $_GET['token']   ?? '';
    $user_id = $_GET['user_id'] ?? '';

    $session = validateToken($token);
    if (!$session) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized.']);
    }

    $col  = getMongoCollection();
    $doc  = $col->findOne(['user_id' => (int)$user_id]);
    $profile = $doc ? [
        'age'     => $doc['age']     ?? '',
        'dob'     => $doc['dob']     ?? '',
        'contact' => $doc['contact'] ?? '',
        'city'    => $doc['city']    ?? '',
        'bio'     => $doc['bio']     ?? '',
    ] : null;

    jsonResponse(['success' => true, 'profile' => $profile]);
}

// ── POST / update profile ─────────────────────────────────────────────────
if ($method === 'POST') {
    $input   = json_decode(file_get_contents('php://input'), true);
    $token   = $input['token']   ?? '';
    $user_id = (int)($input['user_id'] ?? 0);

    $session = validateToken($token);
    if (!$session) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized.']);
    }

    $col = getMongoCollection();
    $col->updateOne(
        ['user_id' => $user_id],
        ['$set' => [
            'user_id' => $user_id,
            'age'     => $input['age']     ?? '',
            'dob'     => $input['dob']     ?? '',
            'contact' => $input['contact'] ?? '',
            'city'    => $input['city']    ?? '',
            'bio'     => $input['bio']     ?? '',
            'updated_at' => new MongoDB\BSON\UTCDateTime(),
        ]],
        ['upsert' => true]
    );

    jsonResponse(['success' => true]);
}

jsonResponse(['success' => false, 'message' => 'Method not allowed.']);
