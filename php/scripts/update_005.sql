ALTER TABLE `TeamPortal_wedstrijden` DROP PRIMARY KEY;
ALTER TABLE `TeamPortal_wedstrijden` ADD `id` INT(11) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);
ALTER TABLE `TeamPortal_wedstrijden` ADD `timestamp` TIMESTAMP NULL DEFAULT NULL AFTER `match_id`;
ALTER TABLE `TeamPortal_wedstrijden` ADD `is_veranderd` BOOLEAN NOT NULL DEFAULT FALSE AFTER `timestamp`;
ALTER TABLE `barcie_schedule_map` DROP INDEX `UNIQUE_DAY_USER_SHIFT`;
ALTER TABLE `deb105013n2_skc`.`barcie_schedule_map` ADD UNIQUE `UNIQUE_DAY_USER_SHIFT` (`id`, `day_id`, `user_id`);