<?php
session_start();
require_once "includes/db.php";

// Sprawdź czy użytkownik jest zalogowany
if (!isset($_SESSION['zalogowany']) || !$_SESSION['zalogowany']) {
    header("Location: login.php");
    exit;
}

$zamowienie_id = $_GET['zamowienie_id'] ?? null;

if (!$zamowienie_id) {
    header("Location: index.php");
    exit;
}

// Pobierz dane zamówienia
$stmt = $pdo->prepare("
    SELECT z.*, u.email, u.pelna_nazwa 
    FROM zamowienia z 
    JOIN uzytkownicy u ON z.uzytkownik_id = u.id 
    WHERE z.id = ? AND z.uzytkownik_id = ?
");
$stmt->execute([$zamowienie_id, $_SESSION['userid']]);
$zamowienie = $stmt->fetch();

if (!$zamowienie) {
    header("Location: index.php");
    exit;
}

// Pobierz pozycje zamówienia
$stmt = $pdo->prepare("
    SELECT pz.*, p.nazwa 
    FROM pozycje_zamowienia pz 
    JOIN produkty p ON pz.produkt_id = p.id 
    WHERE pz.zamowienie_id = ?
");
$stmt->execute([$zamowienie_id]);
$pozycje = $stmt->fetchAll();

// ===========================
// KONFIGURACJA PAYU
// ===========================
// UWAGA: To są dane TESTOWE (Sandbox PayU)
// Zarejestruj się na: https://panel.payu.pl/ i użyj swoich danych

$payu_config = [
    // ŚRODOWISKO TESTOWE (Sandbox)
    'merchant_pos_id' => '501422',  // Twój POS ID (Sandbox: 300746)
    'signature_key' => 'd5e5fafc85916d5f39dcc2fc97ae3f55',  // Twój Signature Key (Sandbox: 13a980d4f851f3d9a1cfc792fb1f5e50)
    'oauth_client_id' => '501422',  // OAuth Client ID
    'oauth_client_secret' => '6f821fef310b382963555b35d751c405',  // OAuth Secret
    
    // URL API
    'api_url' => 'https://secure.snd.payu.com/api/v2_1/orders',  // Sandbox
    // Produkcja: 'https://secure.payu.com/api/v2_1/orders'
    
    'oauth_url' => 'https://secure.snd.payu.com/pl/standard/user/oauth/authorize',  // Sandbox
    // Produkcja: 'https://secure.payu.com/pl/standard/user/oauth/authorize'
];

// Przygotuj produkty dla PayU
$products = [];
foreach ($pozycje as $p) {
    $products[] = [
        'name' => $p['nazwa'],
        'unitPrice' => (int)($p['cena'] * 100), // Kwota w groszach
        'quantity' => $p['ilosc']
    ];
}

// Dodaj koszt dostawy jako osobny produkt
$stmt = $pdo->prepare("SELECT nazwa, koszt FROM dostawy WHERE id = ?");
$stmt->execute([$zamowienie['dostawa_id']]);
$dostawa = $stmt->fetch();

if ($dostawa && $dostawa['koszt'] > 0) {
    $products[] = [
        'name' => 'Dostawa: ' . $dostawa['nazwa'],
        'unitPrice' => (int)($dostawa['koszt'] * 100),
        'quantity' => 1
    ];
}

// Przygotuj dane zamówienia dla PayU
$order_data = [
    'notifyUrl' => 'http://sklepgamestore.free.nf/payu_notify.php',  // URL do powiadomień PayU
    'continueUrl' => 'http://sklepgamestore.free.nf/zamowienie_sukces.php?zamowienie_id=' . $zamowienie_id,  // URL powrotu po płatności
    'customerIp' => $_SERVER['REMOTE_ADDR'],
    'merchantPosId' => $payu_config['oauth_client_id'],
    'description' => 'Zamówienie #' . $zamowienie_id . ' - GameStore',
    'currencyCode' => 'PLN',
    'totalAmount' => (int)($zamowienie['suma'] * 100),  // Kwota w groszach
    'extOrderId' => 'GAMESTORE_' . $zamowienie_id . '_' . time(),  // Unikalny ID zamówienia
    'buyer' => [
        'email' => $zamowienie['email'],
        'firstName' => explode(' ', $zamowienie['pelna_nazwa'])[0],
        'lastName' => explode(' ', $zamowienie['pelna_nazwa'], 2)[1] ?? '',
        'language' => 'pl'
    ],
    'products' => $products
];

// Funkcja do uzyskania tokenu OAuth
function getPayUToken($config) {
    $ch = curl_init($config['oauth_url']);
    
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'client_credentials',
        'client_id' => $config['oauth_client_id'],
        'client_secret' => $config['oauth_client_secret']
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    }
    
    return null;
}

// Funkcja do utworzenia zamówienia w PayU
function createPayUOrder($config, $order_data, $token) {
    $ch = curl_init($config['api_url']);
    
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 201 || $http_code === 302) {
        return json_decode($response, true);
    }
    
    return null;
}

