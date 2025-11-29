<?php
session_start();
require_once "includes/db.php";

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM produkty WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($p['nazwa']) ?> - GameStore</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
/* Dodatkowe style dla strony produktu */
.produkt-szczegoly {
    max-width: 1200px;
    margin: 40px auto;
    padding: 40px;
    background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
    border-radius: 12px;
    border: 1px solid #333;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 50px;
    align-items: start;
}

.produkt-galeria {
    position: sticky;
    top: 100px;
}

.produkt-galeria img {
    width: 100%;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 217, 255, 0.2);
    transition: 0.3s;
}

.produkt-galeria img:hover {
    transform: scale(1.02);
    box-shadow: 0 15px 50px rgba(0, 217, 255, 0.3);
}

.produkt-info {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.produkt-info h1 {
    font-size: 36px;
    color: #00d9ff;
    margin-bottom: 10px;
    text-shadow: 0 0 20px rgba(0, 217, 255, 0.3);
}

.produkt-meta {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.meta-tag {
    background: #0a0a0a;
    padding: 8px 16px;
    border-radius: 8px;
    border: 1px solid #333;
    color: #ddd;
    font-size: 14px;
}

.meta-tag strong {
    color: #00d9ff;
    margin-right: 5px;
}

.produkt-opis {
    background: #0a0a0a;
    padding: 25px;
    border-radius: 10px;
    border: 1px solid #333;
    line-height: 1.8;
    color: #ccc;
}

.produkt-cena {
    font-size: 42px;
    font-weight: bold;
    color: #00d9ff;
    margin: 20px 0;
    text-shadow: 0 0 20px rgba(0, 217, 255, 0.4);
}

.produkt-akcje {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn-koszyk-duzy {
    flex: 1;
    padding: 18px 30px;
    background: #00d9ff;
    color: #000;
    border-radius: 10px;
    text-decoration: none;
    font-weight: bold;
    font-size: 18px;
    text-align: center;
    transition: 0.3s;
    border: none;
    cursor: pointer;
}

.btn-koszyk-duzy:hover {
    background: #00b8dd;
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0, 217, 255, 0.4);
}

.btn-powrot {
    padding: 18px 30px;
    background: #333;
    color: #ddd;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    text-align: center;
    transition: 0.3s;
}

.btn-powrot:hover {
    background: #444;
    color: #00d9ff;
}

.info-box {
    background: rgba(0, 217, 255, 0.1);
    border: 1px solid #00d9ff;
    padding: 20px;
    border-radius: 10px;
    margin-top: 20px;
}

.info-box h3 {
    color: #00d9ff;
    margin-bottom: 10px;
    font-size: 18px;
}

.info-box ul {
    list-style: none;
    padding: 0;
}

.info-box li {
    padding: 8px 0;
    border-bottom: 1px solid #333;
    color: #ccc;
}

.info-box li:last-child {
    border-bottom: none;
}

.info-box li:before {
    content: "✓ ";
    color: #00d9ff;
    font-weight: bold;
    margin-right: 8px;
}

@media (max-width: 768px) {
    .produkt-szczegoly {
        grid-template-columns: 1fr;
        gap: 30px;
        padding: 20px;
    }
    
    .produkt-galeria {
        position: static;
    }
    
    .produkt-info h1 {
        font-size: 28px;
    }
    
    .produkt-cena {
        font-size: 32px;
    }
    
    .produkt-akcje {
        flex-direction: column;
    }
}
</style>
</head>
<body>

<header>
    <nav class="navbar">
        <!-- LOGO -->
        <div class="logo">
            <a href="index.php">🎮 GameStore</a>
        </div>

        <!-- MENU NAWIGACYJNE -->
        <ul class="menu">
            <li><a href="index.php">Strona główna</a></li>
            <li><a href="index.php">Promocje</a></li>
            <li><a href="index.php">Nowości</a></li>

            <!-- ROZWIJANE MENU KATEGORII -->
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Kategorie ▼</a>
                <ul class="dropdown-menu">
                    <li><a href="index.php?kategoria=RPG">🗡️ RPG</a></li>
                    <li><a href="index.php?kategoria=FPS">🔫 FPS</a></li>
                    <li><a href="index.php?kategoria=Strategia">🎯 Strategia</a></li>
                    <li><a href="index.php?kategoria=Sportowe">⚽ Sportowe</a></li>
                    <li><a href="index.php?kategoria=Przygodowe">🏔️ Przygodowe</a></li>
                    <li><a href="index.php?kategoria=MMO">🌐 MMO</a></li>
                </ul>
            </li>
        </ul>

        <!-- PASEK WYSZUKIWANIA -->
        <div class="search-container">
            <form method="GET" action="index.php" class="search-form">
                <input type="text" name="search" placeholder="Szukaj gry...">
                <button type="submit">🔍</button>
            </form>
        </div>

        <!-- PRAWE MENU (USER + KOSZYK) -->
        <div class="right-menu">
            <?php if (isset($_SESSION['zalogowany']) && $_SESSION['zalogowany']): ?>
                <span class="username">👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="logout.php" class="btn-logout">Wyloguj</a>
            <?php else: ?>
                <a href="login.php" class="btn-login">Logowanie</a>
            <?php endif; ?>

            <a href="koszyk.php" class="cart-link">
                🛒 Koszyk <span class="cart-count">(<?= count($_SESSION['koszyk'] ?? []); ?>)</span>
            </a>
        </div>
    </nav>
</header>

<div class="produkt-szczegoly">
    <!-- GALERIA PRODUKTU -->
    <div class="produkt-galeria">
        <img src="assets/img/<?= htmlspecialchars($p['zdjecie']) ?>" alt="<?= htmlspecialchars($p['nazwa']) ?>">
    </div>

    <!-- INFORMACJE O PRODUKCIE -->
    <div class="produkt-info">
        <h1><?= htmlspecialchars($p['nazwa']) ?></h1>
        
        <div class="produkt-meta">
            <span class="meta-tag"><strong>Platforma:</strong> <?= htmlspecialchars($p['platforma']) ?></span>
            <span class="meta-tag"><strong>Wydawca:</strong> <?= htmlspecialchars($p['wydawca']) ?></span>
            <span class="meta-tag"><strong>Wersja:</strong> <?= htmlspecialchars($p['wersja']) ?></span>
            <span class="meta-tag"><strong>PEGI:</strong> <?= htmlspecialchars($p['pegi']) ?>+</span>
        </div>

        <div class="produkt-opis">
            <p><?= nl2br(htmlspecialchars($p['opis'])) ?></p>
        </div>

        <div class="produkt-cena">
            <?= number_format($p['cena'], 2, ',', ' ') ?> zł
        </div>

        <div class="info-box">
            <h3>📦 Informacje o dostawie</h3>
            <ul>
                <?php if ($p['wersja'] === 'klucz cyfrowy'): ?>
                    <li>Natychmiastowa dostawa na email</li>
                    <li>Kod aktywacyjny do platformy</li>
                    <li>Bez kosztów wysyłki</li>
                <?php else: ?>
                    <li>Wysyłka w ciągu 24h</li>
                    <li>Darmowa dostawa od 100 zł</li>
                    <li>Możliwość odbioru osobistego</li>
                <?php endif; ?>
                <li>Stan magazynowy: <?= $p['ilosc_stan'] > 0 ? '<strong style="color: #00ff88;">Dostępny</strong>' : '<strong style="color: #ff4444;">Brak w magazynie</strong>' ?></li>
            </ul>
        </div>

        <div class="produkt-akcje">
            <button class="btn-koszyk-duzy" id="add-to-cart" data-id="<?= $p['id'] ?>">
                🛒 Dodaj do koszyka
            </button>
            <a href="index.php" class="btn-powrot">← Powrót do sklepu</a>
        </div>
    </div>
</div>

<footer>
    <div class="footer-content">
        <!-- SEKCJA 1 - O NAS -->
        <div class="footer-section">
            <h3>🎮 GameStore</h3>
            <div class="trustpilot-box">
                <div class="trustpilot-stars">★★★★★</div>
                <p class="trustpilot-text">
                    TrustScore <span class="trustpilot-score">4.7</span> z 788 735 recenzji
                </p>
            </div>
        </div>

        <!-- SEKCJA 2 - INFORMACJE -->
        <div class="footer-section">
            <h3>Informacje</h3>
            <ul>
                <li><a href="#">📋 Warunki korzystania</a></li>
                <li><a href="#">🔒 Polityka prywatności</a></li>
                <li><a href="#">💰 Program afiliacyjny</a></li>
                <li><a href="#">📧 Kontakt do nas</a></li>
            </ul>
        </div>

        <!-- SEKCJA 3 - KONTAKT -->
        <div class="footer-section">
            <h3>Kontakt</h3>
            <ul>
                <li><a href="#">💬 Zainstaluj naszego Bota Discord</a></li>
                <li><a href="#">🎁 Zrealizuj kartę podarunkową</a></li>
                <li><a href="#">🎮 Nowości o grach na PC i konsole</a></li>
            </ul>
        </div>

        <!-- SEKCJA 4 - SOCIAL MEDIA -->
        <div class="footer-section">
            <h3>Dołącz do społeczności</h3>
            <div class="social-links">
                <a href="#" class="social-icon" title="Discord">
                    <img src="assets/img/discord.png" alt="Discord">
                </a>
                <a href="#" class="social-icon" title="X (Twitter)">
                    <img src="assets/img/x.png" alt="X">
                </a>
                <a href="#" class="social-icon" title="Instagram">
                    <img src="assets/img/instagram.png" alt="Instagram">
                </a>
                <a href="#" class="social-icon" title="Facebook">
                    <img src="assets/img/facebook.png" alt="Facebook">
                </a>
            </div>

            <div class="app-buttons">
                <a href="#" class="app-button">
                    <span class="app-button-icon">🍎</span>
                    <div class="app-button-text">
                        <span class="app-button-small">Pobierz z</span>
                        <span class="app-button-large">App Store</span>
                    </div>
                </a>
                <a href="#" class="app-button">
                    <span class="app-button-icon">🤖</span>
                    <div class="app-button-text">
                        <span class="app-button-small">Pobierz z</span>
                        <span class="app-button-large">Google Play</span>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>Copyright © 2025 GameStore - Wszelkie prawa zastrzeżone</p>
        <div class="footer-options">
            <a href="#" class="footer-option">
                <span>📍</span> Polska
            </a>
            <a href="#" class="footer-option">
                <span>🌐</span> Polski
            </a>
            <a href="#" class="footer-option">
                <span>💰</span> PLN
            </a>
        </div>
    </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addButton = document.getElementById('add-to-cart');
    
    if (addButton) {
        addButton.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const originalText = this.innerHTML;
            
            this.innerHTML = '✓ Dodano do koszyka!';
            this.style.background = '#00ff88';
            
            fetch(`dodaj_do_koszyka.php?id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const cartCount = document.querySelector('.cart-count');
                        if (cartCount) {
                            cartCount.textContent = `(${data.ilosc})`;
                        }
                        
                        setTimeout(() => {
                            this.innerHTML = originalText;
                            this.style.background = '';
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Błąd:', error);
                    this.innerHTML = originalText;
                    this.style.background = '';
                });
        });
    }
});
</script>

</body>
</html>