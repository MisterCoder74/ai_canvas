<?php
/**
 * CHATBOT API - OpenAI GPT-4o-mini Integration
 * 
 * Questo file gestisce le richieste di chat al modello GPT-4o-mini di OpenAI.
 * 
 * === DATI DA INVIARE DAL FRONTEND (POST JSON) ===
 * {
 *     "apiKey": "sk-...",              // La tua OpenAI API Key
 *     "messages": [                     // Array di messaggi della conversazione
 *         {
 *             "role": "user",           // Ruolo: "user" o "assistant"
 *             "content": "Messaggio"    // Contenuto del messaggio
 *         }
 *     ],
 *     "file": {                         // (Opzionale) File di testo caricato
 *         "name": "example.js",         // Nome del file
 *         "content": "contenuto..."     // Contenuto del file (testo)
 *     }
 * }
 * 
 * === RISPOSTA JSON ===
 * {
 *     "success": true,
 *     "response": "Risposta dell'AI..."
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

if (!isset($data['messages']) || !is_array($data['messages'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Array messaggi mancante o non valido'
    ], JSON_PRETTY_PRINT);
    exit;
}

$apiKey = $data['apiKey'];
$messages = $data['messages'];
$file = isset($data['file']) ? $data['file'] : null;

// Se c'è un file allegato, aggiungi il suo contenuto al contesto
if ($file && isset($file['content']) && isset($file['name'])) {
    $fileContext = "\n\n[File allegato: {$file['name']}]\n```\n{$file['content']}\n```";
    
    // Aggiungi il contenuto del file all'ultimo messaggio dell'utente
    $lastMessageIndex = count($messages) - 1;
    if ($lastMessageIndex >= 0 && $messages[$lastMessageIndex]['role'] === 'user') {
        $messages[$lastMessageIndex]['content'] .= $fileContext;
    }
}

// Prepara la richiesta per OpenAI
$requestData = [
    'model' => 'gpt-4o-mini',
    'messages' => $messages,
    'temperature' => 0.7,
    'max_tokens' => 2000
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
            'response' => $result['choices'][0]['message']['content']
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