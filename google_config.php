<?php
/**
 * GOOGLE OAUTH CONFIGURATION
 * 
 * Inserisci qui le tue credenziali Google Cloud OAuth 2.0
 * 
 * Come ottenere le credenziali:
 * 1. Vai su https://console.cloud.google.com/
 * 2. Crea un progetto (o seleziona esistente)
 * 3. Vai su "API e servizi" → "Credenziali"
 * 4. Crea "ID client OAuth 2.0"
 * 5. Tipo applicazione: "Applicazione web"
 * 6. Aggiungi URI di reindirizzamento autorizzati:
 *    - http://localhost/your-project/google_callback.php
 *    - https://tuodominio.com/google_callback.php
 * 7. Copia Client ID e Client Secret qui sotto
 */

// === INSERISCI LE TUE CREDENZIALI QUI ===
define('GOOGLE_CLIENT_ID', '');
define('GOOGLE_CLIENT_SECRET', '');

// === REDIRECT URI ===
// Cambia questo URL con il percorso effettivo del tuo google_callback.php
define('GOOGLE_REDIRECT_URI', '');

// === SCOPES ===
// Permessi richiesti (email e profilo base)
define('GOOGLE_SCOPES', 'email profile');

// === DATA FOLDER ===
// Cartella dove salvare i dati utenti (assicurati che sia scrivibile)
define('DATA_FOLDER', __DIR__ . '/data');

// === SESSION CONFIG ===
define('SESSION_NAME', 'ai_tools_session');
define('SESSION_LIFETIME', 86400 * 7); // 7 giorni

?>