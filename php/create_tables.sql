-- For a fresh TeamPortal install
-- Create the TeamPortal database first, then run the following SQL to create the necessary tables:

CREATE TABLE `barcie_days` (
 `id` int NOT NULL AUTO_INCREMENT,
 `date` date NOT NULL,
 `remarks` int DEFAULT NULL,	
 PRIMARY KEY (`id`),
 UNIQUE KEY `date` (`date`),
 KEY `date_2` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=213 DEFAULT CHARSET=latin1;

CREATE TABLE `barcie_availability` (
 `id` int NOT NULL AUTO_INCREMENT,
 `day_id` int NOT NULL,
 `user_id` int NOT NULL,
 `is_beschikbaar` enum('Ja','Nee') NOT NULL,
 `remarks` text,
 PRIMARY KEY (`id`),
 UNIQUE KEY `unique_day_user` (`day_id`,`user_id`),
 KEY `day_id_index` (`day_id`),
 KEY `user_id` (`user_id`),
 CONSTRAINT `FK_barcie_days` FOREIGN KEY (`day_id`) REFERENCES `barcie_days` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=562 DEFAULT CHARSET=latin1;


	CREATE TABLE `barcie_schedule_map` (
 `id` int NOT NULL AUTO_INCREMENT,
 `day_id` int NOT NULL,
 `user_id` int NOT NULL,
 `shift` int NOT NULL,
 `is_bhv` tinyint(1) DEFAULT NULL,
 `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`),
 UNIQUE KEY `UNIQUE_DAY_USER_SHIFT` (`id`,`day_id`,`user_id`),
 KEY `day_id_index` (`day_id`),
 KEY `user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=193 DEFAULT CHARSET=latin1;

CREATE TABLE `teamportal_aanwezigheden` (
 `id` int NOT NULL AUTO_INCREMENT,
 `match_id` varchar(16) NOT NULL,
 `user_id` int NOT NULL,
 `is_aanwezig` enum('Ja','Nee') NOT NULL,
 `rol` enum('speler','coach') NOT NULL,
 PRIMARY KEY (`id`),
 KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=650 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `teamportal_email` (
 `id` int NOT NULL AUTO_INCREMENT,
 `sender_email` varchar(64) NOT NULL,
 `sender_naam` varchar(64) NOT NULL,
 `receiver_email` varchar(64) NOT NULL,
 `receiver_naam` varchar(64) NOT NULL,
 `titel` varchar(128) NOT NULL,
 `body` text NOT NULL,
 `signature` varchar(40) NOT NULL,
 `queue_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `send_date` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `signature` (`signature`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

CREATE TABLE `teamportal_fluitbeschikbaarheid` (
 `id` int NOT NULL AUTO_INCREMENT,
 `user_id` int NOT NULL,
 `date` date NOT NULL,
 `time` time NOT NULL,
 `is_beschikbaar` enum('Ja','Nee','Onbekend') NOT NULL,
 `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `teamportal_wedstrijden` (
 `id` int NOT NULL AUTO_INCREMENT,
 `match_id` varchar(16) NOT NULL,
 `timestamp` timestamp NULL DEFAULT NULL,
 `is_veranderd` tinyint(1) NOT NULL DEFAULT '0',
 `scheidsrechter_id` int DEFAULT NULL,
 `teller1_id` int DEFAULT NULL,
 `teller2_id` int DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `match_id` (`match_id`),
 KEY `teller1` (`teller1_id`),
 KEY `teller2` (`teller2_id`)
) ENGINE=InnoDB AUTO_INCREMENT=269 DEFAULT CHARSET=utf8mb3;

CREATE TABLE `teamportal_zaalwacht` (
 `id` int NOT NULL AUTO_INCREMENT,
 `date` date NOT NULL,
 `team1_id` int unsigned DEFAULT NULL,
 `team2_id` int unsigned DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `date` (`date`),
 KEY `team1` (`team1_id`),
 KEY `team2` (`team2_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;