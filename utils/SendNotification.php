<?php
/**
 * Script PHP standalone per inviare una notifica FCM a un topic usando l'API HTTP v1
 * Requisiti:
 * - PHP >= 7.2
 * - OpenSSL abilitato
 * - File JSON dell'account di servizio Firebase
 */



function inviaNotificaFCM( $topic, $titolo, $messaggio) {
    // === CONFIGURAZIONE ===
    $serviceAccountFile = './service-account.json'; // Percorso al file JSON
    $projectId = 'coffeecounter-be'; // Sostituisci con il tuo ID progetto Firebase

    // === CARICA IL FILE JSON ===
    $credentials = json_decode(file_get_contents($serviceAccountFile), true);
    $now = time();
    $header = ['alg' => 'RS256', 'typ' => 'JWT'];
    $claimSet = [
        'iss' => $credentials['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'iat' => $now,
        'exp' => $now + 3600
    ];

    // === CODIFICA BASE64URL ===
    function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // === CREA JWT ===
    $jwtHeader = base64url_encode(json_encode($header));
    $jwtClaim = base64url_encode(json_encode($claimSet));
    $signatureInput = $jwtHeader . '.' . $jwtClaim;

    $privateKey = openssl_pkey_get_private($credentials['private_key']);
    openssl_sign($signatureInput, $signature, $privateKey, 'sha256WithRSAEncryption');
    $jwtSignature = base64url_encode($signature);
    $jwt = $signatureInput . '.' . $jwtSignature;

    // === OTTIENI ACCESS TOKEN ===
    $tokenRequest = [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenRequest));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    $response = curl_exec($ch);
    curl_close($ch);

    $tokenData = json_decode($response, true);
    $accessToken = $tokenData['access_token'] ?? null;

    if (!$accessToken) {
        die("Errore nell'autenticazione: " . $response);
    }

    // === CREA IL MESSAGGIO ===
    $message = [
        'message' => [
            'topic' => $topic,
            'notification' => [
                'title' => $titolo,
                'body' => $messaggio
            ]
        ]
    ];

    // === INVIA LA NOTIFICA ===
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json; UTF-8'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
    $response = curl_exec($ch);
    curl_close($ch);

    // === RISULTATO ===
    echo "Risposta Firebase:\n";
    echo $response;
}
?>
