<?php
session_start();
require_once "includes/db.php";

$blad = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $login = trim($_POST["login"]);
    $email = trim($_POST["email"]);
    $pelna_nazwa = trim($_POST["pelna_nazwa"]);
    $haslo = trim($_POST["haslo"]);
    $haslo2 = trim($_POST["haslo2"]);

    if ($login === "" || $email === "" || $pelna_nazwa === "" || $haslo === "" || $haslo2 === "") {
        $blad = "Wypełnij wszystkie pola.";
    } elseif ($haslo !== $haslo2) {
        $blad = "Hasła muszą być takie same.";
    } else {


        $stmt = $pdo->prepare("SELECT id FROM uzytkownicy WHERE login = ?");
        $stmt->execute([$login]);

        if ($stmt->fetch()) {
            $blad = "Taki login już istnieje.";
        } else {


            $stmt = $pdo->prepare("INSERT INTO uzytkownicy(login, haslo, email, pelna_nazwa) VALUES (?, ?, ?, ?)");
            $stmt->execute([$login, $haslo, $email, $pelna_nazwa]);

            $success = "Konto zostało utworzone! Możesz się zalogować.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Rejestracja - GameStore</title>
<link rel="stylesheet" href="assets/css/login_styl.css">
</head>
<body>

<div class="login-container">
    <h2>Rejestracja</h2>

    <?php if ($blad): ?>
        <div class="error-box"><?= $blad ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="error-box" style="background:#00aa00"><?= $success ?></div>
    <?php endif; ?>

    <form action="" method="POST">

        <label>Login:</label>
        <input type="text" name="login" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Pełna nazwa:</label>
        <input type="text" name="pelna_nazwa" required>

        <label>Hasło:</label>
        <input type="password" name="haslo" required>

        <label>Powtórz hasło:</label>
        <input type="password" name="haslo2" required>

        <button type="submit">Zarejestruj się</button>
    </form>

    <p>Masz już konto? <a href="login.php">Zaloguj się</a></p>
</div>

</body>
</html>
