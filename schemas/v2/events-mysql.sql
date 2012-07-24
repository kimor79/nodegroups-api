CREATE TABLE IF NOT EXISTS `events` (
 `id` VARCHAR(255) NOT NULL,
 `_order` TINYINT(1) NOT NULL DEFAULT 0,
 `nodegroup` VARCHAR(255) NOT NULL,
 `user` VARCHAR(255) NOT NULL,
 `timestamp` INT(10) UNSIGNED NOT NULL DEFAULT 0,
 `event` VARCHAR(255) NOT NULL,
 `node` VARCHAR(255) NOT NULL,
 INDEX (`nodegroup`, `timestamp`),
 INDEX (`node`, `timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
