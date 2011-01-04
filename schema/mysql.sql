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