// Spróbuj utworzyć płatność
$token = getPayUToken($payu_config);

if (!$token) {
    $error = "Nie udało się uzyskać tokenu PayU. Sprawdź konfigurację.";
} else {
    $payu_response = createPayUOrder($payu_config, $order_data, $token);
    
    if ($payu_response && isset($payu_response['redirectUri'])) {
        // Zapisz orderId w bazie
        $stmt = $pdo->prepare("UPDATE zamowienia SET status = 'oczekuje_na_platnosc' WHERE id = ?");
        $stmt->execute([$zamowienie_id]);
        
        // Przekieruj do PayU
        header("Location: " . $payu_response['redirectUri']);
        exit;
    } else {
        $error = "Nie udało się utworzyć zamówienia w PayU.";
        if (isset($payu_response['status'])) {
            $error .= " Status: " . $payu_response['status']['statusCode'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Przekierowanie do płatności - GameStore</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
.platnosc-container {
    max-width: 600px;
    margin: 100px auto;
    padding: 50px;
    background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
    border-radius: 12px;
    border: 2px solid #00d9ff;
    text-align: center;
}

.platnosc-container h1 {
    color: #00d9ff;
    font-size: 32px;
    margin-bottom: 20px;
    text-shadow: 0 0 20px rgba(0, 217, 255, 0.3);
}

.loader {
    width: 60px;
    height: 60px;
    border: 5px solid #333;
    border-top-color: #00d9ff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 30px auto;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.error-box {
    background: rgba(255, 68, 68, 0.1);
    border: 2px solid #ff4444;
    padding: 20px;
    border-radius: 10px;
    color: #ff4444;
    margin-top: 20px;
}

.btn-powrot {
    display: inline-block;
    margin-top: 30px;
    padding: 15px 30px;
    background: #333;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    transition: 0.3s;
}

.btn-powrot:hover {
    background: #444;
    color: #00d9ff;
}
</style>
</head>
<body>

<div class="platnosc-container">
    <?php if (isset($error)): ?>
        <h1>❌ Błąd płatności</h1>
        <div class="error-box">
            <?= htmlspecialchars($error) ?>
        </div>
        <p style="color: #888; margin-top: 20px;">
            Jeśli problem się powtarza, skontaktuj się z obsługą klienta.
        </p>
        <a href="zamowienie.php" class="btn-powrot">← Powrót do formularza</a>
    <?php else: ?>
        <h1>💳 Przekierowanie do płatności...</h1>
        <div class="loader"></div>
        <p style="color: #888; margin-top: 20px;">
            Proszę czekać, trwa przekierowanie do bezpiecznej bramki płatności PayU.
        </p>
    <?php endif; ?>
</div>

</body>
</html>