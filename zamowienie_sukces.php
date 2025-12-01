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
    SELECT z.*, d.nazwa as dostawa_nazwa 
    FROM zamowienia z 
    LEFT JOIN dostawy d ON z.dostawa_id = d.id
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
    SELECT pz.*, p.nazwa, p.platforma, p.zdjecie 
    FROM pozycje_zamowienia pz 
    JOIN produkty p ON pz.produkt_id = p.id 
    WHERE pz.zamowienie_id = ?
");
$stmt->execute([$zamowienie_id]);
$pozycje = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zamówienie złożone - GameStore</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
.sukces-container {
    max-width: 900px;
    margin: 60px auto;
    padding: 50px;
    background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
    border-radius: 12px;
    border: 2px solid #00ff88;
    box-shadow: 0 20px 60px rgba(0, 255, 136, 0.2);
}

.sukces-header {
    text-align: center;
    margin-bottom: 50px;
}

.sukces-icon {
    font-size: 80px;
    margin-bottom: 20px;
    animation: bounce 1s ease-in-out;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

.sukces-header h1 {
    color: #00ff88;
    font-size: 36px;
    margin-bottom: 15px;
    text-shadow: 0 0 20px rgba(0, 255, 136, 0.3);
}

.sukces-header p {
    color: #888;
    font-size: 18px;
}

.zamowienie-numer {
    background: rgba(0, 217, 255, 0.1);
    padding: 25px;
    border-radius: 10px;
    border: 2px solid #00d9ff;
    margin-bottom: 40px;
    text-align: center;
}

.zamowienie-numer h3 {
    color: #00d9ff;
    font-size: 18px;
    margin-bottom: 10px;
}

.zamowienie-numer .numer {
    font-size: 32px;
    font-weight: bold;
    color: #fff;
}

.zamowienie-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.info-block {
    background: #0a0a0a;
    padding: 25px;
    border-radius: 10px;
    border: 1px solid #333;
}

.info-block h4 {
    color: #00d9ff;
    margin-bottom: 15px;
    font-size: 16px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-block p {
    color: #ddd;
    margin: 8px 0;
    line-height: 1.6;
}

.info-block strong {
    color: #fff;
}

.produkty-lista {
    background: #0a0a0a;
    padding: 30px;
    border-radius: 10px;
    border: 1px solid #333;
    margin-bottom: 30px;
}

.produkty-lista h3 {
    color: #00d9ff;
    margin-bottom: 25px;
    font-size: 20px;
}

.produkt-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    background: #111;
    border-radius: 8px;
    margin-bottom: 15px;
    border: 1px solid #222;
}

.produkt-item:last-child {
    margin-bottom: 0;
}

.produkt-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 6px;
}

.produkt-details {
    flex: 1;
}

.produkt-nazwa {
    font-size: 16px;
    font-weight: 600;
    color: #fff;
    margin-bottom: 5px;
}

.produkt-platforma {
    color: #888;
    font-size: 14px;
}

.produkt-cena {
    font-size: 18px;
    font-weight: bold;
    color: #00d9ff;
}

.suma-box {
    background: rgba(0, 217, 255, 0.1);
    padding: 25px;
    border-radius: 10px;
    border: 2px solid #00d9ff;
    text-align: right;
    margin-bottom: 40px;
}

.suma-box .label {
    font-size: 18px;
    color: #ddd;
    margin-bottom: 10px;
}

.suma-box .kwota {
    font-size: 36px;
    font-weight: bold;
    color: #00d9ff;
    text-shadow: 0 0 20px rgba(0, 217, 255, 0.3);
}

.akcje-box {
    display: flex;
    gap: 20px;
    justify-content: center;
}

.btn-akcja {
    padding: 18px 40px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 16px;
    transition: 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.btn-sklep {
    background: #00d9ff;
    color: #000;
}

.btn-sklep:hover {
    background: #00b8dd;
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0, 217, 255, 0.4);
}

.btn-zamowienia {
    background: #333;
    color: #ddd;
}

.btn-zamowienia:hover {
    background: #444;
    color: #00d9ff;
}

.info-box-bottom {
    background: rgba(0, 255, 136, 0.1);
    border: 1px solid #00ff88;
    padding: 25px;
    border-radius: 10px;
    margin-top: 40px;
}

.info-box-bottom h4 {
    color: #00ff88;
    margin-bottom: 15px;
    font-size: 18px;
}

.info-box-bottom ul {
    list-style: none;
    padding: 0;
}

.info-box-bottom li {
    color: #ddd;
    padding: 10px 0;
    border-bottom: 1px solid #333;
}

.info-box-bottom li:last-child {
    border-bottom: none;
}

.info-box-bottom li:before {
    content: "✓ ";
    color: #00ff88;
    font-weight: bold;
    margin-right: 10px;
}

@media (max-width: 768px) {
    .sukces-container {
        padding: 30px 20px;
        margin: 20px;
    }
    
    .zamowienie-info {
        grid-template-columns: 1fr;
    }
    
    .akcje-box {
        flex-direction: column;
    }
    
    .btn-akcja {
        width: 100%;
        justify-content: center;
    }
}
</style>
</head>
<body>

