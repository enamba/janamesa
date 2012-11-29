/**
*   Database upgrade v1.96
*   @author alex
*   @since 17.01.2011
*/

alter table rabatt add column `onlyCash` TINYINT(4) default 0 after info;
