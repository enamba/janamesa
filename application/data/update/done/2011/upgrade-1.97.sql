/**
*   Database upgrade v1.97
*   @author alex
*   @since 18.01.2011
*/

alter table rabatt drop column `onlyCash`, add column `noCash` TINYINT(4) default 0 after info;
