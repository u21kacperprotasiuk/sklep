<?php
session_start();

$id = $_GET['id'] ?? null;

if ($id && isset($_SESSION['koszyk'][$id])) {
    // Usuń produkt z koszyka
    unset($_SESSION['koszyk'][$id]);
}

// Przekieruj z powrotem do koszyka
header("Location: koszyk.php");
exit;
?>