<header>
    <nav class="navbar">
        <div class="logo">
            <a href="index.php">🎮 GameStore</a>
        </div>
        
        <ul class="menu">
            <li><a href="index.php">Strona główna</a></li>
        </ul>

        <div class="search-container">
            <form method="GET" action="index.php" class="search-form">
                <input type="text" name="search" placeholder="Szukaj gry...">
                <button type="submit">🔍</button>
            </form>
        </div>

        <div class="right-menu">
            <span class="username">👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="logout.php" class="btn-logout">Wyloguj</a>
            <a href="koszyk.php" class="cart-link">
                🛒 Koszyk <span class="cart-count">(0)</span>
            </a>
        </div>
    </nav>
</header>

<div class="sukces-container">
    <!-- HEADER SUKCESU -->
    <div class="sukces-header">
        <div class="sukces-icon">✅</div>
        <h1>Zamówienie zostało złożone!</h1>
        <p>Dziękujemy za zakupy w GameStore</p>
    </div>

    <!-- NUMER ZAMÓWIENIA -->
    <div class="zamowienie-numer">
        <h3>Numer Twojego zamówienia:</h3>
        <div class="numer">#<?= $zamowienie['id'] ?></div>
    </div>

    <!-- INFORMACJE O ZAMÓWIENIU -->
    <div class="zamowienie-info">
        <div class="info-block">
            <h4>📅 Data zamówienia</h4>
            <p><strong><?= date('d.m.Y H:i', strtotime($zamowienie['data_zamowienia'])) ?></strong></p>
        </div>

        <div class="info-block">
            <h4>📦 Status</h4>
            <p><strong style="color: #00d9ff;">
                <?php
                switch($zamowienie['status']) {
                    case 'nowe': echo 'Nowe'; break;
                    case 'oczekuje_na_platnosc': echo 'Oczekuje na płatność'; break;
                    case 'w_realizacji': echo 'W realizacji'; break;
                    case 'wysłane': echo 'Wysłane'; break;
                    default: echo htmlspecialchars($zamowienie['status']);
                }
                ?>
            </strong></p>
        </div>

        <div class="info-block">
            <h4>🚚 Dostawa</h4>
            <p><strong><?= htmlspecialchars($zamowienie['dostawa_nazwa']) ?></strong></p>
            <p><?= htmlspecialchars($zamowienie['adres_ulica']) ?></p>
            <p><?= htmlspecialchars($zamowienie['adres_kod']) ?> <?= htmlspecialchars($zamowienie['adres_miasto']) ?></p>
        </div>
    </div>

    <!-- LISTA PRODUKTÓW -->
    <div class="produkty-lista">
        <h3>🎮 Zamówione produkty</h3>
        <?php foreach ($pozycje as $p): ?>
            <div class="produkt-item">
                <img src="assets/img/<?= htmlspecialchars($p['zdjecie']) ?>" alt="<?= htmlspecialchars($p['nazwa']) ?>">
                <div class="produkt-details">
                    <div class="produkt-nazwa"><?= htmlspecialchars($p['nazwa']) ?></div>
                    <div class="produkt-platforma"><?= htmlspecialchars($p['platforma']) ?></div>
                    <div style="color: #888; font-size: 14px; margin-top: 5px;">
                        Ilość: <?= $p['ilosc'] ?> × <?= number_format($p['cena'], 2, ',', ' ') ?> zł
                    </div>
                </div>
                <div class="produkt-cena">
                    <?= number_format($p['cena'] * $p['ilosc'], 2, ',', ' ') ?> zł
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- SUMA -->
    <div class="suma-box">
        <div class="label">Łączna kwota zamówienia:</div>
        <div class="kwota"><?= number_format($zamowienie['suma'], 2, ',', ' ') ?> zł</div>
    </div>

    <!-- CO DALEJ? -->
    <div class="info-box-bottom">
        <h4>📧 Co dalej?</h4>
        <ul>
            <li>Na Twój adres email została wysłana wiadomość potwierdzająca zamówienie</li>
            <li>Status zamówienia możesz śledzić w zakładce "Moje zamówienia"</li>
            <li>Otrzymasz powiadomienie email o każdej zmianie statusu</li>
            <li>W przypadku pytań skontaktuj się z naszą obsługą klienta</li>
        </ul>
    </div>

    <!-- AKCJE -->
    <div class="akcje-box">
        <a href="index.php" class="btn-akcja btn-sklep">
            🛍️ Kontynuuj zakupy
        </a>
        <a href="#" class="btn-akcja btn-zamowienia">
            📦 Moje zamówienia
        </a>
    </div>
</div>

<footer>
    <div class="footer-content">
        <div class="footer-section">
            <h3>🎮 GameStore</h3>
            <p style="color: #888; margin-top: 10px;">
                Dziękujemy za zaufanie i zakupy w naszym sklepie!
            </p>
        </div>
    </div>

    <div class="footer-bottom">
        <p>Copyright © 2025 GameStore - Wszelkie prawa zastrzeżone</p>
    </div>
</footer>

</body>
</html>