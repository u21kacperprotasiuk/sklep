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

if ($kategoria !== '') {
    $query .= " AND EXISTS (SELECT 1 FROM kategorie WHERE kategorie.id = produkty.kategoria_id AND kategorie.nazwa = :kategoria)";
    $params[':kategoria'] = $kategoria;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$produkty = $stmt->fetchAll();

// Zwróć tylko HTML produktów
if (!empty($produkty)):
    foreach ($produkty as $p):
?>
    <div class="produkt">
        <a href="produkt.php?id=<?= $p['id'] ?>" class="produkt-link">
            <img src="assets/img/<?= htmlspecialchars($p['zdjecie']) ?>" alt="<?= htmlspecialchars($p['nazwa']) ?>">
            <h3><?= htmlspecialchars($p['nazwa']) ?></h3>
            <p class="platforma"><?= htmlspecialchars($p['platforma']) ?></p>
            <p class="cena"><?= number_format($p['cena'], 2, ',', ' ') ?> zł</p>
        </a>
        <button class="btn-koszyk" data-id="<?= $p['id'] ?>">Dodaj do koszyka</button>
    </div>
<?php
    endforeach;
else:
?>
    <p class="no-results">Brak produktów spełniających kryteria.</p>
<?php
endif;
?>