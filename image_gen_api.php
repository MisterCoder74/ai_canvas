<?php
/**
 * IMAGE GENERATOR API - OpenAI DALL-E 3 Integration
 * 
 * Questo file gestisce la generazione di immagini tramite DALL-E 3 di OpenAI.
 * Le immagini vengono salvate localmente sul server nella cartella utente.
 * 
 * === DATI DA INVIARE DAL FRONTEND (POST JSON) ===
 * {
 *     "apiKey": "sk-...",              // La tua OpenAI API Key
 *     "prompt": "Descrizione...",      // Descrizione dell'immagine da generare
 *     "size": "1024x1024"              // Dimensione (solo 1024x1024 per ora)
 * }
 * 
 * === RISPOSTA JSON ===
 * {
 *     "success": true,
 *     "imageUrl": "data/user_XXX/images/img_123.png",  // Path locale
 *     "filename": "img_123.png"
 * }
 * 
 * oppure in caso di errore:
 * {
 *     "success": false,
 *     "error": "Messaggio di errore"
 * }
 */

require_once 'google_config.php';

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

if (!isset($data['prompt']) || empty($data['prompt'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Prompt mancante'
    ], JSON_PRETTY_PRINT);
    exit;
}

$apiKey = $data['apiKey'];
$prompt = $data['prompt'];
$size = isset($data['size']) ? $data['size'] : '1024x1024';

// Prepara la richiesta per OpenAI DALL-E
$requestData = [
    'model' => 'dall-e-3',
    'prompt' => $prompt,
    'n' => 1,
    'size' => $size,
    'quality' => 'standard'
];

// Chiamata API OpenAI
$ch = curl_init('https://api.openai.com/v1/images/generations');
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
    
    if (isset($result['data'][0]['url'])) {
        $openaiImageUrl = $result['data'][0]['url'];
        
        // Download image from OpenAI
        $imageData = @file_get_contents($openaiImageUrl);
        
        if ($imageData === false) {
            echo json_encode([
                'success' => false,
                'error' => 'Impossibile scaricare immagine da OpenAI'
            ], JSON_PRETTY_PRINT);
            exit;
        }
        
        // Get user folder from session
        session_name(SESSION_NAME);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user']['google_id'])) {
            echo json_encode([
                'success' => false,
                'error' => 'Utente non autenticato'
            ], JSON_PRETTY_PRINT);
            exit;
        }
        
        $googleId = $_SESSION['user']['google_id'];
        $userFolder = DATA_FOLDER . '/user_' . $googleId;
        $imagesFolder = $userFolder . '/images';
        
        // Create images folder if doesn't exist
        if (!file_exists($imagesFolder)) {
            mkdir($imagesFolder, 0755, true);
        }
        
        // Save image with unique filename
        $timestamp = time();
        $filename = 'img_' . $timestamp . '.png';
        $filepath = $imagesFolder . '/' . $filename;
        
        file_put_contents($filepath, $imageData);
        
        // Return local path instead of OpenAI URL
        $localPath = 'data/user_' . $googleId . '/images/' . $filename;
        
        echo json_encode([
            'success' => true,
            'imageUrl' => $localPath,
            'filename' => $filename
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