DROP TABLE IF EXISTS `restaurant_openings_default`;
CREATE TABLE `restaurant_openings_default` ( 
    `day` TINYINT(4) NOT NULL,
    `openings` VARCHAR(100) NULL,
    `closed` TINYINT(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

INSERT INTO `restaurant_openings_default` (`day`, `openings`, `closed`)
VALUES (0, NULL, 1), 
       (1, NULL, 1), 
       (2, NULL, 1), 
       (3, NULL, 1),
       (4, NULL, 1), 
       (5, NULL, 1), 
       (6, NULL, 1);
