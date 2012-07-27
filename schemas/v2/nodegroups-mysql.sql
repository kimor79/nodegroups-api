CREATE TABLE IF NOT EXISTS `nodegroups` (
 `nodegroup` VARCHAR(255) NOT NULL,
 `description` TEXT NOT NULL,
 `expression` LONGTEXT NOT NULL,
 PRIMARY KEY  (`nodegroup`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `children` (
 `nodegroup` VARCHAR(255) NOT NULL,
 `child` VARCHAR(255) NOT NULL,
 `inherited` TINYINT(1) NOT NULL,
 UNIQUE KEY (`nodegroup`, `child`),
 CONSTRAINT FOREIGN KEY (`nodegroup`) REFERENCES nodegroups(`nodegroup`) ON DELETE CASCADE,
 CONSTRAINT FOREIGN KEY (`child`) REFERENCES nodegroups(`nodegroup`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `nodegroup_history` (
 `nodegroup` VARCHAR(255) NOT NULL,
 `user` VARCHAR(255) NOT NULL,
 `timestamp` INT(10) UNSIGNED NOT NULL DEFAULT 0,
 `action` VARCHAR(255) NOT NULL,
 `description` LONGTEXT NOT NULL,
 `expression` LONGTEXT NOT NULL,
 INDEX (`nodegroup`, `timestamp`),
 INDEX (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
