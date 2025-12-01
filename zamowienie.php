<?php
session_start();
require_once "includes/db.php";

// Sprawdź czy użytkownik jest zalogowany
if (!isset($_SESSION['zalogowany']) || !$_SESSION['zalogowany']) {
    header("Location: login.php");
    exit;
}

// Sprawdź czy koszyk nie jest pusty
$koszyk = $_SESSION['koszyk'] ?? [];
if (empty($koszyk)) {
    header("Location: koszyk.php");
    exit;
}

// Pobierz dane użytkownika
$stmt = $pdo->prepare("SELECT * FROM uzytkownicy WHERE id = ?");
$stmt->execute([$_SESSION['userid']]);
$user = $stmt->fetch();

// Pobierz metody dostawy
$stmt = $pdo->query("SELECT * FROM dostawy ORDER BY koszt");
$dostawy = $stmt->fetchAll();

// Oblicz sumę koszyka
$suma_produkty = 0;
$produkty_w_koszyku = [];
foreach ($koszyk as $id => $ilosc) {
    $stmt = $pdo->prepare("SELECT * FROM produkty WHERE id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    if ($p) {
        $produkty_w_koszyku[] = [
            'id' => $p['id'],
            'nazwa' => $p['nazwa'],
            'cena' => $p['cena'],
            'ilosc' => $ilosc,
            'suma' => $p['cena'] * $ilosc
        ];
        $suma_produkty += $p['cena'] * $ilosc;
    }
}

$komunikat = "";
$typ_komunikatu = "";

// Przetwarzanie formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dostawa_id = (int)$_POST['dostawa_id'];
    $adres_ulica = trim($_POST['adres_ulica']);
    $adres_miasto = trim($_POST['adres_miasto']);
    $adres_kod = trim($_POST['adres_kod']);
    $adres_kraj = trim($_POST['adres_kraj']) ?: 'Polska';

    if (empty($adres_ulica) || empty($adres_miasto) || empty($adres_kod)) {
        $komunikat = "Uzupełnij wszystkie pola adresu dostawy.";
        $typ_komunikatu = "error";
    } else {
        $stmt = $pdo->prepare("SELECT koszt FROM dostawy WHERE id = ?");
        $stmt->execute([$dostawa_id]);
        $dostawa = $stmt->fetch();
        $koszt_dostawy = $dostawa['koszt'] ?? 0;
        $suma_total = $suma_produkty + $koszt_dostawy;

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO zamowienia 
                (uzytkownik_id, suma, dostawa_id, adres_ulica, adres_miasto, adres_kod, adres_kraj, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'nowe')
            ");
            $stmt->execute([
                $_SESSION['userid'],
                $suma_total,
                $dostawa_id,
                $adres_ulica,
                $adres_miasto,
                $adres_kod,
                $adres_kraj
            ]);

            $zamowienie_id = $pdo->lastInsertId();

            foreach ($produkty_w_koszyku as $produkt) {
                $stmt = $pdo->prepare("
                    INSERT INTO pozycje_zamowienia (zamowienie_id, produkt_id, ilosc, cena)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $zamowienie_id,
                    $produkt['id'],
                    $produkt['ilosc'],
                    $produkt['cena']
                ]);

                $stmt = $pdo->prepare("UPDATE produkty SET ilosc_stan = ilosc_stan - ? WHERE id = ?");
                $stmt->execute([$produkt['ilosc'], $produkt['id']]);
            }

            $pdo->commit();
            $_SESSION['koszyk'] = [];

            header("Location: payu_platnosc.php?zamowienie_id=" . $zamowienie_id);
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $komunikat = "Błąd podczas składania zamówienia: " . $e->getMessage();
            $typ_komunikatu = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Składanie zamówienia - GameStore</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
<style>
.zamowienie-container {
    max-width: 1400px;
    margin: 40px auto;
    padding: 40px;
    display: grid;
    grid-template-columns: 1fr 450px;
    gap: 40px;
}

.formularz-section {
    background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
    padding: 35px;
    border-radius: 12px;
    border: 1px solid #333;
}

.formularz-section h2 {
    color: #00d9ff;
    margin-bottom: 30px;
    font-size: 28px;
    text-shadow: 0 0 15px rgba(0, 217, 255, 0.3);
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    color: #00d9ff;
    font-weight: 600;
    margin-bottom: 8px;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 14px 18px;
    background: #0a0a0a;
    border: 2px solid #333;
    color: #eee;
    border-radius: 8px;
    font-size: 15px;
    transition: 0.3s;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #00d9ff;
    outline: none;
    box-shadow: 0 0 15px rgba(0, 217, 255, 0.3);
}

