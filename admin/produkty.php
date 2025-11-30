<?php
session_start();
require_once "../includes/db.php";
require_once "auth.php";

$komunikat = "";
$typ_komunikatu = "";

// USUWANIE PRODUKTU
if (isset($_GET['usun'])) {
    $id = (int)$_GET['usun'];
    try {
        $stmt = $pdo->prepare("DELETE FROM produkty WHERE id = ?");
        $stmt->execute([$id]);
        $komunikat = "Produkt został usunięty.";
        $typ_komunikatu = "success";
    } catch (PDOException $e) {
        $komunikat = "Błąd: " . $e->getMessage();
        $typ_komunikatu = "error";
    }
}

// DODAWANIE/EDYCJA PRODUKTU
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nazwa = trim($_POST['nazwa']);
    $opis = trim($_POST['opis']);
    $cena = (float)$_POST['cena'];
    $pegi = (int)$_POST['pegi'];
    $platforma = trim($_POST['platforma']);
    $wydawca = trim($_POST['wydawca']);
    $wersja = trim($_POST['wersja']);
    $zdjecie = trim($_POST['zdjecie']);
    $ilosc_stan = (int)$_POST['ilosc_stan'];
    $kategoria_id = (int)$_POST['kategoria_id'];
    
    try {
        if ($id) {
            // EDYCJA
            $stmt = $pdo->prepare("
                UPDATE produkty SET 
                nazwa = ?, opis = ?, cena = ?, pegi = ?, platforma = ?, 
                wydawca = ?, wersja = ?, zdjecie = ?, ilosc_stan = ?, kategoria_id = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $nazwa, $opis, $cena, $pegi, $platforma, 
                $wydawca, $wersja, $zdjecie, $ilosc_stan, $kategoria_id, $id
            ]);
            $komunikat = "Produkt został zaktualizowany.";
        } else {
            // DODAWANIE
            $stmt = $pdo->prepare("
                INSERT INTO produkty 
                (nazwa, opis, cena, pegi, platforma, wydawca, wersja, zdjecie, ilosc_stan, kategoria_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $nazwa, $opis, $cena, $pegi, $platforma, 
                $wydawca, $wersja, $zdjecie, $ilosc_stan, $kategoria_id
            ]);
            $komunikat = "Produkt został dodany.";
        }
        $typ_komunikatu = "success";
    } catch (PDOException $e) {
        $komunikat = "Błąd: " . $e->getMessage();
        $typ_komunikatu = "error";
    }
}

// POBRANIE PRODUKTU DO EDYCJI
$edytowany_produkt = null;
if (isset($_GET['edytuj'])) {
    $id = (int)$_GET['edytuj'];
    $stmt = $pdo->prepare("SELECT * FROM produkty WHERE id = ?");
    $stmt->execute([$id]);
    $edytowany_produkt = $stmt->fetch();
}

// LISTA PRODUKTÓW
$search = $_GET['search'] ?? '';
$query = "SELECT p.*, k.nazwa as kategoria_nazwa FROM produkty p 
          LEFT JOIN kategorie k ON p.kategoria_id = k.id WHERE 1=1";
$params = [];

if ($search !== '') {
    $query .= " AND p.nazwa LIKE :search";
    $params[':search'] = "%" . $search . "%";
}

$query .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$produkty = $stmt->fetchAll();

