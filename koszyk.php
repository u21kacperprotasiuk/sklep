<?php
session_start();
require_once "includes/db.php";

$koszyk = $_SESSION['koszyk'] ?? [];
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Koszyk</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<h1>Twój koszyk</h1>

<?php if (!$koszyk): ?>
<p>Koszyk jest pusty.</p>

<?php else: ?>

<table>
<tr><th>Produkt</th><th>Ilość</th><th>Cena</th><th>Usuń</th></tr>

<?php foreach ($koszyk as $id => $ilosc):
    $stmt = $pdo->prepare("SELECT * FROM produkty WHERE id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch();
?>
<tr>
    <td><?= $p['nazwa'] ?></td>
    <td><?= $ilosc ?></td>
    <td><?= $p['cena'] * $ilosc ?> zł</td>
    <td><a href="usun_z_koszyka.php?id=<?= $id ?>">X</a></td>
</tr>
<?php endforeach; ?>

</table>

<a href="opróznij_koszyk.php">Opróżnij koszyk</a>

<?php endif; ?>

</body>
</html>
