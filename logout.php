<?php
/**
 * LOGOUT
 * 
 * Distrugge la sessione utente e reindirizza alla pagina di login
 */

require_once 'google_config.php';

session_name(SESSION_NAME);
session_start();

// Distruggi tutte le variabili di sessione
$_SESSION = [];

// Elimina il cookie di sessione
if (isset($_COOKIE[SESSION_NAME])) {
    setcookie(SESSION_NAME, '', time() - 3600, '/');
}

// Distruggi la sessione
session_destroy();

// Redirect alla pagina di login
header('Location: index.html');
exit;
?>