// POBIERZ KATEGORIE
$stmt = $pdo->query("SELECT * FROM kategorie ORDER BY nazwa");
$kategorie = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zarządzanie Produktami - Admin Panel</title>
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
            <a href="produkty.php" class="admin-nav-item active">🎮 Produkty</a>
            <a href="kategorie.php" class="admin-nav-item">📁 Kategorie</a>
            <a href="zamowienia.php" class="admin-nav-item">📦 Zamówienia</a>
            <a href="uzytkownicy.php" class="admin-nav-item">👥 Użytkownicy</a>
            <a href="dostawy.php" class="admin-nav-item">🚚 Dostawy</a>
            <a href="../index.php" class="admin-nav-item" style="margin-top: auto;">🏠 Powrót do sklepu</a>
            <a href="../logout.php" class="admin-nav-item logout">🚪 Wyloguj</a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="admin-main">
        <header class="admin-header">
            <h1><?= $edytowany_produkt ? 'Edytuj Produkt' : 'Produkty' ?></h1>
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
                <h2><?= $edytowany_produkt ? '✏️ Edytuj produkt' : '➕ Dodaj nowy produkt' ?></h2>
                
                <form method="POST" class="admin-form">
                    <?php if ($edytowany_produkt): ?>
                        <input type="hidden" name="id" value="<?= $edytowany_produkt['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Nazwa produktu *</label>
                        <input type="text" name="nazwa" required 
                               value="<?= htmlspecialchars($edytowany_produkt['nazwa'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Opis *</label>
                        <textarea name="opis" required><?= htmlspecialchars($edytowany_produkt['opis'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Cena (zł) *</label>
                        <input type="number" step="0.01" name="cena" required 
                               value="<?= $edytowany_produkt['cena'] ?? '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>PEGI *</label>
                        <select name="pegi" required>
                            <option value="3" <?= ($edytowany_produkt['pegi'] ?? '') == 3 ? 'selected' : '' ?>>3+</option>
                            <option value="7" <?= ($edytowany_produkt['pegi'] ?? '') == 7 ? 'selected' : '' ?>>7+</option>
                            <option value="12" <?= ($edytowany_produkt['pegi'] ?? '') == 12 ? 'selected' : '' ?>>12+</option>
                            <option value="16" <?= ($edytowany_produkt['pegi'] ?? '') == 16 ? 'selected' : '' ?>>16+</option>
                            <option value="18" <?= ($edytowany_produkt['pegi'] ?? '') == 18 ? 'selected' : '' ?>>18+</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Platforma *</label>
                        <select name="platforma" required>
                            <option value="PC" <?= ($edytowany_produkt['platforma'] ?? '') == 'PC' ? 'selected' : '' ?>>PC</option>
                            <option value="PS5" <?= ($edytowany_produkt['platforma'] ?? '') == 'PS5' ? 'selected' : '' ?>>PS5</option>
                            <option value="PS4" <?= ($edytowany_produkt['platforma'] ?? '') == 'PS4' ? 'selected' : '' ?>>PS4</option>
                            <option value="Xbox" <?= ($edytowany_produkt['platforma'] ?? '') == 'Xbox' ? 'selected' : '' ?>>Xbox</option>
                            <option value="Nintendo Switch" <?= ($edytowany_produkt['platforma'] ?? '') == 'Nintendo Switch' ? 'selected' : '' ?>>Nintendo Switch</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Wydawca *</label>
                        <input type="text" name="wydawca" required 
                               value="<?= htmlspecialchars($edytowany_produkt['wydawca'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Wersja *</label>
                        <select name="wersja" required>
                            <option value="klucz cyfrowy" <?= ($edytowany_produkt['wersja'] ?? '') == 'klucz cyfrowy' ? 'selected' : '' ?>>Klucz cyfrowy</option>
                            <option value="płyta" <?= ($edytowany_produkt['wersja'] ?? '') == 'płyta' ? 'selected' : '' ?>>Płyta</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Nazwa pliku zdjęcia *</label>
                        <input type="text" name="zdjecie" required 
                               value="<?= htmlspecialchars($edytowany_produkt['zdjecie'] ?? '') ?>"
                               placeholder="np. cyberpunk2077.jpg">
                    </div>
                    
                    <div class="form-group">
                        <label>Stan magazynowy *</label>
                        <input type="number" name="ilosc_stan" required 
                               value="<?= $edytowany_produkt['ilosc_stan'] ?? '0' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Kategoria *</label>
                        <select name="kategoria_id" required>
                            <option value="">-- Wybierz kategorię --</option>
                            <?php foreach ($kategorie as $k): ?>
                                <option value="<?= $k['id'] ?>" 
                                    <?= ($edytowany_produkt['kategoria_id'] ?? '') == $k['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nazwa']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <?= $edytowany_produkt ? '💾 Zapisz zmiany' : '➕ Dodaj produkt' ?>
                        </button>
                        <?php if ($edytowany_produkt): ?>
                            <a href="produkty.php" class="btn-danger">❌ Anuluj</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- LISTA PRODUKTÓW -->
            <?php if (!$edytowany_produkt): ?>
            <div class="section-card">
                <div class="toolbar">
                    <h2>Lista produktów (<?= count($produkty) ?>)</h2>
                    <div class="search-box">
                        <form method="GET" style="display: flex; gap: 10px;">
                            <input type="text" name="search" placeholder="Szukaj produktu..." 
                                   value="<?= htmlspecialchars($search) ?>">
                            <button type="submit">🔍 Szukaj</button>
                        </form>
                    </div>
                </div>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nazwa</th>
                            <th>Kategoria</th>
                            <th>Platforma</th>
                            <th>Cena</th>
                            <th>Stan</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produkty as $p): ?>
                        <tr>
                            <td>#<?= $p['id'] ?></td>
                            <td><strong><?= htmlspecialchars($p['nazwa']) ?></strong></td>
                            <td><?= htmlspecialchars($p['kategoria_nazwa'] ?? 'Brak') ?></td>
                            <td><?= htmlspecialchars($p['platforma']) ?></td>
                            <td><?= number_format($p['cena'], 2, ',', ' ') ?> zł</td>
                            <td>
                                <?php if ($p['ilosc_stan'] == 0): ?>
                                    <span style="color: #ff4444;">Brak</span>
                                <?php elseif ($p['ilosc_stan'] < 50): ?>
                                    <span class="stock-warning"><?= $p['ilosc_stan'] ?></span>
                                <?php else: ?>
                                    <span style="color: #00ff88;"><?= $p['ilosc_stan'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="produkty.php?edytuj=<?= $p['id'] ?>" class="btn-small">✏️ Edytuj</a>
                                <a href="produkty.php?usun=<?= $p['id'] ?>" 
                                   class="btn-danger"
                                   onclick="return confirm('Czy na pewno chcesz usunąć ten produkt?')">
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