CREATE TABLE IF NOT EXISTS `nodes` (
 `nodegroup` VARCHAR(255) NOT NULL,
 `node` VARCHAR(255) NOT NULL,
 `inherited` TINYINT(1) NOT NULL,
 UNIQUE KEY (`node`, `nodegroup`),
 CONSTRAINT FOREIGN KEY (`nodegroup`) REFERENCES nodegroups(`nodegroup`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
