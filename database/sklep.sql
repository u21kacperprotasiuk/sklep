-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Czas generowania: 27 Lis 2025, 13:05
-- Wersja serwera: 10.4.17-MariaDB
-- Wersja PHP: 8.0.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Baza danych: `sklep`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `dostawy`
--

CREATE TABLE `dostawy` (
  `id` int(11) NOT NULL,
  `nazwa` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `koszt` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Zrzut danych tabeli `dostawy`
--

INSERT INTO `dostawy` (`id`, `nazwa`, `koszt`) VALUES
(1, 'Kurier DPD', '19.99'),
(2, 'Paczkomat InPost', '14.99'),
(3, 'Odbiór osobisty', '0.00');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `kategorie`
--

CREATE TABLE `kategorie` (
  `id` int(11) NOT NULL,
  `nazwa` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `opis` text COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Zrzut danych tabeli `kategorie`
--

INSERT INTO `kategorie` (`id`, `nazwa`, `opis`) VALUES
(1, 'FPS', 'Strzelanki pierwszoosobowe'),
(2, 'RPG', 'Gry fabularne'),
(3, 'Strategie', 'Gry strategiczne'),
(4, 'Sportowe', 'Gry sportowe'),
(5, 'Wyścigowe', 'Gry wyścigowe'),
(6, 'Akcja', 'Gry akcji');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `komentarze`
--

CREATE TABLE `komentarze` (
  `id` int(11) NOT NULL,
  `komentarz` text COLLATE utf8_unicode_ci NOT NULL,
  `data_komentarza` datetime NOT NULL DEFAULT current_timestamp(),
  `produkt_id` int(11) NOT NULL,
  `uzytkownik_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Zrzut danych tabeli `komentarze`
--

INSERT INTO `komentarze` (`id`, `komentarz`, `data_komentarza`, `produkt_id`, `uzytkownik_id`) VALUES
(1, 'Świetna gra!', '2025-11-27 11:34:21', 1, 1),
(2, 'Najlepsze RPG!', '2025-11-27 11:34:21', 2, 2);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `koszyki`
--

CREATE TABLE `koszyki` (
  `id` int(11) NOT NULL,
  `uzytkownik_id` int(11) NOT NULL,
  `data_utworzenia` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Zrzut danych tabeli `koszyki`
--

INSERT INTO `koszyki` (`id`, `uzytkownik_id`, `data_utworzenia`) VALUES
(1, 1, '2025-11-27 11:34:21'),
(2, 2, '2025-11-27 11:34:21');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `pozycje_koszyka`
--

CREATE TABLE `pozycje_koszyka` (
  `id` int(11) NOT NULL,
  `koszyk_id` int(11) NOT NULL,
  `produkt_id` int(11) NOT NULL,
  `ilosc` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Zrzut danych tabeli `pozycje_koszyka`
--

INSERT INTO `pozycje_koszyka` (`id`, `koszyk_id`, `produkt_id`, `ilosc`) VALUES
(1, 1, 1, 1),
(2, 1, 3, 1),
(3, 2, 2, 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `pozycje_zamowienia`
--

CREATE TABLE `pozycje_zamowienia` (
  `id` int(11) NOT NULL,
  `zamowienie_id` int(11) NOT NULL,
  `produkt_id` int(11) NOT NULL,
  `ilosc` int(11) NOT NULL,
  `cena` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Zrzut danych tabeli `pozycje_zamowienia`
--

INSERT INTO `pozycje_zamowienia` (`id`, `zamowienie_id`, `produkt_id`, `ilosc`, `cena`) VALUES
(1, 1, 1, 1, '199.99'),
(2, 2, 2, 1, '79.99');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `produkty`
--

CREATE TABLE `produkty` (
  `id` int(11) NOT NULL,
  `nazwa` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `opis` text COLLATE utf8_unicode_ci NOT NULL,
  `cena` decimal(10,2) NOT NULL,
  `data_dodania` datetime NOT NULL DEFAULT current_timestamp(),
  `pegi` int(11) NOT NULL,
  `platforma` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `wydawca` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `wersja` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `zdjecie` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ilosc_stan` int(11) NOT NULL DEFAULT 0,
  `kategoria_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Zrzut danych tabeli `produkty`
--

INSERT INTO `produkty` (`id`, `nazwa`, `opis`, `cena`, `data_dodania`, `pegi`, `platforma`, `wydawca`, `wersja`, `zdjecie`, `ilosc_stan`, `kategoria_id`) VALUES
(1, 'Cyberpunk 2077', 'RPG w otwartym świecie.', '199.99', '2025-11-27 11:34:21', 18, 'PC', 'CD Projekt RED', 'klucz cyfrowy', 'cyberpunk2077.jpg', 100, 2),
(2, 'The Witcher 3: Wild Hunt', 'Kultowe RPG fantasy.', '79.99', '2025-11-27 11:34:21', 18, 'PC', 'CD Projekt RED', 'płyta', 'witcher.jpg', 25, 2),
(3, 'Counter-Strike 2', 'Strzelanka FPS online.', '0.00', '2025-11-27 11:34:21', 16, 'PC', 'Valve', 'klucz cyfrowy', 'counter-strike-2.jpg', 9999, 1),
(4, 'FIFA 24', 'Symulator piłki nożnej.', '239.00', '2025-11-27 11:34:21', 3, 'PS5', 'EA Sports', 'płyta', 'fc24.jpg', 40, 4),
(5, 'Forza Horizon 5', 'Wyścigi w otwartym świecie.', '249.99', '2025-11-27 11:34:21', 12, 'Xbox', 'Microsoft', 'klucz cyfrowy', 'forza5.jpg', 60, 5),
(6, 'Age of Empires IV', 'Strategia czasu rzeczywistego.', '159.99', '2025-11-27 11:34:21', 12, 'PC', 'Microsoft', 'klucz cyfrowy', 'age-of-empires-iv.jpg', 30, 3);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `stan`
--

CREATE TABLE `stan` (
  `id` int(11) NOT NULL,
  `produkt_id` int(11) NOT NULL,
  `zmiana` int(11) NOT NULL,
  `data_zmiany` datetime NOT NULL DEFAULT current_timestamp(),
  `opis` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Zrzut danych tabeli `stan`
--

INSERT INTO `stan` (`id`, `produkt_id`, `zmiana`, `data_zmiany`, `opis`) VALUES
(1, 1, 100, '2025-11-27 11:34:21', 'Pierwsze dodanie do magazynu'),
(2, 2, 25, '2025-11-27 11:34:21', 'Pierwsza dostawa'),
(3, 4, 40, '2025-11-27 11:34:21', 'Dostawa FIFA 24');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `uzytkownicy`
--

CREATE TABLE `uzytkownicy` (
  `id` int(11) NOT NULL,
  `login` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `haslo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pelna_nazwa` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_rejestracji` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Zrzut danych tabeli `uzytkownicy`
--

INSERT INTO `uzytkownicy` (`id`, `login`, `haslo`, `email`, `pelna_nazwa`, `data_rejestracji`) VALUES
(1, 'arek', 'test123', 'arek@example.com', 'Arek Gracz', '2025-11-27 11:34:21'),
(2, 'kasia', 'test123', 'kasia@example.com', 'Kasia Gamerka', '2025-11-27 11:34:21');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `zamowienia`
--

CREATE TABLE `zamowienia` (
  `id` int(11) NOT NULL,
  `uzytkownik_id` int(11) NOT NULL,
  `data_zamowienia` datetime NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'nowe',
  `suma` decimal(10,2) NOT NULL,
  `dostawa_id` int(11) NOT NULL DEFAULT 1,
  `adres_ulica` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `adres_miasto` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `adres_kod` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `adres_kraj` varchar(255) COLLATE utf8_unicode_ci DEFAULT 'Polska'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Zrzut danych tabeli `zamowienia`
--

INSERT INTO `zamowienia` (`id`, `uzytkownik_id`, `data_zamowienia`, `status`, `suma`, `dostawa_id`, `adres_ulica`, `adres_miasto`, `adres_kod`, `adres_kraj`) VALUES
(1, 1, '2025-11-27 11:34:21', 'nowe', '199.99', 1, 'Testowa 12/3', 'Warszawa', '00-001', 'Polska'),
(2, 2, '2025-11-27 11:34:21', 'nowe', '79.99', 2, 'Lipowa 5', 'Kraków', '30-002', 'Polska');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `dostawy`
--
ALTER TABLE `dostawy`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `kategorie`
--
ALTER TABLE `kategorie`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `komentarze`
--
ALTER TABLE `komentarze`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produkt_id` (`produkt_id`),
  ADD KEY `uzytkownik_id` (`uzytkownik_id`);

--
-- Indeksy dla tabeli `koszyki`
--
ALTER TABLE `koszyki`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uzytkownik_id` (`uzytkownik_id`);

--
-- Indeksy dla tabeli `pozycje_koszyka`
--
ALTER TABLE `pozycje_koszyka`
  ADD PRIMARY KEY (`id`),
  ADD KEY `koszyk_id` (`koszyk_id`),
  ADD KEY `produkt_id` (`produkt_id`);

--
-- Indeksy dla tabeli `pozycje_zamowienia`
--
ALTER TABLE `pozycje_zamowienia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `zamowienie_id` (`zamowienie_id`),
  ADD KEY `produkt_id` (`produkt_id`);

--
-- Indeksy dla tabeli `produkty`
--
ALTER TABLE `produkty`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategoria_id` (`kategoria_id`);

--
-- Indeksy dla tabeli `stan`
--
ALTER TABLE `stan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produkt_id` (`produkt_id`);

--
-- Indeksy dla tabeli `uzytkownicy`
--
ALTER TABLE `uzytkownicy`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `zamowienia`
--
ALTER TABLE `zamowienia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uzytkownik_id` (`uzytkownik_id`),
  ADD KEY `dostawa_id` (`dostawa_id`);

--
-- AUTO_INCREMENT dla zrzuconych tabel
--

--
-- AUTO_INCREMENT dla tabeli `dostawy`
--
ALTER TABLE `dostawy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT dla tabeli `kategorie`
--
ALTER TABLE `kategorie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT dla tabeli `komentarze`
--
ALTER TABLE `komentarze`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT dla tabeli `koszyki`
--
ALTER TABLE `koszyki`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT dla tabeli `pozycje_koszyka`
--
ALTER TABLE `pozycje_koszyka`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT dla tabeli `pozycje_zamowienia`
--
ALTER TABLE `pozycje_zamowienia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT dla tabeli `produkty`
--
ALTER TABLE `produkty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT dla tabeli `stan`
--
ALTER TABLE `stan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT dla tabeli `uzytkownicy`
--
ALTER TABLE `uzytkownicy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT dla tabeli `zamowienia`
--
ALTER TABLE `zamowienia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ograniczenia dla zrzutów tabel
--

--
-- Ograniczenia dla tabeli `komentarze`
--
ALTER TABLE `komentarze`
  ADD CONSTRAINT `komentarze_ibfk_1` FOREIGN KEY (`produkt_id`) REFERENCES `produkty` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `komentarze_ibfk_2` FOREIGN KEY (`uzytkownik_id`) REFERENCES `uzytkownicy` (`id`) ON DELETE CASCADE;

--
-- Ograniczenia dla tabeli `koszyki`
--
ALTER TABLE `koszyki`
  ADD CONSTRAINT `koszyki_ibfk_1` FOREIGN KEY (`uzytkownik_id`) REFERENCES `uzytkownicy` (`id`) ON DELETE CASCADE;

--
-- Ograniczenia dla tabeli `pozycje_koszyka`
--
ALTER TABLE `pozycje_koszyka`
  ADD CONSTRAINT `pozycje_koszyka_ibfk_1` FOREIGN KEY (`koszyk_id`) REFERENCES `koszyki` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pozycje_koszyka_ibfk_2` FOREIGN KEY (`produkt_id`) REFERENCES `produkty` (`id`) ON DELETE CASCADE;

--
-- Ograniczenia dla tabeli `pozycje_zamowienia`
--
ALTER TABLE `pozycje_zamowienia`
  ADD CONSTRAINT `pozycje_zamowienia_ibfk_1` FOREIGN KEY (`zamowienie_id`) REFERENCES `zamowienia` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pozycje_zamowienia_ibfk_2` FOREIGN KEY (`produkt_id`) REFERENCES `produkty` (`id`);

--
-- Ograniczenia dla tabeli `produkty`
--
ALTER TABLE `produkty`
  ADD CONSTRAINT `produkty_ibfk_1` FOREIGN KEY (`kategoria_id`) REFERENCES `kategorie` (`id`) ON DELETE CASCADE;

--
-- Ograniczenia dla tabeli `stan`
--
ALTER TABLE `stan`
  ADD CONSTRAINT `stan_ibfk_1` FOREIGN KEY (`produkt_id`) REFERENCES `produkty` (`id`) ON DELETE CASCADE;

--
-- Ograniczenia dla tabeli `zamowienia`
--
ALTER TABLE `zamowienia`
  ADD CONSTRAINT `zamowienia_ibfk_1` FOREIGN KEY (`uzytkownik_id`) REFERENCES `uzytkownicy` (`id`),
  ADD CONSTRAINT `zamowienia_ibfk_2` FOREIGN KEY (`dostawa_id`) REFERENCES `dostawy` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
