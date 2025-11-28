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
            <li><a href="index.php">Promocje</a></li>
            <li><a href="index.php">Nowości</a></li>

            <!-- ROZWIJANE MENU KATEGORII -->
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Kategorie ▼</a>
                <ul class="dropdown-menu">
                    <li><a href="?kategoria=RPG">🗡️ RPG</a></li>
                    <li><a href="?kategoria=FPS">🔫 FPS</a></li>
                    <li><a href="?kategoria=Strategia">🎯 Strategia</a></li>
                    <li><a href="?kategoria=Sportowe">⚽ Sportowe</a></li>
                    <li><a href="?kategoria=Przygodowe">🏔️ Przygodowe</a></li>
                    <li><a href="?kategoria=MMO">🌐 MMO</a></li>
                </ul>
            </li>
        </ul>

        <!-- PASEK WYSZUKIWANIA -->
        <div class="search-container">
            <form method="GET" action="index.php" class="search-form">
                <input type="text" name="search" placeholder="Szukaj gry..." value="<?= htmlspecialchars($search) ?>">
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
    <form method="GET" action="index.php">
        <input type="text" name="search" placeholder="Szukaj gry..." value="<?= htmlspecialchars($search) ?>">

        <select name="platforma">
            <option value="">Platforma</option>
            <option value="PC" <?= $platforma === "PC" ? "selected" : "" ?>>PC</option>
            <option value="PS5" <?= $platforma === "PS5" ? "selected" : "" ?>>PS5</option>
            <option value="Xbox" <?= $platforma === "Xbox" ? "selected" : "" ?>>Xbox</option>
        </select>

        <select name="wydawca">
            <option value="">Wydawca</option>
            <option value="EA" <?= $wydawca === "EA" ? "selected" : "" ?>>EA</option>
            <option value="Ubisoft" <?= $wydawca === "Ubisoft" ? "selected" : "" ?>>Ubisoft</option>
            <option value="Rockstar" <?= $wydawca === "Rockstar" ? "selected" : "" ?>>Rockstar</option>
            <option value="Sony" <?= $wydawca === "Sony" ? "selected" : "" ?>>Sony</option>
        </select>

        <input type="number" name="cena_od" placeholder="Cena od" value="<?= htmlspecialchars($cena_od) ?>">
        <input type="number" name="cena_do" placeholder="Cena do" value="<?= htmlspecialchars($cena_do) ?>">

        <button type="submit">Filtruj</button>
        <a href="index.php" class="reset">Reset filtrów</a>
    </form>
</section>

<!-- PRODUKTY -->
<section class="produkty">
<?php if (!empty($produkty)): ?>
    <?php foreach ($produkty as $p): ?>
        <div class="produkt">
            <img src="assets/img/<?= htmlspecialchars($p['zdjecie']) ?>" alt="<?= htmlspecialchars($p['nazwa']) ?>">
            <h3><?= htmlspecialchars($p['nazwa']) ?></h3>
            <p class="platforma"><?= htmlspecialchars($p['platforma']) ?></p>
            <p class="cena"><?= number_format($p['cena'], 2, ',', ' ') ?> zł</p>

            <a href="produkt.php?id=<?= $p['id'] ?>" class="btn-szczegoly">Szczegóły</a>
            <a href="dodaj_do_koszyka.php?id=<?= $p['id'] ?>" class="btn-koszyk">Dodaj do koszyka</a>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p class="no-results">Brak produktów spełniających kryteria.</p>
<?php endif; ?>
</section>

</body>
</html>