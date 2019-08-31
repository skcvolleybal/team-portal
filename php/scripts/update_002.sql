ALTER TABLE
  `TeamPortal_aanwezigheden`
ADD
  column `rol` ENUM('speler', 'coach') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;