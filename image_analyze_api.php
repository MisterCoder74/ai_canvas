<?php
/**
 * IMAGE ANALYZER API - OpenAI GPT-4 Vision Integration
 * 
 * Questo file gestisce l'analisi di immagini tramite GPT-4 Vision di OpenAI.
 * 
 * === DATI DA INVIARE DAL FRONTEND (POST JSON) ===
 * {
 *     "apiKey": "sk-...",              // La tua OpenAI API Key
 *     "imageData": "...",              // URL immagine oppure Base64 data (data:image/...)
 *     "prompt": "Analizza..."          // Domanda/richiesta sull'immagine
 * }
 * 
 * === RISPOSTA JSON ===
 * {
 *     "success": true,
 *     "analysis": "Descrizione dell'AI..."
 * }
 * 
 * oppure in caso di errore:
 * {
 *     "success": false,
 *     "error": "Messaggio di errore"
 * }
 */

header('Content-Type: application/json');

// Leggi i dati inviati dal frontend
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validazione dati
if (!isset($data['apiKey']) || empty($data['apiKey'])) {
    echo json_encode([
        'success' => false,
        'error' => 'API Key mancante'
    ], JSON_PRETTY_PRINT);
    exit;
}

if (!isset($data['imageData']) || empty($data['imageData'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Immagine mancante'
    ], JSON_PRETTY_PRINT);
    exit;
}

if (!isset($data['prompt']) || empty($data['prompt'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Prompt mancante'
    ], JSON_PRETTY_PRINT);
    exit;
}

$apiKey = $data['apiKey'];
$imageData = $data['imageData'];
$prompt = $data['prompt'];

// Determina se l'immagine è un URL o Base64
$imageUrl = '';
if (strpos($imageData, 'http') === 0) {
    // È un URL
    $imageUrl = $imageData;
} elseif (strpos($imageData, 'data:image') === 0) {
    // È Base64
    $imageUrl = $imageData;
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Formato immagine non valido (usa URL o Base64)'
    ], JSON_PRETTY_PRINT);
    exit;
}

// Prepara la richiesta per OpenAI GPT-4 Vision
$requestData = [
    'model' => 'gpt-4o-mini',  // Nota: usa il modello corretto per vision
    'messages' => [
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => $prompt
                ],
                [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => $imageUrl
                    ]
                ]
            ]
        ]
    ],
    'max_tokens' => 1000
];

// Chiamata API OpenAI
$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Gestione risposta
if ($httpCode === 200) {
    $result = json_decode($response, true);
    
    if (isset($result['choices'][0]['message']['content'])) {
        echo json_encode([
            'success' => true,
            'analysis' => $result['choices'][0]['message']['content']
        ], JSON_PRETTY_PRINT);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Risposta API non valida'
        ], JSON_PRETTY_PRINT);
    }
} else {
    $error = json_decode($response, true);
    $errorMessage = isset($error['error']['message']) ? $error['error']['message'] : 'Errore API sconosciuto';
    
    echo json_encode([
        'success' => false,
        'error' => $errorMessage
    ], JSON_PRETTY_PRINT);
}
?>