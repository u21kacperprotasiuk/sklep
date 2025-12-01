<?php

$host = "sql100.infinityfree.com";
$dbname = "if0_40567490_sklep";
$user = "if0_40567490";
$pass = "CSJdRro3Ur";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => false
        ]
    );
} catch (PDOException $e) {
    exit("Błąd połączenia z bazą: " . $e->getMessage());
}
