<?php
/**
 * USER DATA API
 * 
 * Gestisce caricamento e salvataggio dati utente
 * (config, canvas, history)
 * 
 * === RICHIESTE ===
 * 
 * GET: Carica dati
 * ?type=config|canvas|history
 * 
 * POST: Salva dati
 * {
 *     "type": "config|canvas|history",
 *     "data": {...}
 * }
 * 
 * === RISPOSTA ===
 * {
 *     "success": true,
 *     "data": {...}
 * }
 */

require_once 'google_config.php';

session_name(SESSION_NAME);
session_start();

header('Content-Type: application/json');

// Verifica autenticazione
if (!isset($_SESSION['user']) || $_SESSION['user']['logged_in'] !== true) {
    echo json_encode([
        'success' => false,
        'error' => 'Non autenticato'
    ], JSON_PRETTY_PRINT);
    exit;
}

$googleId = $_SESSION['user']['google_id'];
$userFolder = DATA_FOLDER . '/user_' . $googleId;

// Assicurati che la cartella utente esista
if (!file_exists($userFolder)) {
    mkdir($userFolder, 0755, true);
    file_put_contents($userFolder . '/config.json', json_encode(['apiKey' => ''], JSON_PRETTY_PRINT));
    file_put_contents($userFolder . '/canvas.json', json_encode(['cards' => [], 'connections' => []], JSON_PRETTY_PRINT));
    file_put_contents($userFolder . '/history.json', json_encode([], JSON_PRETTY_PRINT));
}

// === GET REQUEST: Carica dati ===
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    
    $validTypes = ['config', 'canvas', 'history'];
    if (!in_array($type, $validTypes)) {
        echo json_encode([
            'success' => false,
            'error' => 'Tipo non valido'
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    $fileName = $userFolder . '/' . $type . '.json';
    
    if (!file_exists($fileName)) {
        // Inizializza file se non esiste
        $defaultData = [];
        if ($type === 'config') {
            $defaultData = ['apiKey' => ''];
        } elseif ($type === 'canvas') {
            $defaultData = ['cards' => [], 'connections' => []];
        }
        file_put_contents($fileName, json_encode($defaultData, JSON_PRETTY_PRINT));
    }
    
    $data = json_decode(file_get_contents($fileName), true);
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ], JSON_PRETTY_PRINT);
    exit;
}

// === POST REQUEST: Salva dati ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $requestData = json_decode($input, true);
    
    if (!isset($requestData['type']) || !isset($requestData['data'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Dati mancanti'
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    $type = $requestData['type'];
    $data = $requestData['data'];
    
    $validTypes = ['config', 'canvas', 'history'];
    if (!in_array($type, $validTypes)) {
        echo json_encode([
            'success' => false,
            'error' => 'Tipo non valido'
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    $fileName = $userFolder . '/' . $type . '.json';
    
    // Salva dati
    $result = file_put_contents($fileName, json_encode($data, JSON_PRETTY_PRINT));
    
    if ($result === false) {
        echo json_encode([
            'success' => false,
            'error' => 'Errore nel salvataggio'
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Dati salvati con successo'
    ], JSON_PRETTY_PRINT);
    exit;
}

// Metodo non supportato
echo json_encode([
    'success' => false,
    'error' => 'Metodo non supportato'
], JSON_PRETTY_PRINT);
?>