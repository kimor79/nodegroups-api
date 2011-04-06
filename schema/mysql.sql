CREATE TABLE IF NOT EXISTS `nodegroups` (
 `nodegroup` VARCHAR(255) NOT NULL,
 `description` TEXT NOT NULL,
 `expression` LONGTEXT NOT NULL,
 PRIMARY KEY  (`nodegroup`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `parent_child` (
 `parent` VARCHAR(255) NOT NULL,
 `child` VARCHAR(255) NOT NULL,
 UNIQUE KEY (`parent`, `child`),
 CONSTRAINT FOREIGN KEY (`parent`) REFERENCES nodegroups(`nodegroup`) ON DELETE CASCADE,
 CONSTRAINT FOREIGN KEY (`child`) REFERENCES nodegroups(`nodegroup`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `nodes` (
 `nodegroup` VARCHAR(255) NOT NULL,
 `node` VARCHAR(255) NOT NULL,
 UNIQUE KEY (`node`, `nodegroup`),
 CONSTRAINT FOREIGN KEY (`nodegroup`) REFERENCES nodegroups(`nodegroup`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `order` (
 `nodegroup` VARCHAR(255) NOT NULL,
 `app` VARCHAR(255) NOT NULL,
 `order` INT(10) UNSIGNED NOT NULL DEFAULT 50,
 UNIQUE KEY (`app`, `nodegroup`),
 CONSTRAINT FOREIGN KEY (`nodegroup`) REFERENCES nodegroups(`nodegroup`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `nodegroup_history` (
 `nodegroup` VARCHAR(255) NOT NULL,
 `user` VARCHAR(255) NOT NULL,
 `c_time` INT(10) UNSIGNED NOT NULL DEFAULT 0,
 `action` VARCHAR(255) NOT NULL,
 `description` LONGTEXT NOT NULL,
 `expression` LONGTEXT NOT NULL,
 INDEX (`nodegroup`, `c_time`),
 INDEX (`c_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `order_history` (
 `nodegroup` VARCHAR(255) NOT NULL,
 `user` VARCHAR(255) NOT NULL,
 `c_time` INT(10) UNSIGNED NOT NULL DEFAULT 0,
 `action` VARCHAR(255) NOT NULL,
 `app` VARCHAR(255) NOT NULL,
 `old_order` INT(10) UNSIGNED NOT NULL DEFAULT 50,
 `new_order` INT(10) UNSIGNED NOT NULL DEFAULT 50,
 INDEX (`nodegroup`, `app`, `c_time`),
 INDEX (`app`),
 INDEX (`c_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `nodegroup_events` (
 `nodegroup` VARCHAR(255) NOT NULL,
 `user` VARCHAR(255) NOT NULL,
 `c_time` INT(10) UNSIGNED NOT NULL DEFAULT 0,
 `event` VARCHAR(255) NOT NULL,
 `node` VARCHAR(255) NOT NULL,
 INDEX (`nodegroup`, `c_time`, `node`),
 INDEX (`c_time`),
 INDEX (`node`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