.form-row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 15px;
}

.dostawa-opcje {
    display: grid;
    gap: 15px;
    margin-top: 10px;
}

.dostawa-option {
    background: #0a0a0a;
    padding: 20px;
    border: 2px solid #333;
    border-radius: 10px;
    cursor: pointer;
    transition: 0.3s;
    display: flex;
    align-items: center;
    gap: 15px;
}

.dostawa-option:hover {
    border-color: #00d9ff;
    background: rgba(0, 217, 255, 0.05);
}

.dostawa-option input[type="radio"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.dostawa-option.selected {
    border-color: #00d9ff;
    background: rgba(0, 217, 255, 0.1);
}

.dostawa-info {
    flex: 1;
}

.dostawa-nazwa {
    font-size: 16px;
    font-weight: 600;
    color: #fff;
    margin-bottom: 5px;
}

.dostawa-koszt {
    font-size: 18px;
    font-weight: bold;
    color: #00d9ff;
}

.podsumowanie-section {
    position: sticky;
    top: 100px;
    height: fit-content;
}

.podsumowanie-box {
    background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
    padding: 30px;
    border-radius: 12px;
    border: 2px solid #00d9ff;
    box-shadow: 0 10px 40px rgba(0, 217, 255, 0.2);
}

.podsumowanie-box h3 {
    color: #00d9ff;
    margin-bottom: 25px;
    font-size: 24px;
    text-shadow: 0 0 15px rgba(0, 217, 255, 0.3);
}

.produkt-lista {
    margin-bottom: 25px;
}

.produkt-item {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #333;
    color: #ddd;
}

.produkt-item:last-child {
    border-bottom: none;
}

.produkt-nazwa-podsumowanie {
    flex: 1;
    font-size: 14px;
}

.produkt-cena-podsumowanie {
    font-weight: 600;
    color: #fff;
}

.suma-row {
    display: flex;
    justify-content: space-between;
    padding: 15px 0;
    font-size: 16px;
    color: #ddd;
}

.suma-row.total {
    border-top: 2px solid #00d9ff;
    margin-top: 15px;
    padding-top: 20px;
    font-size: 24px;
    font-weight: bold;
    color: #00d9ff;
}

.btn-zloz-zamowienie {
    width: 100%;
    padding: 18px;
    background: #00d9ff;
    color: #000;
    border: none;
    border-radius: 10px;
    font-size: 18px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
    margin-top: 20px;
}

.btn-zloz-zamowienie:hover {
    background: #00b8dd;
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0, 217, 255, 0.4);
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

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 500;
}

.alert-error {
    background: rgba(255, 68, 68, 0.1);
    border: 1px solid #ff4444;
    color: #ff4444;
}

.platnosc-info {
    background: rgba(0, 217, 255, 0.1);
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #00d9ff;
    margin-top: 20px;
}

.platnosc-info h4 {
    color: #00d9ff;
    margin-bottom: 10px;
}

.platnosc-info p {
    color: #ddd;
    font-size: 14px;
    line-height: 1.6;
}

.payu-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 15px;
    padding: 15px;
    background: #fff;
    border-radius: 8px;
}

.payu-logo img {
    height: 30px;
}

