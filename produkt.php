<?php
session_start();
require_once "includes/db.php";

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM produkty WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title><?= $p['nazwa'] ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="produkt-szczegoly">
    <img src="assets/img/<?= $p['zdjecie'] ?>">
    <h1><?= $p['nazwa'] ?></h1>
    <p>Platforma: <?= $p['platforma'] ?></p>
    <p>Wydawca: <?= $p['wydawca'] ?></p>
    <p>Cena: <?= $p['cena'] ?> zł</p>

    <a href="dodaj_do_koszyka.php?id=<?= $p['id'] ?>" class="btn-koszyk">
        Dodaj do koszyka
    </a>
</div>

</body>
</html>
