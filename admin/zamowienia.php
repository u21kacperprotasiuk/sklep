<?php
session_start();
require_once "../includes/db.php";
require_once "auth.php";

$komunikat = "";
$typ_komunikatu = "";

// ZMIANA STATUSU ZAMÓWIENIA
if (isset($_POST['zmien_status'])) {
    $id = (int)$_POST['zamowienie_id'];
    $nowy_status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE zamowienia SET status = ? WHERE id = ?");
        $stmt->execute([$nowy_status, $id]);
        $komunikat = "Status zamówienia został zaktualizowany.";
        $typ_komunikatu = "success";
    } catch (PDOException $e) {
        $komunikat = "Błąd: " . $e->getMessage();
        $typ_komunikatu = "error";
    }
}

// USUWANIE ZAMÓWIENIA
if (isset($_GET['usun'])) {
    $id = (int)$_GET['usun'];
    try {
        $stmt = $pdo->prepare("DELETE FROM zamowienia WHERE id = ?");
        $stmt->execute([$id]);
        $komunikat = "Zamówienie zostało usunięte.";
        $typ_komunikatu = "success";
    } catch (PDOException $e) {
        $komunikat = "Błąd: " . $e->getMessage();
        $typ_komunikatu = "error";
    }
}

// SZCZEGÓŁY ZAMÓWIENIA
$szczegoly = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Pobierz zamówienie
    $stmt = $pdo->prepare("
        SELECT z.*, u.pelna_nazwa, u.email, d.nazwa as dostawa_nazwa, d.koszt as dostawa_koszt
        FROM zamowienia z
        JOIN uzytkownicy u ON z.uzytkownik_id = u.id
        LEFT JOIN dostawy d ON z.dostawa_id = d.id
        WHERE z.id = ?
    ");
    $stmt->execute([$id]);
    $szczegoly = $stmt->fetch();
    
    if ($szczegoly) {
        // Pobierz pozycje zamówienia
        $stmt = $pdo->prepare("
            SELECT pz.*, p.nazwa, p.platforma, p.zdjecie
            FROM pozycje_zamowienia pz
            JOIN produkty p ON pz.produkt_id = p.id
            WHERE pz.zamowienie_id = ?
        ");
        $stmt->execute([$id]);
        $szczegoly['pozycje'] = $stmt->fetchAll();
    }
}

// LISTA ZAMÓWIEŃ
$status_filter = $_GET['status'] ?? '';
$query = "
    SELECT z.*, u.pelna_nazwa, u.email 
    FROM zamowienia z
    JOIN uzytkownicy u ON z.uzytkownik_id = u.id
    WHERE 1=1
";
$params = [];

if ($status_filter !== '') {
    $query .= " AND z.status = :status";
    $params[':status'] = $status_filter;
}

$query .= " ORDER BY z.data_zamowienia DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$zamowienia = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zarządzanie Zamówieniami - Admin Panel</title>
<link rel="stylesheet" href="../assets/css/admin_style.css">
<style>
.order-details {
    display: grid;
    gap: 25px;
}

.order-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.info-block {
    background: #0a0a0a;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #333;
}

.info-block h3 {
    color: #00d9ff;
    margin-bottom: 15px;
    font-size: 16px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #222;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    color: #888;
    font-size: 14px;
}

.info-value {
    color: #eee;
    font-weight: 600;
}

.order-items {
    background: #0a0a0a;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #333;
}

.order-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 15px;
    background: #111;
    border-radius: 8px;
    margin-bottom: 10px;
}

.order-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 6px;
}

.order-item-info {
    flex: 1;
}

.order-item-name {
    font-size: 16px;
    font-weight: 600;
    color: #00d9ff;
    margin-bottom: 5px;
}

.order-item-platform {
    font-size: 14px;
    color: #888;
}

.order-item-price {
    font-size: 18px;
    font-weight: bold;
    color: #fff;
}

.order-summary {
    background: #0a0a0a;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #00d9ff;
    margin-top: 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    font-size: 16px;
}

.summary-row.total {
    border-top: 2px solid #00d9ff;
    margin-top: 10px;
    padding-top: 15px;
    font-size: 20px;
    font-weight: bold;
    color: #00d9ff;
}

.status-form {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-top: 20px;
}

