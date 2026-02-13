<?php
/**
 * TEXT ANALYZER API - OpenAI GPT-4o-mini Integration
 * 
 * Questo file gestisce l'analisi di file di testo e codice tramite GPT-4o-mini.
 * 
 * === DATI DA INVIARE DAL FRONTEND (POST JSON) ===
 * {
 *     "apiKey": "sk-...",              // La tua OpenAI API Key
 *     "fileContent": "...",            // Contenuto del file di testo
 *     "fileName": "example.py",        // Nome del file (per contesto)
 *     "prompt": "Analizza..."          // Richiesta di analisi
 * }
 * 
 * === RISPOSTA JSON ===
 * {
 *     "success": true,
 *     "analysis": "Analisi dettagliata del file..."
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

if (!isset($data['fileContent']) || empty($data['fileContent'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Contenuto file mancante'
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
$fileContent = $data['fileContent'];
$fileName = isset($data['fileName']) ? $data['fileName'] : 'file.txt';
$prompt = $data['prompt'];

// Determina il tipo di file dall'estensione
$fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
$fileTypeHints = [
    'py' => 'Python',
    'php' => 'PHP',
    'js' => 'JavaScript',
    'html' => 'HTML',
    'css' => 'CSS',
    'cpp' => 'C++',
    'c' => 'C',
    'h' => 'C/C++ Header',
    'vb' => 'Visual Basic',
    'json' => 'JSON',
    'xml' => 'XML',
    'sql' => 'SQL',
    'sh' => 'Shell Script',
    'bat' => 'Batch Script',
    'ini' => 'Configuration INI',
    'md' => 'Markdown',
    'txt' => 'Plain Text'
];

$fileType = isset($fileTypeHints[$fileExtension]) ? $fileTypeHints[$fileExtension] : 'Unknown';

// Costruisci il messaggio di sistema con contesto
$systemMessage = "Sei un esperto analista di codice e testi. ";
$systemMessage .= "Analizza accuratamente il contenuto fornito e rispondi in modo dettagliato e professionale. ";
$systemMessage .= "Se trovi errori, bug o problemi, segnalali chiaramente. ";
$systemMessage .= "Se suggerisci miglioramenti, spiega il perché.";

// Costruisci il messaggio utente con file e richiesta
$userMessage = "File: {$fileName}\n";
$userMessage .= "Tipo: {$fileType}\n\n";
$userMessage .= "Contenuto:\n```\n{$fileContent}\n```\n\n";
$userMessage .= "Richiesta: {$prompt}";

// Prepara la richiesta per OpenAI
$requestData = [
    'model' => 'gpt-4o-mini',
    'messages' => [
        [
            'role' => 'system',
            'content' => $systemMessage
        ],
        [
            'role' => 'user',
            'content' => $userMessage
        ]
    ],
    'temperature' => 0.7,
    'max_tokens' => 2500
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