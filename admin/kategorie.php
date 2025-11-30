<?php
session_start();
require_once "../includes/db.php";
require_once "auth.php";

$komunikat = "";
$typ_komunikatu = "";

// USUWANIE KATEGORII
if (isset($_GET['usun'])) {
    $id = (int)$_GET['usun'];
    try {
        $stmt = $pdo->prepare("DELETE FROM kategorie WHERE id = ?");
        $stmt->execute([$id]);
        $komunikat = "Kategoria została usunięta.";
        $typ_komunikatu = "success";
    } catch (PDOException $e) {
        $komunikat = "Nie można usunąć kategorii - może być przypisana do produktów.";
        $typ_komunikatu = "error";
    }
}

// DODAWANIE/EDYCJA KATEGORII
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nazwa = trim($_POST['nazwa']);
    $opis = trim($_POST['opis']);
    
    try {
        if ($id) {
            // EDYCJA
            $stmt = $pdo->prepare("UPDATE kategorie SET nazwa = ?, opis = ? WHERE id = ?");
            $stmt->execute([$nazwa, $opis, $id]);
            $komunikat = "Kategoria została zaktualizowana.";
        } else {
            // DODAWANIE
            $stmt = $pdo->prepare("INSERT INTO kategorie (nazwa, opis) VALUES (?, ?)");
            $stmt->execute([$nazwa, $opis]);
            $komunikat = "Kategoria została dodana.";
        }
        $typ_komunikatu = "success";
    } catch (PDOException $e) {
        $komunikat = "Błąd: " . $e->getMessage();
        $typ_komunikatu = "error";
    }
}

// POBRANIE KATEGORII DO EDYCJI
$edytowana_kategoria = null;
if (isset($_GET['edytuj'])) {
    $id = (int)$_GET['edytuj'];
    $stmt = $pdo->prepare("SELECT * FROM kategorie WHERE id = ?");
    $stmt->execute([$id]);
    $edytowana_kategoria = $stmt->fetch();
}

// LISTA KATEGORII Z LICZBĄ PRODUKTÓW
$stmt = $pdo->query("
    SELECT k.*, COUNT(p.id) as liczba_produktow 
    FROM kategorie k 
    LEFT JOIN produkty p ON k.id = p.kategoria_id 
    GROUP BY k.id 
    ORDER BY k.nazwa
");
$kategorie = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zarządzanie Kategoriami - Admin Panel</title>
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
            <a href="kategorie.php" class="admin-nav-item active">📁 Kategorie</a>
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
            <h1>Kategorie</h1>
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
                <h2><?= $edytowana_kategoria ? '✏️ Edytuj kategorię' : '➕ Dodaj nową kategorię' ?></h2>
                
                <form method="POST" class="admin-form">
                    <?php if ($edytowana_kategoria): ?>
                        <input type="hidden" name="id" value="<?= $edytowana_kategoria['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Nazwa kategorii *</label>
                        <input type="text" name="nazwa" required 
                               value="<?= htmlspecialchars($edytowana_kategoria['nazwa'] ?? '') ?>"
                               placeholder="np. RPG, FPS, Strategie">
                    </div>
                    
                    <div class="form-group">
                        <label>Opis</label>
                        <textarea name="opis" 
                                  placeholder="Opcjonalny opis kategorii"><?= htmlspecialchars($edytowana_kategoria['opis'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <?= $edytowana_kategoria ? '💾 Zapisz zmiany' : '➕ Dodaj kategorię' ?>
                        </button>
                        <?php if ($edytowana_kategoria): ?>
                            <a href="kategorie.php" class="btn-danger">❌ Anuluj</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- LISTA KATEGORII -->
            <div class="section-card">
                <h2>Lista kategorii (<?= count($kategorie) ?>)</h2>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nazwa</th>
                            <th>Opis</th>
                            <th>Liczba produktów</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kategorie as $k): ?>
                        <tr>
                            <td>#<?= $k['id'] ?></td>
                            <td><strong><?= htmlspecialchars($k['nazwa']) ?></strong></td>
                            <td><?= htmlspecialchars($k['opis'] ?: 'Brak opisu') ?></td>
                            <td>
                                <span style="color: #00d9ff; font-weight: 600;">
                                    <?= $k['liczba_produktow'] ?> produktów
                                </span>
                            </td>
                            <td>
                                <a href="kategorie.php?edytuj=<?= $k['id'] ?>" class="btn-small">✏️ Edytuj</a>
                                <?php if ($k['liczba_produktow'] == 0): ?>
                                    <a href="kategorie.php?usun=<?= $k['id'] ?>" 
                                       class="btn-danger"
                                       onclick="return confirm('Czy na pewno chcesz usunąć tę kategorię?')">
                                        🗑️ Usuń
                                    </a>
                                <?php else: ?>
                                    <span style="color: #888; font-size: 12px;">
                                        (nie można usunąć - zawiera produkty)
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