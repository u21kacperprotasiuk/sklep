<?php
session_start();
require_once "includes/db.php";

$koszyk = $_SESSION['koszyk'] ?? [];
$suma_total = 0;
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Koszyk - GameStore</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
.koszyk-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 40px;
    background: #1a1a1a;
    border-radius: 12px;
    border: 1px solid #333;
}

.koszyk-container h1 {
    color: #00d9ff;
    margin-bottom: 30px;
    text-align: center;
    font-size: 36px;
}

.koszyk-pusty {
    text-align: center;
    padding: 60px;
    color: #888;
    font-size: 18px;
}

.koszyk-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
}

.koszyk-table th {
    background: #0d0d0d;
    color: #00d9ff;
    padding: 15px;
    text-align: left;
    border-bottom: 2px solid #00d9ff;
}

.koszyk-table td {
    padding: 20px 15px;
    border-bottom: 1px solid #333;
    color: #ddd;
}

.koszyk-table tr:hover {
    background: rgba(0, 217, 255, 0.05);
}

.produkt-nazwa {
    font-weight: 600;
    color: #fff;
    font-size: 16px;
}

.produkt-cena {
    color: #00d9ff;
    font-weight: bold;
    font-size: 18px;
}

.btn-usun {
    background: #ff4444;
    color: white;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    transition: 0.3s;
    display: inline-block;
}

.btn-usun:hover {
    background: #cc0000;
    transform: scale(1.05);
}

.koszyk-podsumowanie {
    background: #0d0d0d;
    padding: 25px;
    border-radius: 8px;
    margin-top: 20px;
    border: 1px solid #00d9ff;
}

.suma-total {
    font-size: 28px;
    color: #00d9ff;
    font-weight: bold;
    text-align: right;
}

.koszyk-akcje {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    margin-top: 20px;
}

.btn-oproznij {
    background: #666;
    color: white;
    padding: 12px 25px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: 0.3s;
}

.btn-oproznij:hover {
    background: #888;
}

.btn-zamow {
    background: #00d9ff;
    color: #000;
    padding: 12px 30px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: 0.3s;
}

.btn-zamow:hover {
    background: #00b8dd;
    transform: scale(1.05);
    box-shadow: 0 0 20px rgba(0, 217, 255, 0.4);
}

.btn-powrot {
    display: inline-block;
    margin-bottom: 20px;
    color: #00d9ff;
    text-decoration: none;
    font-weight: 500;
}

.btn-powrot:hover {
    text-decoration: underline;
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
            <?php if (isset($_SESSION['zalogowany']) && $_SESSION['zalogowany']): ?>
                <span class="username">👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="logout.php" class="btn-logout">Wyloguj</a>
            <?php else: ?>
                <a href="login.php" class="btn-login">Logowanie</a>
            <?php endif; ?>

            <a href="koszyk.php" class="cart-link">
                🛒 Koszyk <span class="cart-count">(<?= count($koszyk); ?>)</span>
            </a>
        </div>
    </nav>
</header>

<div class="koszyk-container">
    <a href="index.php" class="btn-powrot">← Powrót do sklepu</a>
    
    <h1>🛒 Twój koszyk</h1>

    <?php if (empty($koszyk)): ?>
        <div class="koszyk-pusty">
            <p>Koszyk jest pusty.</p>
            <p><a href="index.php" style="color: #00d9ff;">Przejdź do sklepu</a></p>
        </div>

    <?php else: ?>

        <table class="koszyk-table">
            <thead>
                <tr>
                    <th>Produkt</th>
                    <th>Platforma</th>
                    <th>Cena jednostkowa</th>
                    <th>Ilość</th>
                    <th>Suma</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($koszyk as $id => $ilosc):
                $stmt = $pdo->prepare("SELECT * FROM produkty WHERE id = ?");
                $stmt->execute([$id]);
                $p = $stmt->fetch();
                
                if ($p):
                    $suma = $p['cena'] * $ilosc;
                    $suma_total += $suma;
            ?>
                <tr>
                    <td class="produkt-nazwa"><?= htmlspecialchars($p['nazwa']) ?></td>
                    <td><?= htmlspecialchars($p['platforma']) ?></td>
                    <td><?= number_format($p['cena'], 2, ',', ' ') ?> zł</td>
                    <td><?= $ilosc ?></td>
                    <td class="produkt-cena"><?= number_format($suma, 2, ',', ' ') ?> zł</td>
                    <td>
                        <a href="usun_z_koszyka.php?id=<?= $id ?>" class="btn-usun">Usuń</a>
                    </td>
                </tr>
            <?php 
                endif;
            endforeach; 
            ?>
            </tbody>
        </table>

        <div class="koszyk-podsumowanie">
            <div class="suma-total">
                Razem do zapłaty: <?= number_format($suma_total, 2, ',', ' ') ?> zł
            </div>
        </div>

        <div class="koszyk-akcje">
            <a href="oproznij_koszyk.php" class="btn-oproznij">Opróżnij koszyk</a>
            <a href="zamowienie.php" class="btn-zamow">Przejdź do płatności</a>
        </div>

    <?php endif; ?>
</div>

</body>
</html>