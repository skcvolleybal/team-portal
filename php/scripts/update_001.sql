ALTER TABLE
  TeamPortal_aanwezigheden
ADD
  FOREIGN KEY (user_id) REFERENCES j3_users(id);
ALTER TABLE
  `TeamPortal_aanwezigheden` CHANGE `aanwezigheid` `is_aanwezig` ENUM('Y', 'N') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE
  `teamportal_fluitbeschikbaarheid` CHANGE `datum` `date` DATE NOT NULL;
ALTER TABLE
  `teamportal_fluitbeschikbaarheid` CHANGE `tijd` `time` TIME NOT NULL;
ALTER TABLE
  `teamportal_fluitbeschikbaarheid` CHANGE `is_beschikbaar` `is_beschikbaar` ENUM('Y', 'N') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;