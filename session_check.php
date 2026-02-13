<?php
/**
 * SESSION CHECK API
 * 
 * Verifica se l'utente è autenticato
 * Ritorna informazioni utente o errore
 * 
 * === RISPOSTA JSON ===
 * {
 *     "authenticated": true,
 *     "user": {
 *         "google_id": "...",
 *         "email": "...",
 *         "name": "...",
 *         "picture": "..."
 *     }
 * }
 * 
 * oppure:
 * {
 *     "authenticated": false
 * }
 */

require_once 'google_config.php';

session_name(SESSION_NAME);
session_start();

header('Content-Type: application/json');

if (isset($_SESSION['user']) && $_SESSION['user']['logged_in'] === true) {
    echo json_encode([
        'authenticated' => true,
        'user' => [
            'google_id' => $_SESSION['user']['google_id'],
            'email' => $_SESSION['user']['email'],
            'name' => $_SESSION['user']['name'],
            'picture' => $_SESSION['user']['picture']
        ]
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        'authenticated' => false
    ], JSON_PRETTY_PRINT);
}
?>