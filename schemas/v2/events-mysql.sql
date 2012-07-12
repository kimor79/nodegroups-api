CREATE TABLE IF NOT EXISTS `nodegroup_events` (
 `nodegroup` VARCHAR(255) NOT NULL,
 `user` VARCHAR(255) NOT NULL,
 `timestamp` INT(10) UNSIGNED NOT NULL DEFAULT 0,
 `event` VARCHAR(255) NOT NULL,
 `node` VARCHAR(255) NOT NULL,
 INDEX (`nodegroup`, `timestamp`),
 INDEX (`node`, `timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
