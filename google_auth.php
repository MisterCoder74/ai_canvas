<?php
/**
 * GOOGLE AUTHENTICATION
 * 
 * Genera l'URL di login Google OAuth 2.0
 * e reindirizza l'utente alla pagina di autenticazione Google
 */

require_once 'google_config.php';

// Genera un state token per sicurezza (CSRF protection)
session_start();
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

// Costruisci l'URL di autenticazione Google
$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => GOOGLE_SCOPES,
    'state' => $state,
    'access_type' => 'online',
    'prompt' => 'select_account'
];

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

// Reindirizza a Google
header('Location: ' . $authUrl);
exit;
?>