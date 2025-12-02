<?php
// LOGIKA BIZNESOWA DLA UŻYTKOWNIKÓW

$komunikat = "";
$typ_komunikatu = "";

// USUWANIE UŻYTKOWNIKA
if (isset($_GET['usun'])) {
    $id = (int)$_GET['usun'];
    
    // Nie pozwalaj usunąć samego siebie ani ID=3 (główny admin)
    if ($id == $_SESSION['userid']) {
        $komunikat = "Nie możesz usunąć samego siebie!";
        $typ_komunikatu = "error";
    } elseif ($id == 3) {
        $komunikat = "Nie można usunąć głównego administratora!";
        $typ_komunikatu = "error";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM uzytkownicy WHERE id = ?");
            $stmt->execute([$id]);
            $komunikat = "Użytkownik został usunięty.";
            $typ_komunikatu = "success";
        } catch (PDOException $e) {
            $komunikat = "Błąd: " . $e->getMessage();
            $typ_komunikatu = "error";
        }
    }
}

// ZMIANA ROLI
if (isset($_POST['zmien_role'])) {
    $uzytkownik_id = (int)$_POST['uzytkownik_id'];
    $nowa_rola = $_POST['rola'];
    
    try {
        $stmt = $pdo->prepare("UPDATE uzytkownicy SET rola = ? WHERE id = ?");
        $stmt->execute([$nowa_rola, $uzytkownik_id]);
        $komunikat = "Rola użytkownika została zmieniona.";
        $typ_komunikatu = "success";
    } catch (PDOException $e) {
        $komunikat = "Błąd: " . $e->getMessage();
        $typ_komunikatu = "error";
    }
}

// DODAWANIE UŻYTKOWNIKA
if (isset($_POST['dodaj_uzytkownika'])) {
    $login = trim($_POST['login']);
    $haslo = trim($_POST['haslo']);
    $email = trim($_POST['email']);
    $pelna_nazwa = trim($_POST['pelna_nazwa']);
    $rola = $_POST['rola'];
    
    if (empty($login) || empty($haslo) || empty($email) || empty($pelna_nazwa)) {
        $komunikat = "Wypełnij wszystkie pola!";
        $typ_komunikatu = "error";
    } else {
        try {
            // Sprawdź czy login już istnieje
            $stmt = $pdo->prepare("SELECT id FROM uzytkownicy WHERE login = ?");
            $stmt->execute([$login]);
            
            if ($stmt->fetch()) {
                $komunikat = "Login już istnieje!";
                $typ_komunikatu = "error";
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO uzytkownicy (login, haslo, email, pelna_nazwa, rola) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$login, $haslo, $email, $pelna_nazwa, $rola]);
                $komunikat = "Użytkownik został dodany.";
                $typ_komunikatu = "success";
            }
        } catch (PDOException $e) {
            $komunikat = "Błąd: " . $e->getMessage();
            $typ_komunikatu = "error";
        }
    }
}

// EDYCJA UŻYTKOWNIKA
$edytowany_uzytkownik = null;
if (isset($_GET['edytuj'])) {
    $id = (int)$_GET['edytuj'];
    $stmt = $pdo->prepare("SELECT * FROM uzytkownicy WHERE id = ?");
    $stmt->execute([$id]);
    $edytowany_uzytkownik = $stmt->fetch();
}

if (isset($_POST['edytuj_uzytkownika'])) {
    $id = (int)$_POST['id'];
    $email = trim($_POST['email']);
    $pelna_nazwa = trim($_POST['pelna_nazwa']);
    $nowe_haslo = trim($_POST['nowe_haslo']);
    
    try {
        if (!empty($nowe_haslo)) {
            // Zmień hasło
            $stmt = $pdo->prepare("UPDATE uzytkownicy SET email = ?, pelna_nazwa = ?, haslo = ? WHERE id = ?");
            $stmt->execute([$email, $pelna_nazwa, $nowe_haslo, $id]);
        } else {
            // Bez zmiany hasła
            $stmt = $pdo->prepare("UPDATE uzytkownicy SET email = ?, pelna_nazwa = ? WHERE id = ?");
            $stmt->execute([$email, $pelna_nazwa, $id]);
        }
        
        $komunikat = "Dane użytkownika zostały zaktualizowane.";
        $typ_komunikatu = "success";
        
        // Wyczyść edytowanego użytkownika
        $edytowany_uzytkownik = null;
        header("Location: uzytkownicy.php");
        exit;
    } catch (PDOException $e) {
        $komunikat = "Błąd: " . $e->getMessage();
        $typ_komunikatu = "error";
    }
}

// FILTROWANIE
$search = $_GET['search'] ?? '';
$rola_filter = $_GET['rola'] ?? '';

$query = "
    SELECT u.*, 
           COUNT(DISTINCT z.id) as liczba_zamowien,
           SUM(z.suma) as suma_zamowien
    FROM uzytkownicy u
    LEFT JOIN zamowienia z ON u.id = z.uzytkownik_id
    WHERE 1=1
";
$params = [];

if ($search !== '') {
    $query .= " AND (u.login LIKE :search OR u.email LIKE :search OR u.pelna_nazwa LIKE :search)";
    $params[':search'] = "%" . $search . "%";
}

if ($rola_filter !== '') {
    $query .= " AND u.rola = :rola";
    $params[':rola'] = $rola_filter;
}

$query .= " GROUP BY u.id ORDER BY u.data_rejestracji DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$uzytkownicy = $stmt->fetchAll();

// STATYSTYKI
$stmt = $pdo->query("SELECT COUNT(*) as total FROM uzytkownicy WHERE rola = 'user'");
$total_users = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM uzytkownicy WHERE rola = 'admin'");
$total_admins = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM uzytkownicy WHERE DATE(data_rejestracji) = CURDATE()");
$new_today = $stmt->fetch()['total'];
?>