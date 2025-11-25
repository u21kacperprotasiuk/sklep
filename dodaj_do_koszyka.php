<?php
session_start();

$id = $_GET['id'];

if (!isset($_SESSION['koszyk'][$id])) {
    $_SESSION['koszyk'][$id] = 1;
} else {
    $_SESSION['koszyk'][$id]++;
}

header("Location: koszyk.php");
exit;