.status-form select {
    padding: 10px 15px;
    background: #0a0a0a;
    border: 2px solid #333;
    color: #eee;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
}

.status-form select:focus {
    border-color: #00d9ff;
    outline: none;
}
</style>
</head>
<body>

<div class="admin-container">
    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
        <div class="admin-logo">
            <h2>🎮 Admin Panel</h2>
        </div>
        
        <nav class="admin-nav">
            <a href="index.php" class="admin-nav-item">📊 Dashboard</a>
            <a href="produkty.php" class="admin-nav-item">🎮 Produkty</a>
            <a href="kategorie.php" class="admin-nav-item">📁 Kategorie</a>
            <a href="zamowienia.php" class="admin-nav-item active">📦 Zamówienia</a>
            <a href="uzytkownicy.php" class="admin-nav-item">👥 Użytkownicy</a>
            <a href="dostawy.php" class="admin-nav-item">🚚 Dostawy</a>
            <a href="../index.php" class="admin-nav-item" style="margin-top: auto;">🏠 Powrót do sklepu</a>
            <a href="../logout.php" class="admin-nav-item logout">🚪 Wyloguj</a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="admin-main">
        <header class="admin-header">
            <h1><?= $szczegoly ? 'Szczegóły zamówienia #' . $szczegoly['id'] : 'Zamówienia' ?></h1>
            <div class="admin-user">
                Zalogowany jako: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
            </div>
        </header>

        <div class="admin-content">
            <?php if ($komunikat): ?>
                <div class="alert alert-<?= $typ_komunikatu ?>">
                    <?= htmlspecialchars($komunikat) ?>
                </div>
            <?php endif; ?>

            <?php if ($szczegoly): ?>
                <!-- SZCZEGÓŁY ZAMÓWIENIA -->
                <div class="section-card">
                    <a href="zamowienia.php" class="btn-small" style="margin-bottom: 20px;">← Powrót do listy</a>
                    
                    <div class="order-details">
                        <div class="order-info-grid">
                            <!-- INFO O KLIENCIE -->
                            <div class="info-block">
                                <h3>👤 Klient</h3>
                                <div class="info-row">
                                    <span class="info-label">Imię i nazwisko:</span>
                                    <span class="info-value"><?= htmlspecialchars($szczegoly['pelna_nazwa']) ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Email:</span>
                                    <span class="info-value"><?= htmlspecialchars($szczegoly['email']) ?></span>
                                </div>
                            </div>
                            
                            <!-- INFO O ZAMÓWIENIU -->
                            <div class="info-block">
                                <h3>📦 Zamówienie</h3>
                                <div class="info-row">
                                    <span class="info-label">Data:</span>
                                    <span class="info-value"><?= date('d.m.Y H:i', strtotime($szczegoly['data_zamowienia'])) ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Status:</span>
                                    <span class="info-value">
                                        <span class="status-badge status-<?= $szczegoly['status'] ?>">
                                            <?= htmlspecialchars($szczegoly['status']) ?>
                                        </span>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- ADRES DOSTAWY -->
                            <div class="info-block">
                                <h3>🚚 Dostawa</h3>
                                <div class="info-row">
                                    <span class="info-label">Sposób:</span>
                                    <span class="info-value"><?= htmlspecialchars($szczegoly['dostawa_nazwa']) ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Adres:</span>
                                    <span class="info-value">
                                        <?= htmlspecialchars($szczegoly['adres_ulica']) ?><br>
                                        <?= htmlspecialchars($szczegoly['adres_kod']) ?> <?= htmlspecialchars($szczegoly['adres_miasto']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- PRODUKTY -->
                        <div class="order-items">
                            <h3 style="color: #00d9ff; margin-bottom: 15px;">Zamówione produkty</h3>
                            <?php foreach ($szczegoly['pozycje'] as $pozycja): ?>
                                <div class="order-item">
                                    <img src="../assets/img/<?= htmlspecialchars($pozycja['zdjecie']) ?>" 
                                         alt="<?= htmlspecialchars($pozycja['nazwa']) ?>">
                                    <div class="order-item-info">
                                        <div class="order-item-name"><?= htmlspecialchars($pozycja['nazwa']) ?></div>
                                        <div class="order-item-platform"><?= htmlspecialchars($pozycja['platforma']) ?></div>
                                        <div style="color: #888; font-size: 14px; margin-top: 5px;">
                                            Ilość: <?= $pozycja['ilosc'] ?> × <?= number_format($pozycja['cena'], 2, ',', ' ') ?> zł
                                        </div>
                                    </div>
                                    <div class="order-item-price">
                                        <?= number_format($pozycja['cena'] * $pozycja['ilosc'], 2, ',', ' ') ?> zł
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- PODSUMOWANIE -->
                        <div class="order-summary">
                            <div class="summary-row">
                                <span>Produkty:</span>
                                <span><?= number_format($szczegoly['suma'] - $szczegoly['dostawa_koszt'], 2, ',', ' ') ?> zł</span>
                            </div>
                            <div class="summary-row">
                                <span>Dostawa:</span>
                                <span><?= number_format($szczegoly['dostawa_koszt'], 2, ',', ' ') ?> zł</span>
                            </div>
                            <div class="summary-row total">
                                <span>RAZEM:</span>
                                <span><?= number_format($szczegoly['suma'], 2, ',', ' ') ?> zł</span>
                            </div>
                        </div>
                        
                        <!-- ZMIANA STATUSU -->
                        <div class="info-block">
                            <h3>🔄 Zmień status zamówienia</h3>
                            <form method="POST" class="status-form">
                                <input type="hidden" name="zamowienie_id" value="<?= $szczegoly['id'] ?>">
                                <select name="status">
                                    <option value="nowe" <?= $szczegoly['status'] == 'nowe' ? 'selected' : '' ?>>Nowe</option>
                                    <option value="w_realizacji" <?= $szczegoly['status'] == 'w_realizacji' ? 'selected' : '' ?>>W realizacji</option>
                                    <option value="wysłane" <?= $szczegoly['status'] == 'wysłane' ? 'selected' : '' ?>>Wysłane</option>
                                    <option value="dostarczone" <?= $szczegoly['status'] == 'dostarczone' ? 'selected' : '' ?>>Dostarczone</option>
                                    <option value="anulowane" <?= $szczegoly['status'] == 'anulowane' ? 'selected' : '' ?>>Anulowane</option>
                                </select>
                                <button type="submit" name="zmien_status" class="btn-primary">💾 Zapisz</button>
                            </form>
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- LISTA ZAMÓWIEŃ -->
                <div class="section-card">
                    <div class="toolbar">
                        <h2>Lista zamówień (<?= count($zamowienia) ?>)</h2>
                        <form method="GET" style="display: flex; gap: 10px;">
                            <select name="status" onchange="this.form.submit()">
                                <option value="">-- Wszystkie statusy --</option>
                                <option value="nowe" <?= $status_filter == 'nowe' ? 'selected' : '' ?>>Nowe</option>
                                <option value="w_realizacji" <?= $status_filter == 'w_realizacji' ? 'selected' : '' ?>>W realizacji</option>
                                <option value="wysłane" <?= $status_filter == 'wysłane' ? 'selected' : '' ?>>Wysłane</option>
                                <option value="dostarczone" <?= $status_filter == 'dostarczone' ? 'selected' : '' ?>>Dostarczone</option>
                                <option value="anulowane" <?= $status_filter == 'anulowane' ? 'selected' : '' ?>>Anulowane</option>
                            </select>
                        </form>
                    </div>
                    
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Klient</th>
                                <th>Data</th>
                                <th>Status</th>
                                <th>Suma</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($zamowienia as $z): ?>
                            <tr>
                                <td>#<?= $z['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($z['pelna_nazwa']) ?></strong><br>
                                    <small style="color: #888;"><?= htmlspecialchars($z['email']) ?></small>
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($z['data_zamowienia'])) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $z['status'] ?>">
                                        <?= htmlspecialchars($z['status']) ?>
                                    </span>
                                </td>
                                <td><?= number_format($z['suma'], 2, ',', ' ') ?> zł</td>
                                <td>
                                    <a href="zamowienia.php?id=<?= $z['id'] ?>" class="btn-small">👁️ Szczegóły</a>
                                    <a href="zamowienia.php?usun=<?= $z['id'] ?>" 
                                       class="btn-danger"
                                       onclick="return confirm('Czy na pewno chcesz usunąć to zamówienie?')">
                                        🗑️ Usuń
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>