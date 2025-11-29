<?php
session_start();

$id = $_GET['id'] ?? null;

if ($id) {
    if (!isset($_SESSION['koszyk'][$id])) {
        $_SESSION['koszyk'][$id] = 1;
    } else {
        $_SESSION['koszyk'][$id]++;
    }
    
    $ilosc = count($_SESSION['koszyk']);
    
    // Zwróć JSON zamiast przekierowania
    echo json_encode(['success' => true, 'ilosc' => $ilosc]);
} else {
    echo json_encode(['success' => false]);
}
exit;