<?php
session_start();
require_once "includes/db.php";

$search = $_GET['search'] ?? '';
$platforma = $_GET['platforma'] ?? '';
$wydawca = $_GET['wydawca'] ?? '';
$cena_od = $_GET['cena_od'] ?? '';
$cena_do = $_GET['cena_do'] ?? '';
$kategoria = $_GET['kategoria'] ?? '';

$query = "SELECT * FROM produkty WHERE 1=1";
$params = [];

// FILTRY
if ($search !== '') {
    $query .= " AND nazwa LIKE :search";
    $params[':search'] = "%" . $search . "%";
}

if ($platforma !== '') {
    $query .= " AND platforma = :platforma";
    $params[':platforma'] = $platforma;
}

if ($wydawca !== '') {
    $query .= " AND wydawca = :wydawca";
    $params[':wydawca'] = $wydawca;
}

if ($cena_od !== '') {
    $query .= " AND cena >= :cena_od";
    $params[':cena_od'] = (float)$cena_od;
}

if ($cena_do !== '') {
    $query .= " AND cena <= :cena_do";
    $params[':cena_do'] = (float)$cena_do;
}

// POPRAWKA: używamy kategoria_id i JOIN z tabelą kategorie
if ($kategoria !== '') {
    $query .= " AND EXISTS (SELECT 1 FROM kategorie WHERE kategorie.id = produkty.kategoria_id AND kategorie.nazwa = :kategoria)";
    $params[':kategoria'] = $kategoria;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$produkty = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GameStore - Sklep z grami</title>
<link rel="stylesheet" href="assets/css/style.css">
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
                    <li><a href="#" class="kategoria-link" data-kategoria="RPG">🗡️ RPG</a></li>
                    <li><a href="#" class="kategoria-link" data-kategoria="FPS">🔫 FPS</a></li>
                    <li><a href="#" class="kategoria-link" data-kategoria="Strategia">🎯 Strategia</a></li>
                    <li><a href="#" class="kategoria-link" data-kategoria="Sportowe">⚽ Sportowe</a></li>
                    <li><a href="#" class="kategoria-link" data-kategoria="Przygodowe">🏔️ Przygodowe</a></li>
                    <li><a href="#" class="kategoria-link" data-kategoria="MMO">🌐 MMO</a></li>
                </ul>
            </li>
        </ul>

        <!-- PASEK WYSZUKIWANIA -->
        <div class="search-container">
            <form id="search-form-header" class="search-form">
                <input type="text" id="search-header" name="search" placeholder="Szukaj gry..." value="<?= htmlspecialchars($search) ?>">
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

<!-- SLIDER -->
<div class="slider">
    <div class="slides">
        <img src="assets/img/slide1.jpg" alt="Slider 1">
        <img src="assets/img/slide2.jpg" alt="Slider 2">
        <img src="assets/img/slide3.jpg" alt="Slider 3">
    </div>
</div>

<!-- FILTRY -->
<section class="filters">
    <form id="filter-form">
        <select name="platforma" id="platforma">
            <option value="">-- Brak filtra --</option>
            <option value="PC" <?= $platforma === "PC" ? "selected" : "" ?>>PC</option>
            <option value="PS5" <?= $platforma === "PS5" ? "selected" : "" ?>>PS5</option>
            <option value="Xbox" <?= $platforma === "Xbox" ? "selected" : "" ?>>Xbox</option>
        </select>

        <select name="wydawca" id="wydawca">
            <option value="">-- Brak filtra --</option>
            <option value="CD Projekt RED" <?= $wydawca === "CD Projekt RED" ? "selected" : "" ?>>CD Projekt RED</option>
            <option value="Valve" <?= $wydawca === "Valve" ? "selected" : "" ?>>Valve</option>
            <option value="EA Sports" <?= $wydawca === "EA Sports" ? "selected" : "" ?>>EA Sports</option>
            <option value="Microsoft" <?= $wydawca === "Microsoft" ? "selected" : "" ?>>Microsoft</option>
        </select>

        <input type="number" name="cena_od" id="cena_od" placeholder="Cena od" value="<?= htmlspecialchars($cena_od) ?>">
        <input type="number" name="cena_do" id="cena_do" placeholder="Cena do" value="<?= htmlspecialchars($cena_do) ?>">

        <button type="button" id="reset-filters" class="reset">🔄 Reset filtrów</button>
    </form>
</section>

<!-- PRODUKTY -->
<section class="produkty" id="produkty-container">
<?php if (!empty($produkty)): ?>
    <?php foreach ($produkty as $p): ?>
        <div class="produkt">
            <a href="produkt.php?id=<?= $p['id'] ?>" class="produkt-link">
                <img src="assets/img/<?= htmlspecialchars($p['zdjecie']) ?>" alt="<?= htmlspecialchars($p['nazwa']) ?>">
                <h3><?= htmlspecialchars($p['nazwa']) ?></h3>
                <p class="platforma"><?= htmlspecialchars($p['platforma']) ?></p>
                <p class="cena"><?= number_format($p['cena'], 2, ',', ' ') ?> zł</p>
            </a>
            <button class="btn-koszyk" data-id="<?= $p['id'] ?>">Dodaj do koszyka</button>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p class="no-results">Brak produktów spełniających kryteria.</p>
<?php endif; ?>
</section>

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
// Funkcja do ładowania produktów
function loadProducts(params = {}) {
    const urlParams = new URLSearchParams(params);
    
    fetch(`get_produkty.php?${urlParams.toString()}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('produkty-container').innerHTML = html;
            attachCartButtons();
        })
        .catch(error => {
            console.error('Błąd ładowania produktów:', error);
        });
}

// Funkcja do podłączania przycisków koszyka
function attachCartButtons() {
    const buttons = document.querySelectorAll('.btn-koszyk');
    
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.getAttribute('data-id');
            const originalText = this.textContent;
            
            this.textContent = '✓ Dodano!';
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
                            this.textContent = originalText;
                            this.style.background = '';
                        }, 1500);
                    }
                })
                .catch(error => {
                    console.error('Błąd:', error);
                    this.textContent = originalText;
                    this.style.background = '';
                });
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    attachCartButtons();
    
    // Wyszukiwanie w headerze
    const searchFormHeader = document.getElementById('search-form-header');
    if (searchFormHeader) {
        searchFormHeader.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchValue = document.getElementById('search-header').value;
            loadProducts({ search: searchValue });
        });
    }
    
    // Reset filtrów
    const resetButton = document.getElementById('reset-filters');
    if (resetButton) {
        resetButton.addEventListener('click', function() {
            // Wyczyść wyszukiwanie
            const searchInput = document.getElementById('search-header');
            if (searchInput) searchInput.value = '';
            
            // Wyczyść filtry
            document.getElementById('platforma').value = '';
            document.getElementById('wydawca').value = '';
            document.getElementById('cena_od').value = '';
            document.getElementById('cena_do').value = '';
            
            // Załaduj wszystkie produkty
            loadProducts({});
        });
    }
    
    // Filtry na żywo - zmiana selectów
    document.getElementById('platforma').addEventListener('change', function() {
        const params = {
            platforma: document.getElementById('platforma').value,
            wydawca: document.getElementById('wydawca').value,
            cena_od: document.getElementById('cena_od').value,
            cena_do: document.getElementById('cena_do').value
        };
        loadProducts(params);
    });
    
    document.getElementById('wydawca').addEventListener('change', function() {
        const params = {
            platforma: document.getElementById('platforma').value,
            wydawca: document.getElementById('wydawca').value,
            cena_od: document.getElementById('cena_od').value,
            cena_do: document.getElementById('cena_do').value
        };
        loadProducts(params);
    });
    
    // Filtrowanie po cenie z opóźnieniem
    let priceTimeout;
    document.getElementById('cena_od').addEventListener('input', function() {
        clearTimeout(priceTimeout);
        priceTimeout = setTimeout(function() {
            const params = {
                platforma: document.getElementById('platforma').value,
                wydawca: document.getElementById('wydawca').value,
                cena_od: document.getElementById('cena_od').value,
                cena_do: document.getElementById('cena_do').value
            };
            loadProducts(params);
        }, 800);
    });
    
    document.getElementById('cena_do').addEventListener('input', function() {
        clearTimeout(priceTimeout);
        priceTimeout = setTimeout(function() {
            const params = {
                platforma: document.getElementById('platforma').value,
                wydawca: document.getElementById('wydawca').value,
                cena_od: document.getElementById('cena_od').value,
                cena_do: document.getElementById('cena_do').value
            };
            loadProducts(params);
        }, 800);
    });
    
    // Kliknięcie w kategorię
    const kategorieLinks = document.querySelectorAll('.kategoria-link');
    kategorieLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const kategoria = this.getAttribute('data-kategoria');
            
            // Wyczyść wyszukiwanie
            const searchInput = document.getElementById('search-header');
            if (searchInput) searchInput.value = '';
            
            // Reset filtrów
            document.getElementById('platforma').value = '';
            document.getElementById('wydawca').value = '';
            document.getElementById('cena_od').value = '';
            document.getElementById('cena_do').value = '';
            
            // Załaduj produkty z wybranej kategorii
            loadProducts({ kategoria: kategoria });
        });
    });
});
</script>

</body>
</html>