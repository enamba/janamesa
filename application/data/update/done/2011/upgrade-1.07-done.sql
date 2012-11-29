/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.7
 *
 * @author abriliano
 */

--
-- Table structure for table `linkcheck`
--

CREATE TABLE IF NOT EXISTS `linkcheck` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurantId` int(255) NOT NULL,
  `qype_ourlink` varchar(255) NOT NULL,
  `www_qype_com` varchar(255) DEFAULT NULL,
  `qype_status` enum('0','1','-1') NOT NULL DEFAULT '-1',
  `meinestadt_ourlink` varchar(255) NOT NULL,
  `www_meinestadt_de` varchar(255) DEFAULT NULL,
  `meinestadt_status` enum('0','1','-1') NOT NULL DEFAULT '-1',
  `townster_ourlink` varchar(255) NOT NULL,
  `www_townster_de` varchar(255) DEFAULT NULL,
  `townster_status` enum('0','1','-1') NOT NULL DEFAULT '-1',
  `goyellow_ourlink` varchar(255) NOT NULL,
  `www_goyellow_de` varchar(255) DEFAULT NULL,
  `goyellow_status` enum('0','1','-1') NOT NULL DEFAULT '-1',
  `stadtbranchenbuch_ourlink` varchar(255) NOT NULL,
  `www_stadtbranchenbuch_com` varchar(255) DEFAULT NULL,
  `stadtbranchenbuch_status` enum('0','1','-1') NOT NULL DEFAULT '-1',
  `pointoo_ourlink` varchar(255) NOT NULL,
  `www_pointoo_de` varchar(255) DEFAULT NULL,
  `pointoo_status` enum('0','1','-1') NOT NULL DEFAULT '-1',
  `gelbeseiten_ourlink` varchar(255) NOT NULL,
  `www_gelbeseiten_de` varchar(255) DEFAULT NULL,
  `gelbeseiten_status` enum('0','1','-1') NOT NULL DEFAULT '-1',
  `kennstdueinen_ourlink` varchar(255) NOT NULL,
  `www_kennstdueinen_de` varchar(255) DEFAULT NULL,
  `kennstdueinen_status` enum('0','1','-1') NOT NULL DEFAULT '-1',
  `auskunft_ourlink` varchar(255) NOT NULL,
  `www_auskunft_de` varchar(255) DEFAULT NULL,
  `auskunft_status` enum('0','1','-1') NOT NULL DEFAULT '-1',
  `total_online` int(10) DEFAULT NULL,
  `total_offline` int(10) DEFAULT NULL,
  `total_unknown` int(10) DEFAULT NULL,
  `time_update` int(14) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 ;


