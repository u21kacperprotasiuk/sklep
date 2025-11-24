<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sklep";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Błąd połączenia: " . $conn->connect_error);

$where = [];
$params = [];
$types = "";

if (!empty($_GET['search'])) {
    $where[] = "nazwa LIKE ?";
    $params[] = "%" . $_GET['search'] . "%";
    $types .= "s";
}

if (!empty($_GET['platforma'])) {
    $where[] = "platforma = ?";
    $params[] = $_GET['platforma'];
    $types .= "s";
}
if (!empty($_GET['wydawca'])) {
    $where[] = "wydawca = ?";
    $params[] = $_GET['wydawca'];
    $types .= "s";
}
if (!empty($_GET['min_price'])) {
    $where[] = "cena >= ?";
    $params[] = $_GET['min_price'];
    $types .= "d";
}
if (!empty($_GET['max_price'])) {
    $where[] = "cena <= ?";
    $params[] = $_GET['max_price'];
    $types .= "d";
}

$sql = "SELECT * FROM produkty";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY data_dodania DESC";

$stmt = $conn->prepare($sql);
if ($where) $stmt->bind_param($types, ...$params);
$stmt->execute();
$produkty = $stmt->get_result();

$platformy = $conn->query("SELECT DISTINCT platforma FROM produkty ORDER BY platforma ASC");
$wydawcy   = $conn->query("SELECT DISTINCT wydawca FROM produkty ORDER BY wydawca ASC");
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>GameZone – Sklep z grami</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
  <div class="logo">🎮 GameZone</div>
  <nav id="menu">
    <a href="index.php" class="active">Strona główna</a>
    <a href="#">Nowości</a>
    <a href="#">Promocje</a>
    <a href="#">Kontakt</a>
    <a href="login.php" class="btn">Logowanie</a>
    <a href="koszyk.php" class="cart">🛒 Koszyk</a>
  </nav>
  <button class="burger" onclick="toggleMenu()">☰</button>
</header>

<section class="slider">
  <div class="slides">
    <div class="slide" style="background-image:url('assets/img/slide1.jpg')"></div>
    <div class="slide" style="background-image:url('assets/img/slide2.jpg')"></div>
    <div class="slide" style="background-image:url('assets/img/slide3.jpg')"></div>
  </div>

  <button class="prev" onclick="changeSlide(-1)">❮</button>
  <button class="next" onclick="changeSlide(1)">❯</button>
</section>

<div class="container">

  <aside class="filters">
    <h3>Filtry</h3>
    <form method="GET">

      <label>Szukaj gry:</label>
      <input type="text" name="search" placeholder="np. GTA V"
             value="<?= $_GET['search'] ?? '' ?>">

      <label>Platforma:</label>
      <select name="platforma">
        <option value="">Dowolna</option>
        <?php while($p = $platformy->fetch_assoc()): ?>
          <option value="<?= $p['platforma'] ?>" <?= (($_GET['platforma'] ?? '') == $p['platforma'])?'selected':'' ?>>
            <?= $p['platforma'] ?>
          </option>
        <?php endwhile; ?>
      </select>

      <label>Wydawca:</label>
      <select name="wydawca">
        <option value="">Dowolny</option>
        <?php while($w = $wydawcy->fetch_assoc()): ?>
          <option value="<?= $w['wydawca'] ?>" <?= (($_GET['wydawca'] ?? '') == $w['wydawca'])?'selected':'' ?>>
            <?= $w['wydawca'] ?>
          </option>
        <?php endwhile; ?>
      </select>

      <label>Cena od (zł):</label>
      <input type="number" step="0.01" name="min_price"
             value="<?= $_GET['min_price'] ?? '' ?>">

      <label>Cena do (zł):</label>
      <input type="number" step="0.01" name="max_price"
             value="<?= $_GET['max_price'] ?? '' ?>">

      <button type="submit" class="filter-btn">Filtruj</button>

      <a href="index.php" class="reset-btn">Resetuj</a>
    </form>
  </aside>

  <section class="products" id="produkty">
    <?php if ($produkty->num_rows > 0): ?>
      <?php while($row = $produkty->fetch_assoc()): ?>
      <div class="product-card">
        <img src="assets/img/<?= $row['zdjecie'] ?>" alt="okładka gry">
        <h3><?= $row['nazwa'] ?></h3>
        <p><?= mb_strimwidth($row['opis'],0,70,'...') ?></p>
        <span class="platform"><?= $row['platforma'] ?></span>
        <div class="price"><?= number_format($row['cena'],2,',',' ') ?> zł</div>
        <a href="produkt.php?id=<?= $row['id'] ?>" class="more">Szczegóły</a>
        <button class="add-cart">Dodaj do koszyka</button>
      </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="no-results">Nie znaleziono gier.</p>
    <?php endif; ?>
  </section>

</div>

<footer>
  © <?= date("Y") ?> GameZone.pl — Twój sklep z grami
</footer>

<script>
function toggleMenu() {
  document.getElementById('menu').classList.toggle('open');
}

let slideIndex = 0;
showSlide(slideIndex);

function changeSlide(n) {
  showSlide(slideIndex += n);
}

function showSlide(n) {
  const slides = document.querySelectorAll(".slide");
  if (n >= slides.length) slideIndex = 0;
  if (n < 0) slideIndex = slides.length - 1;
  
  slides.forEach(slide => slide.style.display = "none");
  slides[slideIndex].style.display = "block";
}

setInterval(() => changeSlide(1), 5000);
</script>

</body>
</html>
