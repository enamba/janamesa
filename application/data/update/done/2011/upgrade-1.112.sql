/**
*   Database upgrade v1.112
*   @author alex
*   @since 14.02.2011
*/

alter table restaurants add column `offlineStatusUntil` TIMESTAMP DEFAULT '0000-00-00 00:00:00';
