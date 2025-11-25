<?php
session_start();

$id = $_GET['id'];
unset($_SESSION['koszyk'][$id]);

header("Location: koszyk.php");
exit;
