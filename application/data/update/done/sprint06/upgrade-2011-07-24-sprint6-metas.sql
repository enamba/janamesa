ALTER TABLE `restaurants` 
    ADD `metaTitle` VARCHAR(255) DEFAULT NULL,
    ADD `metaKeywords` VARCHAR(255) DEFAULT NULL,
    ADD `metaDescription` VARCHAR(255) DEFAULT NULL,
    ADD `metaRobots` VARCHAR(20) DEFAULT NULL,
    ADD `metaGooglebot` VARCHAR(20) DEFAULT NULL;