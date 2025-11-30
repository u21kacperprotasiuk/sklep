<?php
session_start();
require_once "../includes/db.php";
require_once "auth.php";

// Załaduj logikę biznesową
require_once "includes/users_logic.php";
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zarządzanie Użytkownikami - Admin Panel</title>
<link rel="stylesheet" href="../assets/css/admin_style.css">
<link rel="stylesheet" href="assets/css/users.css">
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
            <a href="uzytkownicy.php" class="admin-nav-item active">👥 Użytkownicy</a>
            <a href="dostawy.php" class="admin-nav-item">🚚 Dostawy</a>
            <a href="../index.php" class="admin-nav-item" style="margin-top: auto;">🏠 Powrót do sklepu</a>
            <a href="../logout.php" class="admin-nav-item logout">🚪 Wyloguj</a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="admin-main">
        <header class="admin-header">
            <h1>Użytkownicy</h1>
            <div class="admin-user">
                Zalogowany jako: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
            </div>
        </header>

        <div class="admin-content">
            <!-- KOMUNIKATY -->
            <?php if ($komunikat): ?>
                <div class="alert alert-<?= $typ_komunikatu ?>">
                    <?= $komunikat ?>
                </div>
            <?php endif; ?>

            <!-- SZYBKIE STATYSTYKI -->
            <div class="quick-stats">
                <div class="quick-stat">
                    <div class="quick-stat-value"><?= count($uzytkownicy) ?></div>
                    <div class="quick-stat-label">Wszyscy użytkownicy</div>
                </div>
                <div class="quick-stat">
                    <div class="quick-stat-value"><?= $total_users ?></div>
                    <div class="quick-stat-label">Zwykli użytkownicy</div>
                </div>
                <div class="quick-stat">
                    <div class="quick-stat-value"><?= $total_admins ?></div>
                    <div class="quick-stat-label">Administratorzy</div>
                </div>
                <div class="quick-stat">
                    <div class="quick-stat-value"><?= $new_today ?></div>
                    <div class="quick-stat-label">Nowi dzisiaj</div>
                </div>
            </div>

            <!-- FORMULARZ EDYCJI (jeśli edytujemy) -->
            <?php if ($edytowany_uzytkownik): ?>
            <div class="section-card">
                <h2>✏️ Edytuj użytkownika</h2>
                
                <form method="POST" class="admin-form">
                    <input type="hidden" name="id" value="<?= $edytowany_uzytkownik['id'] ?>">
                    
                    <div class="form-group">
                        <label>Login (nie można zmienić)</label>
                        <input type="text" value="<?= htmlspecialchars($edytowany_uzytkownik['login']) ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required 
                               value="<?= htmlspecialchars($edytowany_uzytkownik['email']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Pełna nazwa *</label>
                        <input type="text" name="pelna_nazwa" required 
                               value="<?= htmlspecialchars($edytowany_uzytkownik['pelna_nazwa']) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Nowe hasło (zostaw puste aby nie zmieniać)</label>
                        <input type="password" name="nowe_haslo" 
                               placeholder="Wpisz nowe hasło lub zostaw puste">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="edytuj_uzytkownika" class="btn-primary">
                            💾 Zapisz zmiany
                        </button>
                        <a href="uzytkownicy.php" class="btn-danger">❌ Anuluj</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- LISTA UŻYTKOWNIKÓW -->
            <div class="section-card">
                <div class="toolbar">
                    <div class="toolbar-left">
                        <h2>Lista użytkowników (<?= count($uzytkownicy) ?>)</h2>
                        <button onclick="openAddModal()" class="btn-primary">➕ Dodaj użytkownika</button>
                    </div>
                    <form method="GET" style="display: flex; gap: 10px;">
                        <div class="search-box">
                            <input type="text" name="search" placeholder="Szukaj użytkownika..." 
                                   value="<?= htmlspecialchars($search) ?>">
                            <button type="submit">🔍 Szukaj</button>
                        </div>
                        <select name="rola" onchange="this.form.submit()">
                            <option value="">-- Wszystkie role --</option>
                            <option value="user" <?= $rola_filter == 'user' ? 'selected' : '' ?>>Użytkownicy</option>
                            <option value="admin" <?= $rola_filter == 'admin' ? 'selected' : '' ?>>Administratorzy</option>
                        </select>
                    </form>
                </div>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Użytkownik</th>
                            <th>Email</th>
                            <th>Rola</th>
                            <th>Data rejestracji</th>
                            <th>Zamówienia</th>
                            <th>Suma zakupów</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($uzytkownicy as $u): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <?= strtoupper(substr($u['pelna_nazwa'] ?? $u['login'], 0, 1)) ?>
                                    </div>
                                    <div class="user-details">
                                        <div class="user-name"><?= htmlspecialchars($u['pelna_nazwa'] ?? 'Brak') ?></div>
                                        <div class="user-login">@<?= htmlspecialchars($u['login']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <form method="POST" class="role-select-form">
                                    <input type="hidden" name="uzytkownik_id" value="<?= $u['id'] ?>">
                                    <select name="rola" <?= $u['id'] == $_SESSION['userid'] ? 'disabled' : '' ?>>
                                        <option value="user" <?= $u['rola'] == 'user' ? 'selected' : '' ?>>User</option>
                                        <option value="admin" <?= $u['rola'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                    <?php if ($u['id'] != $_SESSION['userid']): ?>
                                        <button type="submit" name="zmien_role" class="btn-small">💾</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                            <td><?= date('d.m.Y', strtotime($u['data_rejestracji'])) ?></td>
                            <td>
                                <?php if ($u['liczba_zamowien'] > 0): ?>
                                    <strong class="user-stats-highlight"><?= $u['liczba_zamowien'] ?></strong>
                                <?php else: ?>
                                    <span class="user-stats-empty">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['suma_zamowien']): ?>
                                    <strong class="user-stats-positive">
                                        <?= number_format($u['suma_zamowien'], 2, ',', ' ') ?> zł
                                    </strong>
                                <?php else: ?>
                                    <span class="user-stats-empty">0 zł</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="uzytkownicy.php?edytuj=<?= $u['id'] ?>" class="btn-small">✏️ Edytuj</a>
                                <?php if ($u['id'] != $_SESSION['userid'] && $u['id'] != 3): ?>
                                    <a href="uzytkownicy.php?usun=<?= $u['id'] ?>" 
                                       class="btn-danger">
                                        🗑️ Usuń
                                    </a>
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

<!-- MODAL DODAWANIA UŻYTKOWNIKA -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>➕ Dodaj nowego użytkownika</h3>
            <button class="modal-close" onclick="closeAddModal()">✕</button>
        </div>
        
        <form method="POST" class="admin-form">
            <div class="form-group">
                <label>Login *</label>
                <input type="text" name="login" required placeholder="np. jan.kowalski">
            </div>
            
            <div class="form-group">
                <label>Hasło *</label>
                <input type="password" name="haslo" required placeholder="Silne hasło (min 6 znaków)">
            </div>
            
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required placeholder="jan@example.com">
            </div>
            
            <div class="form-group">
                <label>Pełna nazwa *</label>
                <input type="text" name="pelna_nazwa" required placeholder="Jan Kowalski">
            </div>
            
            <div class="form-group">
                <label>Rola *</label>
                <select name="rola" required>
                    <option value="user">Użytkownik</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="dodaj_uzytkownika" class="btn-primary">
                    ➕ Dodaj użytkownika
                </button>
                <button type="button" class="btn-danger" onclick="closeAddModal()">
                    ❌ Anuluj
                </button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript -->
<script src="assets/js/users.js"></script>

</body>
</html>