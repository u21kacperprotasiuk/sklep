<?php
session_start();
require_once "../includes/db.php";
require_once "auth.php";

$komunikat = "";
$typ_komunikatu = "";

// USUWANIE DOSTAWY
if (isset($_GET['usun'])) {
    $id = (int)$_GET['usun'];
    try {
        $stmt = $pdo->prepare("DELETE FROM dostawy WHERE id = ?");
        $stmt->execute([$id]);
        $komunikat = "Dostawa została usunięta.";
        $typ_komunikatu = "success";
    } catch (PDOException $e) {
        $komunikat = "Nie można usunąć dostawy - może być przypisana do zamówień.";
        $typ_komunikatu = "error";
    }
}

// DODAWANIE/EDYCJA DOSTAWY
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nazwa = trim($_POST['nazwa']);
    $koszt = (float)$_POST['koszt'];
    
    try {
        if ($id) {
            // EDYCJA
            $stmt = $pdo->prepare("UPDATE dostawy SET nazwa = ?, koszt = ? WHERE id = ?");
            $stmt->execute([$nazwa, $koszt, $id]);
            $komunikat = "Dostawa została zaktualizowana.";
        } else {
            // DODAWANIE
            $stmt = $pdo->prepare("INSERT INTO dostawy (nazwa, koszt) VALUES (?, ?)");
            $stmt->execute([$nazwa, $koszt]);
            $komunikat = "Dostawa została dodana.";
        }
        $typ_komunikatu = "success";
    } catch (PDOException $e) {
        $komunikat = "Błąd: " . $e->getMessage();
        $typ_komunikatu = "error";
    }
}

// POBRANIE DOSTAWY DO EDYCJI
$edytowana_dostawa = null;
if (isset($_GET['edytuj'])) {
    $id = (int)$_GET['edytuj'];
    $stmt = $pdo->prepare("SELECT * FROM dostawy WHERE id = ?");
    $stmt->execute([$id]);
    $edytowana_dostawa = $stmt->fetch();
}

// LISTA DOSTAW Z LICZBĄ ZAMÓWIEŃ
$stmt = $pdo->query("
    SELECT d.*, COUNT(z.id) as liczba_zamowien 
    FROM dostawy d 
    LEFT JOIN zamowienia z ON d.id = z.dostawa_id 
    GROUP BY d.id 
    ORDER BY d.koszt
");
$dostawy = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zarządzanie Dostawami - Admin Panel</title>
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
            <a href="index.php" class="admin-nav-item">📊 Dashboard</a>
            <a href="produkty.php" class="admin-nav-item">🎮 Produkty</a>
            <a href="kategorie.php" class="admin-nav-item">📁 Kategorie</a>
            <a href="zamowienia.php" class="admin-nav-item">📦 Zamówienia</a>
            <a href="uzytkownicy.php" class="admin-nav-item">👥 Użytkownicy</a>
            <a href="dostawy.php" class="admin-nav-item active">🚚 Dostawy</a>
            <a href="../index.php" class="admin-nav-item" style="margin-top: auto;">🏠 Powrót do sklepu</a>
            <a href="../logout.php" class="admin-nav-item logout">🚪 Wyloguj</a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="admin-main">
        <header class="admin-header">
            <h1>Dostawy</h1>
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

            <!-- FORMULARZ DODAWANIA/EDYCJI -->
            <div class="section-card">
                <h2><?= $edytowana_dostawa ? '✏️ Edytuj metodę dostawy' : '➕ Dodaj metodę dostawy' ?></h2>
                
                <form method="POST" class="admin-form">
                    <?php if ($edytowana_dostawa): ?>
                        <input type="hidden" name="id" value="<?= $edytowana_dostawa['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Nazwa dostawy *</label>
                        <input type="text" name="nazwa" required 
                               value="<?= htmlspecialchars($edytowana_dostawa['nazwa'] ?? '') ?>"
                               placeholder="np. Kurier DPD, Paczkomat InPost">
                    </div>
                    
                    <div class="form-group">
                        <label>Koszt (zł) *</label>
                        <input type="number" step="0.01" name="koszt" required 
                               value="<?= $edytowana_dostawa['koszt'] ?? '0.00' ?>"
                               placeholder="0.00">
                        <small style="color: #888;">Wpisz 0.00 dla darmowej dostawy</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <?= $edytowana_dostawa ? '💾 Zapisz zmiany' : '➕ Dodaj dostawę' ?>
                        </button>
                        <?php if ($edytowana_dostawa): ?>
                            <a href="dostawy.php" class="btn-danger">❌ Anuluj</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- LISTA DOSTAW -->
            <div class="section-card">
                <h2>Lista dostaw (<?= count($dostawy) ?>)</h2>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nazwa</th>
                            <th>Koszt</th>
                            <th>Liczba zamówień</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dostawy as $d): ?>
                        <tr>
                            <td>#<?= $d['id'] ?></td>
                            <td><strong><?= htmlspecialchars($d['nazwa']) ?></strong></td>
                            <td>
                                <?php if ($d['koszt'] == 0): ?>
                                    <span style="color: #00ff88; font-weight: 600;">Darmowa</span>
                                <?php else: ?>
                                    <span style="color: #00d9ff; font-weight: 600;">
                                        <?= number_format($d['koszt'], 2, ',', ' ') ?> zł
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="color: #00d9ff; font-weight: 600;">
                                    <?= $d['liczba_zamowien'] ?> zamówień
                                </span>
                            </td>
                            <td>
                                <a href="dostawy.php?edytuj=<?= $d['id'] ?>" class="btn-small">✏️ Edytuj</a>
                                <?php if ($d['liczba_zamowien'] == 0): ?>
                                    <a href="dostawy.php?usun=<?= $d['id'] ?>" 
                                       class="btn-danger"
                                       onclick="return confirm('Czy na pewno chcesz usunąć tę metodę dostawy?')">
                                        🗑️ Usuń
                                    </a>
                                <?php else: ?>
                                    <span style="color: #888; font-size: 12px;">
                                        (nie można usunąć - zawiera zamówienia)
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

</body>
</html>