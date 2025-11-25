<?php
session_start();
require_once "includes/db.php";

$search = $_GET['search'] ?? '';
$platforma = $_GET['platforma'] ?? '';
$wydawca = $_GET['wydawca'] ?? '';
$cena_od = $_GET['cena_od'] ?? '';
$cena_do = $_GET['cena_do'] ?? '';

$query = "SELECT * FROM produkty WHERE 1=1";
$params = [];

if ($search !== '') {
    $query .= " AND nazwa LIKE ?";
    $params[] = "%" . $search . "%";
}
if ($platforma !== '') {
    $query .= " AND platforma = ?";
    $params[] = $platforma;
}
if ($wydawca !== '') {
    $query .= " AND wydawca = ?";
    $params[] = $wydawca;
}
if ($cena_od !== '') {
    $query .= " AND cena >= ?";
    $params[] = $cena_od;
}
if ($cena_do !== '') {
    $query .= " AND cena <= ?";
    $params[] = $cena_do;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$produkty = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Sklep z grami</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <nav>
        <div class="logo">GameStore</div>
        <ul>
            <li><a href="index.php">Strona główna</a></li>
            <li><a href="koszyk.php">Koszyk (<?php echo count($_SESSION['koszyk'] ?? []); ?>)</a></li>
        </ul>
    </nav>
</header>

<div class="slider">
    <div class="slide"><img src="assets/img/slide1.jpg"></div>
    <div class="slide"><img src="assets/img/slide2.jpg"></div>
    <div class="slide"><img src="assets/img/slide3.jpg"></div>
</div>

<section class="filters">
    <form method="GET" action="index.php">

        <input type="text" name="search" placeholder="Szukaj gry..." 
               value="<?= htmlspecialchars($search) ?>">

        <select name="platforma">
            <option value="">Platforma</option>
            <option value="PC">PC</option>
            <option value="PS5">PS5</option>
            <option value="Xbox">Xbox</option>
        </select>

        <select name="wydawca">
            <option value="">Wydawca</option>
            <option value="EA">EA</option>
            <option value="Ubisoft">Ubisoft</option>
            <option value="Rockstar">Rockstar</option>
            <option value="Sony">Sony</option>
        </select>

        <input type="number" name="cena_od" placeholder="Cena od">
        <input type="number" name="cena_do" placeholder="Cena do">

        <button type="submit">Filtruj</button>
        <a href="index.php" class="reset">Reset filtrów</a>
    </form>
</section>

<section class="produkty">
<?php foreach ($produkty as $p): ?>
    <div class="produkt">
        <img src="assets/img/<?= htmlspecialchars($p['zdjecie']) ?>" alt="">
        <h3><?= $p['nazwa'] ?></h3>
        <p><?= $p['platforma'] ?></p>
        <p><?= $p['cena'] ?> zł</p>
        <a href="produkt.php?id=<?= $p['id'] ?>">Szczegóły</a>
        <a href="dodaj_do_koszyka.php?id=<?= $p['id'] ?>" class="btn-koszyk">Dodaj do koszyka</a>
    </div>
<?php endforeach; ?>
</section>

</body>
</html>
