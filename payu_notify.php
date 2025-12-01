<?php
/**
 * PAYU NOTIFY - Obsługa powiadomień (webhook) od PayU
 * Ten plik jest wywoływany automatycznie przez PayU po zmianie statusu płatności
 */

require_once "includes/db.php";

// Konfiguracja PayU (te same dane co w payu_platnosc.php)
$payu_config = [
    'signature_key' => 'd5e5fafc85916d5f39dcc2fc97ae3f55',  // Twój Signature Key
];

// Pobierz dane z żądania POST
$body = file_get_contents('php://input');
$data = json_decode($body, true);

// Logowanie (opcjonalne - do debugowania)
file_put_contents('payu_log.txt', date('Y-m-d H:i:s') . " - " . $body . "\n", FILE_APPEND);

// Weryfikacja sygnatury (bezpieczeństwo)
$signature = $_SERVER['HTTP_OPENPAYU_SIGNATURE'] ?? '';
$signature_parts = explode(';', $signature);
$signature_array = [];

foreach ($signature_parts as $part) {
    list($key, $value) = explode('=', $part);
    $signature_array[$key] = $value;
}

$incoming_signature = $signature_array['signature'] ?? '';
$algorithm = $signature_array['algorithm'] ?? 'MD5';

// Oblicz oczekiwaną sygnaturę
$expected_signature = hash($algorithm, $body . $payu_config['signature_key']);

// Sprawdź sygnaturę
if ($incoming_signature !== $expected_signature) {
    http_response_code(400);
    exit('Invalid signature');
}

// Przetwórz powiadomienie
if (isset($data['order'])) {
    $order = $data['order'];
    $order_status = $order['status'] ?? '';
    $ext_order_id = $order['extOrderId'] ?? '';
    
    // Wyciągnij ID zamówienia z extOrderId (format: GAMESTORE_{ID}_{timestamp})
    if (preg_match('/GAMESTORE_(\d+)_/', $ext_order_id, $matches)) {
        $zamowienie_id = (int)$matches[1];
        
        // Zaktualizuj status zamówienia w bazie danych
        $nowy_status = 'nowe';
        
        switch ($order_status) {
            case 'PENDING':
                $nowy_status = 'oczekuje_na_platnosc';
                break;
            case 'WAITING_FOR_CONFIRMATION':
                $nowy_status = 'oczekuje_na_potwierdzenie';
                break;
            case 'COMPLETED':
                $nowy_status = 'w_realizacji';
                break;
            case 'CANCELED':
                $nowy_status = 'anulowane';
                break;
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE zamowienia SET status = ? WHERE id = ?");
            $stmt->execute([$nowy_status, $zamowienie_id]);
            
            // Jeśli płatność została zakończona pomyślnie, możesz wysłać email do klienta
            if ($order_status === 'COMPLETED') {
                // Pobierz dane zamówienia
                $stmt = $pdo->prepare("
                    SELECT z.*, u.email, u.pelna_nazwa 
                    FROM zamowienia z 
                    JOIN uzytkownicy u ON z.uzytkownik_id = u.id 
                    WHERE z.id = ?
                ");
                $stmt->execute([$zamowienie_id]);
                $zamowienie = $stmt->fetch();
                
                if ($zamowienie) {
                    // Tutaj możesz wysłać email potwierdzający płatność
                    // mail($zamowienie['email'], 'Potwierdzenie płatności', ...);
                    
                    // Log sukcesu
                    file_put_contents('payu_log.txt', 
                        date('Y-m-d H:i:s') . " - Płatność zakończona pomyślnie dla zamówienia #{$zamowienie_id}\n", 
                        FILE_APPEND
                    );
                }
            }
            
            // Odpowiedz PayU
            http_response_code(200);
            echo 'OK';
            
        } catch (PDOException $e) {
            // Log błędu
            file_put_contents('payu_log.txt', 
                date('Y-m-d H:i:s') . " - BŁĄD: " . $e->getMessage() . "\n", 
                FILE_APPEND
            );
            
            http_response_code(500);
            exit('Database error');
        }
    } else {
        http_response_code(400);
        exit('Invalid extOrderId');
    }
} else {
    http_response_code(400);
    exit('Invalid notification data');
}
?>