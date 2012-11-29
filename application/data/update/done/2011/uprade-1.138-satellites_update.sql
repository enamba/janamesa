
-- @author alex
-- @since 23.05.11

ALTER TABLE `satellites`
    ADD COLUMN `dynamicText` TEXT DEFAULT NULL,
    ADD COLUMN `cssTemplate` VARCHAR(255) DEFAULT NULL,
    ADD COLUMN `showOpinions` tinyint(4) DEFAULT NULL,
    ADD COLUMN `showJobs` tinyint(4) DEFAULT NULL,
    ADD COLUMN `showFacebookLink` tinyint(4) DEFAULT NULL,
    ADD COLUMN `facebookLink` VARCHAR(255) DEFAULT NULL;

