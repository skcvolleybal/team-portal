ALTER TABLE `DWF_wedstrijden`
	ALTER `skcTeam` DROP DEFAULT;
ALTER TABLE `DWF_wedstrijden`
	CHANGE COLUMN `skcTeam` `skcTeam` VARCHAR(64) NOT NULL COLLATE 'latin1_general_cs' AFTER `id`;
ALTER TABLE `dwf_wedstrijden`
	ADD UNIQUE INDEX `wedstrijdteams` (`skcTeam`, `otherTeam`, `id`);