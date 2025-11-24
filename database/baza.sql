SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS sklep;
USE sklep;

CREATE TABLE `uzytkownicy` (
  `id` int NOT NULL AUTO_INCREMENT,
  `login` varchar(255) NOT NULL,
  `haslo` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `pelna_nazwa` varchar(255),
  `data_rejestracji` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `uzytkownicy` (`login`, `haslo`, `email`, `pelna_nazwa`) VALUES
('arek', 'test123', 'arek@example.com', 'Arek Gracz'),
('kasia', 'test123', 'kasia@example.com', 'Kasia Gamerka');

CREATE TABLE `kategorie` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nazwa` varchar(255) NOT NULL,
  `opis` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `kategorie` (`nazwa`, `opis`) VALUES
('FPS', 'Strzelanki pierwszoosobowe'),
('RPG', 'Gry fabularne'),
('Strategie', 'Gry strategiczne'),
('Sportowe', 'Gry sportowe'),
('Wyścigowe', 'Gry wyścigowe'),
('Akcja', 'Gry akcji');

CREATE TABLE `produkty` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nazwa` varchar(255) NOT NULL,
  `opis` text NOT NULL,
  `cena` decimal(10,2) NOT NULL,
  `data_dodania` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pegi` int NOT NULL,
  `platforma` varchar(50) NOT NULL,
  `wydawca` varchar(255) NOT NULL,
  `wersja` varchar(50) NOT NULL,
  `zdjecie` varchar(255),
  `ilosc_stan` int NOT NULL DEFAULT 0,
  `kategoria_id` int NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`kategoria_id`) REFERENCES `kategorie` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `produkty`
(`nazwa`, `opis`, `cena`, `pegi`, `platforma`, `wydawca`, `wersja`, `ilosc_stan`, `kategoria_id`)
VALUES
('Cyberpunk 2077', 'RPG w otwartym świecie.', 199.99, 18, 'PC', 'CD Projekt RED', 'klucz cyfrowy', 100, 2),
('The Witcher 3: Wild Hunt', 'Kultowe RPG fantasy.', 79.99, 18, 'PC', 'CD Projekt RED', 'płyta', 25, 2),
('Counter-Strike 2', 'Strzelanka FPS online.', 0.00, 16, 'PC', 'Valve', 'klucz cyfrowy', 9999, 1),
('FIFA 24', 'Symulator piłki nożnej.', 239.00, 3, 'PS5', 'EA Sports', 'płyta', 40, 4),
('Forza Horizon 5', 'Wyścigi w otwartym świecie.', 249.99, 12, 'Xbox', 'Microsoft', 'klucz cyfrowy', 60, 5),
('Age of Empires IV', 'Strategia czasu rzeczywistego.', 159.99, 12, 'PC', 'Microsoft', 'klucz cyfrowy', 30, 3);

CREATE TABLE `stan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `produkt_id` int NOT NULL,
  `zmiana` int NOT NULL,
  `data_zmiany` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `opis` varchar(255),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`produkt_id`) REFERENCES `produkty` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `stan` (`produkt_id`, `zmiana`, `opis`) VALUES
(1, 100, 'Pierwsze dodanie do magazynu'),
(2, 25, 'Pierwsza dostawa'),
(4, 40, 'Dostawa FIFA 24');

CREATE TABLE `komentarze` (
  `id` int NOT NULL AUTO_INCREMENT,
  `komentarz` text NOT NULL,
  `data_komentarza` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `produkt_id` int NOT NULL,
  `uzytkownik_id` int NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`produkt_id`) REFERENCES `produkty` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`uzytkownik_id`) REFERENCES `uzytkownicy` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `komentarze` (`komentarz`, `produkt_id`, `uzytkownik_id`) VALUES
('Świetna gra!', 1, 1),
('Najlepsze RPG!', 2, 2);

CREATE TABLE `koszyki` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uzytkownik_id` int NOT NULL,
  `data_utworzenia` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`uzytkownik_id`) REFERENCES `uzytkownicy` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `koszyki` (`uzytkownik_id`) VALUES (1), (2);

CREATE TABLE `pozycje_koszyka` (
  `id` int NOT NULL AUTO_INCREMENT,
  `koszyk_id` int NOT NULL,
  `produkt_id` int NOT NULL,
  `ilosc` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`koszyk_id`) REFERENCES `koszyki` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`produkt_id`) REFERENCES `produkty` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO `pozycje_koszyka` (`koszyk_id`, `produkt_id`, `ilosc`)
VALUES (1, 1, 1), (1, 3, 1), (2, 2, 1);

CREATE TABLE `dostawy` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nazwa` VARCHAR(255) NOT NULL,
  `koszt` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `dostawy` (`nazwa`, `koszt`) VALUES
('Kurier DPD', 19.99),
('Paczkomat InPost', 14.99),
('Odbiór osobisty', 0.00);

CREATE TABLE `zamowienia` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uzytkownik_id` int NOT NULL,
  `data_zamowienia` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) NOT NULL DEFAULT 'nowe',
  `suma` decimal(10,2) NOT NULL,
  `dostawa_id` INT NOT NULL DEFAULT 1,
  `adres_ulica` VARCHAR(255),
  `adres_miasto` VARCHAR(255),
  `adres_kod` VARCHAR(20),
  `adres_kraj` VARCHAR(255) DEFAULT 'Polska',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`uzytkownik_id`) REFERENCES `uzytkownicy` (`id`),
  FOREIGN KEY (`dostawa_id`) REFERENCES `dostawy`(`id`)
) ENGINE=InnoDB;

INSERT INTO `zamowienia` (`uzytkownik_id`, `suma`, `dostawa_id`, `adres_ulica`, `adres_miasto`, `adres_kod`, `adres_kraj`)
VALUES
(1, 199.99, 1, 'Testowa 12/3', 'Warszawa', '00-001', 'Polska'),
(2, 79.99, 2, 'Lipowa 5', 'Kraków', '30-002', 'Polska');

CREATE TABLE `pozycje_zamowienia` (
  `id` int NOT NULL AUTO_INCREMENT,
  `zamowienie_id` int NOT NULL,
  `produkt_id` int NOT NULL,
  `ilosc` int NOT NULL,
  `cena` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`zamowienie_id`) REFERENCES `zamowienia` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`produkt_id`) REFERENCES `produkty` (`id`)
) ENGINE=InnoDB;

INSERT INTO `pozycje_zamowienia`
(`zamowienie_id`, `produkt_id`, `ilosc`, `cena`)
VALUES
(1, 1, 1, 199.99),
(2, 2, 1, 79.99);

COMMIT;