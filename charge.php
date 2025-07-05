<?php
$access_token = 'YOUR_PRODUCTION_ACCESS_TOKEN';

if (!isset($_POST['nonce'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing nonce']);
    exit;
}

$nonce = $_POST['nonce'];

$body = [
    'source_id' => $nonce,
    'idempotency_key' => uniqid('test_', true),
    'amount_money' => [
        'amount' => 100,
        'currency' => 'USD'
    ],
    'autocomplete' => false,
    'note' => 'Authorize-only test'
];

$ch = curl_init('https://connect.squareup.com/v2/payments');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $access_token,
    'Square-Version: 2024-06-12'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

if ($http_code === 200) {
    echo json_encode(['status' => 'authorized', 'auth_id' => $data['payment']['id']]);
} else {
    echo json_encode(['status' => 'error', 'errors' => $data['errors'] ?? ['Unknown error']]);
}
