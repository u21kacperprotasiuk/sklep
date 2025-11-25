<?php
session_start();
unset($_SESSION['koszyk']);
header("Location: koszyk.php");
exit;
