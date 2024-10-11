DROP TABLE IF EXISTS `DWF_wedstrijden`;
CREATE TABLE `DWF_wedstrijden` (
  `id` varchar(16) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `skcTeam` varchar(64) NOT NULL,
  `otherTeam` varchar(64) NOT NULL,
  `setsSkcTeam` int(11) NOT NULL,
  `setsOtherTeam` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `DWF_wedstrijden`
  ADD KEY `id` (`id`) USING BTREE;

DROP TABLE IF EXISTS `DWF_punten`;
CREATE TABLE `DWF_punten` (
  `id` int(11) NOT NULL,
  `matchId` varchar(16) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `skcTeam` varchar(9) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `set` int(11) NOT NULL,
  `isSkcService` char(1) NOT NULL,
  `isSkcPunt` char(1) NOT NULL,
  `puntenSkcTeam` int(11) NOT NULL,
  `puntenOtherTeam` int(11) NOT NULL,
  `ra` int(11) DEFAULT NULL,
  `rv` int(11) DEFAULT NULL,
  `mv` int(11) DEFAULT NULL,
  `lv` int(11) DEFAULT NULL,
  `la` int(11) DEFAULT NULL,
  `ma` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `DWF_punten`
  ADD PRIMARY KEY (`id`),
  ADD KEY `DWF_punten_matchId` (`matchId`),
  ADD KEY `DWF_punten_ra` (`ra`),
  ADD KEY `DWF_punten_rv` (`rv`),
  ADD KEY `DWF_punten_mv` (`mv`),
  ADD KEY `DWF_punten_lv` (`lv`),
  ADD KEY `DWF_punten_la` (`la`),
  ADD KEY `DWF_punten_ma` (`ma`);

ALTER TABLE `DWF_punten`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;