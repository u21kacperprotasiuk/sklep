<?php
session_start();

// Opróżnij koszyk - inicjalizuj jako pustą tablicę zamiast unset
$_SESSION['koszyk'] = [];

header("Location: koszyk.php");
exit;