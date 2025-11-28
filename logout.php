<?php
session_start();

// Wyczyść wszystkie dane sesji
$_SESSION = array();

// Zniszcz sesję
session_destroy();

// Przekieruj na stronę główną
header("Location: index.php");
exit;
?>