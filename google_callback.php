<?php
/**
 * GOOGLE OAUTH CALLBACK
 * 
 * Riceve il codice di autorizzazione da Google,
 * lo scambia con un access token,
 * recupera le informazioni utente,
 * e crea/aggiorna l'utente nel sistema
 */

require_once 'google_config.php';

// Start session temporaneamente solo per verificare state
session_start();
$savedState = isset($_SESSION['oauth_state']) ? $_SESSION['oauth_state'] : null;
session_write_close();

// Verifica state token (CSRF protection)
if (!isset($_GET['state']) || $_GET['state'] !== $savedState) {
    die('Errore: State token non valido. Possibile attacco CSRF.');
}

// Verifica presenza del codice di autorizzazione
if (!isset($_GET['code'])) {
    die('Errore: Codice di autorizzazione mancante.');
}

$authCode = $_GET['code'];

// === STEP 1: Scambia il codice con un access token ===
$tokenUrl = 'https://oauth2.googleapis.com/token';
$tokenData = [
    'code' => $authCode,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code'
];

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
$tokenResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    die('Errore nel recupero del token: ' . $tokenResponse);
}

$tokenResult = json_decode($tokenResponse, true);

if (!isset($tokenResult['access_token'])) {
    die('Errore: Access token non ricevuto.');
}

$accessToken = $tokenResult['access_token'];

// === STEP 2: Recupera informazioni utente da Google ===
$userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';

$ch = curl_init($userInfoUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken
]);
$userInfoResponse = curl_exec($ch);
curl_close($ch);

$userInfo = json_decode($userInfoResponse, true);

if (!isset($userInfo['id']) || !isset($userInfo['email'])) {
    die('Errore: Impossibile recuperare informazioni utente.');
}

// === STEP 3: Crea/aggiorna utente nel sistema ===
$googleId = $userInfo['id'];
$email = $userInfo['email'];
$name = isset($userInfo['name']) ? $userInfo['name'] : 'Utente';
$picture = isset($userInfo['picture']) ? $userInfo['picture'] : '';

// Carica lista utenti
$usersFile = DATA_FOLDER . '/users.json';
if (!file_exists(DATA_FOLDER)) {
    mkdir(DATA_FOLDER, 0755, true);
}

$users = [];
if (file_exists($usersFile)) {
    $users = json_decode(file_get_contents($usersFile), true) ?: [];
}

// Cerca se l'utente esiste già
$userIndex = null;
foreach ($users as $index => $user) {
    if ($user['google_id'] === $googleId) {
        $userIndex = $index;
        break;
    }
}

// Crea o aggiorna utente
if ($userIndex === null) {
    // Nuovo utente
    $newUser = [
        'google_id' => $googleId,
        'email' => $email,
        'name' => $name,
        'picture' => $picture,
        'created_at' => date('Y-m-d H:i:s'),
        'last_login' => date('Y-m-d H:i:s')
    ];
    $users[] = $newUser;
    
    // Crea cartella personale
    $userFolder = DATA_FOLDER . '/user_' . $googleId;
    if (!file_exists($userFolder)) {
        mkdir($userFolder, 0755, true);
        
        // Inizializza file JSON personali
        file_put_contents($userFolder . '/config.json', json_encode(['apiKey' => ''], JSON_PRETTY_PRINT));
        file_put_contents($userFolder . '/canvas.json', json_encode(['cards' => [], 'connections' => []], JSON_PRETTY_PRINT));
        file_put_contents($userFolder . '/history.json', json_encode([], JSON_PRETTY_PRINT));
    }
} else {
    // Aggiorna ultimo login
    $users[$userIndex]['last_login'] = date('Y-m-d H:i:s');
    $users[$userIndex]['name'] = $name;
    $users[$userIndex]['picture'] = $picture;
}

// Salva lista utenti
file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));

// === STEP 4: Crea sessione utente ===
// Imposta parametri PRIMA di iniziare la sessione
session_name(SESSION_NAME);
session_set_cookie_params(SESSION_LIFETIME);

// Ora avvia la sessione
session_start();

// Rigenera ID per sicurezza
session_regenerate_id(true);

$_SESSION['user'] = [
    'google_id' => $googleId,
    'email' => $email,
    'name' => $name,
    'picture' => $picture,
    'logged_in' => true
];

// Redirect all'app principale
header('Location: dashboard.html');
exit;
?>