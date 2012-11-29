
-- @author alex
-- @since 20.06.11
-- crm tools

CREATE TABLE `crm_call` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `refType` VARCHAR(255) NOT NULL,   
    `refId` INT(10) UNSIGNED NOT NULL,
    `initiator` VARCHAR(255) DEFAULT NULL,
    `assignedToId` INT(10) UNSIGNED DEFAULT NULL,
    `realm` VARCHAR(255) DEFAULT NULL,
    `msgType` VARCHAR(255) DEFAULT NULL,
    `state` TINYINT(4)  DEFAULT NULL,
    `priority` INT(10) DEFAULT NULL,
    `reason` VARCHAR(255) DEFAULT NULL,
    `scheduled` timestamp NULL,
    `createdBy` INT(10) UNSIGNED NOT NULL,
    `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `crm_task` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `refType` VARCHAR(255) NOT NULL,   
    `refId` INT(10) UNSIGNED NOT NULL,
    `assignedToId` INT(10) UNSIGNED NOT NULL,
    `realm` VARCHAR(255) DEFAULT NULL,
    `msgType` VARCHAR(255) DEFAULT NULL,
    `priority` INT(10) DEFAULT NULL,
    `state` TINYINT(4)  DEFAULT NULL,
    `theme` VARCHAR(255) DEFAULT NULL,
    `reason` VARCHAR(255) DEFAULT NULL,
    `ordererId` INT(10) UNSIGNED NOT NULL,
    `scheduled` timestamp NULL,
    `createdBy` INT(10) UNSIGNED NOT NULL,
    `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `crm_call_history` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `callId` INT(10) UNSIGNED NOT NULL,
    `assignedToId` INT(10) UNSIGNED DEFAULT NULL,
    `realm` VARCHAR(255) DEFAULT NULL,
    `msgType` VARCHAR(255) DEFAULT NULL,
    `state` TINYINT(4)  DEFAULT NULL,
    `priority` INT(10) DEFAULT NULL,
    `reason` VARCHAR(255) DEFAULT NULL,
    `scheduled` timestamp NULL,
    `changedBy` INT(10) UNSIGNED NOT NULL,
    `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE `crm_task_history` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `taskId` INT(10) UNSIGNED NOT NULL,
    `assignedToId` INT(10) UNSIGNED NOT NULL,
    `realm` VARCHAR(255) DEFAULT NULL,
    `msgType` VARCHAR(255) DEFAULT NULL,
    `priority` INT(10) DEFAULT NULL,
    `state` TINYINT(4)  DEFAULT NULL,
    `theme` VARCHAR(255) DEFAULT NULL,
    `reason` VARCHAR(255) DEFAULT NULL,
    `ordererId` INT(10) UNSIGNED NOT NULL,
    `scheduled` timestamp NULL,
    `changedBy` INT(10) UNSIGNED NOT NULL,
    `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;