@media (max-width: 1024px) {
    .zamowienie-container {
        grid-template-columns: 1fr;
        padding: 20px;
    }
    
    .podsumowanie-section {
        position: static;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
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
        <span class="username">👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="logout.php" class="btn-logout">Wyloguj</a>
        <a href="koszyk.php" class="cart-link">
            🛒 Koszyk <span class="cart-count">(<?= count($koszyk); ?>)</span>
        </a>
    </div>
</nav>
</header>

<div class="zamowienie-container">
    <div class="formularz-section">
        <a href="koszyk.php" class="btn-powrot">← Powrót do koszyka</a>
        <h2>📋 Składanie zamówienia</h2>

        <?php if ($komunikat): ?>
            <div class="alert alert-<?= $typ_komunikatu ?>">
                <?= htmlspecialchars($komunikat) ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="zamowienie-form">
            <div class="form-group">
                <label>Imię i nazwisko *</label>
                <input type="text" value="<?= htmlspecialchars($user['pelna_nazwa']) ?>" disabled>
            </div>

            <div class="form-group">
                <label>Email *</label>
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
            </div>

            <h3 style="color: #00d9ff; margin: 30px 0 20px;">📍 Adres dostawy</h3>

            <div class="form-group">
                <label>Ulica i numer *</label>
                <input type="text" name="adres_ulica" required placeholder="np. Marszałkowska 12/34">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Miasto *</label>
                    <input type="text" name="adres_miasto" required placeholder="np. Warszawa">
                </div>
                <div class="form-group">
                    <label>Kod pocztowy *</label>
                    <input type="text" name="adres_kod" required placeholder="00-000">
                </div>
            </div>

            <div class="form-group">
                <label>Kraj</label>
                <input type="text" name="adres_kraj" value="Polska">
            </div>

            <h3 style="color: #00d9ff; margin: 30px 0 20px;">🚚 Wybierz sposób dostawy</h3>

            <div class="dostawa-opcje">
                <?php foreach ($dostawy as $index => $d): ?>
                    <label class="dostawa-option" data-koszt="<?= $d['koszt'] ?>">
                        <input type="radio" name="dostawa_id" value="<?= $d['id'] ?>" <?= $index===0?'checked':'' ?> required>
                        <div class="dostawa-info">
                            <div class="dostawa-nazwa"><?= htmlspecialchars($d['nazwa']) ?></div>
                            <div class="dostawa-koszt">
                                <?= $d['koszt']==0 ? 'Darmowa' : number_format($d['koszt'],2,',',' ').' zł' ?>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="platnosc-info">
                <h4>💳 Płatność</h4>
                <p>Po złożeniu zamówienia zostaniesz przekierowany do bezpiecznej bramki płatności PayU, gdzie będziesz mógł dokonać płatności kartą, BLIK-iem lub przelewem bankowym.</p>
                <div class="payu-logo">
                    <span style="color: #000; font-weight: bold; font-size: 18px;">Bezpieczne płatności przez</span>
                    <span style="color: #4fa90a; font-weight: bold; font-size: 24px;">PayU</span>
                </div>
            </div>
        </form>
    </div>

    <div class="podsumowanie-section">
        <div class="podsumowanie-box">
            <h3>🛒 Podsumowanie zamówienia</h3>
            <div class="produkt-lista">
                <?php foreach ($produkty_w_koszyku as $p): ?>
                    <div class="produkt-item">
                        <div class="produkt-nazwa-podsumowanie"><?= htmlspecialchars($p['nazwa']) ?> × <?= $p['ilosc'] ?></div>
                        <div class="produkt-cena-podsumowanie"><?= number_format($p['suma'],2,',',' ') ?> zł</div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="suma-row"><span>Produkty:</span><span id="suma-produkty"><?= number_format($suma_produkty,2,',',' ') ?> zł</span></div>
            <div class="suma-row"><span>Dostawa:</span><span id="suma-dostawa"><?= number_format($dostawy[0]['koszt'],2,',',' ') ?> zł</span></div>
            <div class="suma-row total"><span>Razem:</span><span id="suma-total"><?= number_format($suma_produkty+$dostawy[0]['koszt'],2,',',' ') ?> zł</span></div>

            <button type="submit" form="zamowienie-form" class="btn-zloz-zamowienie">💳 Przejdź do płatności</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const sumaProduktow = <?= $suma_produkty ?>;
    const dostawaOptions = document.querySelectorAll('.dostawa-option');

    dostawaOptions.forEach(option=>{
        option.addEventListener('click', function(){
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            dostawaOptions.forEach(opt=>opt.classList.remove('selected'));
            this.classList.add('selected');
            const kosztDostawy = parseFloat(this.dataset.koszt);
            const sumaTotal = sumaProduktow+kosztDostawy;
            document.getElementById('suma-dostawa').textContent = kosztDostawy===0?'Darmowa':kosztDostawy.toFixed(2).replace('.',',')+' zł';
            document.getElementById('suma-total').textContent = sumaTotal.toFixed(2).replace('.',',')+' zł';
        });
    });

    const checkedOption = document.querySelector('.dostawa-option input[type="radio"]:checked');
    if(checkedOption) checkedOption.closest('.dostawa-option').classList.add('selected');
});
</script>

</body>
</html>
