<?php
session_start();
require_once "../includes/db.php";
require_once "auth.php";

// Pobierz statystyki
$stmt = $pdo->query("SELECT COUNT(*) as total FROM produkty");
$total_produkty = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM uzytkownicy WHERE rola = 'user'");
$total_uzytkownicy = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM zamowienia");
$total_zamowienia = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT SUM(suma) as total FROM zamowienia");
$total_przychod = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) as total FROM kategorie");
$total_kategorie = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel Administracyjny - GameStore</title>
<link rel="stylesheet" href="../assets/css/admin_style.css">
</head>
<body>

<div class="admin-container">
    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
        <div class="admin-logo">
            <h2>🎮 Admin Panel</h2>
        </div>
        
        <nav class="admin-nav">
            <a href="index.php" class="admin-nav-item active">
                📊 Dashboard
            </a>
            <a href="produkty.php" class="admin-nav-item">
                🎮 Produkty
            </a>
            <a href="kategorie.php" class="admin-nav-item">
                📁 Kategorie
            </a>
            <a href="zamowienia.php" class="admin-nav-item">
                📦 Zamówienia
            </a>
            <a href="uzytkownicy.php" class="admin-nav-item">
                👥 Użytkownicy
            </a>
            <a href="dostawy.php" class="admin-nav-item">
                🚚 Dostawy
            </a>
            <a href="../index.php" class="admin-nav-item" style="margin-top: auto;">
                🏠 Powrót do sklepu
            </a>
            <a href="../logout.php" class="admin-nav-item logout">
                🚪 Wyloguj
            </a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="admin-main">
        <header class="admin-header">
            <h1>Dashboard</h1>
            <div class="admin-user">
                Zalogowany jako: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
            </div>
        </header>

        <div class="admin-content">
            <!-- STATYSTYKI -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">🎮</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $total_produkty ?></div>
                        <div class="stat-label">Produkty</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $total_uzytkownicy ?></div>
                        <div class="stat-label">Użytkownicy</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $total_zamowienia ?></div>
                        <div class="stat-label">Zamówienia</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= number_format($total_przychod, 2, ',', ' ') ?> zł</div>
                        <div class="stat-label">Przychód</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📁</div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $total_kategorie ?></div>
                        <div class="stat-label">Kategorie</div>
                    </div>
                </div>
            </div>

            <!-- OSTATNIE ZAMÓWIENIA -->
            <div class="section-card">
                <h2>Ostatnie zamówienia</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Użytkownik</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Suma</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("
                            SELECT z.*, u.pelna_nazwa 
                            FROM zamowienia z
                            JOIN uzytkownicy u ON z.uzytkownik_id = u.id
                            ORDER BY z.data_zamowienia DESC
                            LIMIT 10
                        ");
                        $zamowienia = $stmt->fetchAll();
                        
                        foreach ($zamowienia as $z):
                        ?>
                        <tr>
                            <td>#<?= $z['id'] ?></td>
                            <td><?= htmlspecialchars($z['pelna_nazwa']) ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($z['data_zamowienia'])) ?></td>
                            <td>
                                <span class="status-badge status-<?= $z['status'] ?>">
                                    <?= htmlspecialchars($z['status']) ?>
                                </span>
                            </td>
                            <td><?= number_format($z['suma'], 2, ',', ' ') ?> zł</td>
                            <td>
                                <a href="zamowienia.php?id=<?= $z['id'] ?>" class="btn-small">Szczegóły</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- PRODUKTY Z NISKIM STANEM -->
            <div class="section-card">
                <h2>Produkty z niskim stanem magazynowym</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nazwa</th>
                            <th>Platforma</th>
                            <th>Stan</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("
                            SELECT * FROM produkty 
                            WHERE ilosc_stan < 50 AND ilosc_stan > 0
                            ORDER BY ilosc_stan ASC
                            LIMIT 10
                        ");
                        $produkty_niski_stan = $stmt->fetchAll();
                        
                        if (empty($produkty_niski_stan)):
                        ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #00ff88;">
                                ✓ Wszystkie produkty mają wystarczający stan magazynowy
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($produkty_niski_stan as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['nazwa']) ?></td>
                                <td><?= htmlspecialchars($p['platforma']) ?></td>
                                <td>
                                    <span class="stock-warning"><?= $p['ilosc_stan'] ?> szt.</span>
                                </td>
                                <td>
                                    <a href="produkty.php?edytuj=<?= $p['id'] ?>" class="btn-small">Edytuj</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

</body>
</html>