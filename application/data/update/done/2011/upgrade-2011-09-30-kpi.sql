DROP TABLE IF EXISTS `admin_access_tracking`;

CREATE TABLE `admin_access_tracking` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `action` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `adminId` INT NULL DEFAULT NULL,
    `modelType` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `modelId` INT NOT NULL DEFAULT 0,
    `time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    INDEX (`action` , `adminId`, `modelId`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
