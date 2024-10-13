-- seed.sql

-- Drop tables if they exist
DROP TABLE IF EXISTS barcie_availability;
DROP TABLE IF EXISTS barcie_days;
DROP TABLE IF EXISTS barcie_schedule_map;
DROP TABLE IF EXISTS TeamPortal_aanwezigheden;
DROP TABLE IF EXISTS TeamPortal_email;
DROP TABLE IF EXISTS TeamPortal_fluitbeschikbaarheid;
DROP TABLE IF EXISTS TeamPortal_wedstrijden;
DROP TABLE IF EXISTS TeamPortal_zaalwacht;

-- Create tables
CREATE TABLE barcie_days (
    id INT(11) NOT NULL AUTO_INCREMENT,
    date DATE NOT NULL,
    remarks INT(11) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY date (date)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


CREATE TABLE barcie_availability (
    id INT(11) NOT NULL AUTO_INCREMENT,
    day_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    is_beschikbaar ENUM('Ja', 'Nee') NOT NULL,
    remarks TEXT DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY unique_day_user (day_id, user_id),
    KEY day_id_index (day_id),
    KEY user_id (user_id),
    CONSTRAINT FK_barcie_days FOREIGN KEY (day_id) REFERENCES barcie_days (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE barcie_schedule_map (
    id INT(11) NOT NULL AUTO_INCREMENT,
    day_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    shift INT(11) NOT NULL,
    is_bhv TINYINT(1) DEFAULT NULL,
    created TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id),
    UNIQUE KEY UNIQUE_DAY_USER_SHIFT (id, day_id, user_id),
    KEY day_id_index (day_id),
    KEY user_id_index (user_id),
    CONSTRAINT FK_beacie_days FOREIGN KEY (day_id) REFERENCES barcie_days (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE TeamPortal_aanwezigheden (
    id INT(11) NOT NULL AUTO_INCREMENT,
    match_id VARCHAR(16) NOT NULL,
    user_id INT(11) NOT NULL,
    is_aanwezig ENUM('Ja', 'Nee') NOT NULL,
    rol ENUM('speler', 'coach') NOT NULL,
    PRIMARY KEY (id),
    KEY user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE TeamPortal_email (
    id INT(11) NOT NULL AUTO_INCREMENT,
    sender_email VARCHAR(64) NOT NULL,
    sender_naam VARCHAR(64) NOT NULL,
    receiver_email VARCHAR(64) NOT NULL,
    receiver_naam VARCHAR(64) NOT NULL,
    titel VARCHAR(128) NOT NULL,
    body TEXT NOT NULL,
    signature VARCHAR(40) NOT NULL,
    queue_date TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    send_date TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY signature (signature)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE TeamPortal_fluitbeschikbaarheid (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    is_beschikbaar ENUM('Ja', 'Nee', 'Onbekend') NOT NULL,
    created DATETIME NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE TeamPortal_wedstrijden (
    id INT(11) NOT NULL AUTO_INCREMENT,
    match_id VARCHAR(16) NOT NULL,
    timestamp TIMESTAMP NULL DEFAULT NULL,
    is_veranderd TINYINT(1) NOT NULL DEFAULT 0,
    scheidsrechter_id INT(11) DEFAULT NULL,
    teller1_id INT(11) DEFAULT NULL,
    teller2_id INT(11) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY match_id (match_id),
    KEY teller1 (teller1_id),
    KEY teller2 (teller2_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE TeamPortal_zaalwacht (
    id INT(11) NOT NULL AUTO_INCREMENT,
    date DATE NOT NULL,
    team1_id INT(10) UNSIGNED DEFAULT NULL,
    team2_id INT(10) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY date (date),
    KEY team1 (team1_id),
    KEY team2 (team2_id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
