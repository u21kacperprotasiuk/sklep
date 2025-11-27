<?php
session_start();
require_once "includes/db.php";

$blad = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $login = trim($_POST["login"]);
    $haslo = trim($_POST["haslo"]);

    if ($login === "" || $haslo === "") {
        $blad = "Wypełnij wszystkie pola.";
    } else {

        $stmt = $pdo->prepare("SELECT id, login, haslo, pelna_nazwa FROM uzytkownicy WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {

            if ($haslo === $user["haslo"]) {

                $_SESSION["zalogowany"] = true;
                $_SESSION["userid"] = $user["id"];
                $_SESSION["username"] = $user["pelna_nazwa"];

                header("Location: index.php");
                exit;
            } else {
                $blad = "Nieprawidłowe hasło.";
            }
        } else {
            $blad = "Użytkownik nie istnieje.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Logowanie - GameStore</title>
<link rel="stylesheet" href="assets/css/login_styl.css">
</head>
<body>

<div class="login-container">
    <h2>Logowanie</h2>

    <?php if ($blad): ?>
        <div class="error-box"><?= $blad ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <label>Login:</label>
        <input type="text" name="login" required>

        <label>Hasło:</label>
        <input type="password" name="haslo" required>

        <button type="submit">Zaloguj się</button>
    </form>

    <p>Nie masz konta? <a href="register.php">Zarejestruj się</a></p>
</div>

</body>
</html>
