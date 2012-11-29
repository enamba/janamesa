/**
*   Database upgrade v1.88
*   @author alex
*   @since 20.12.2010
*/

alter table restaurants add column acceptsPfand TINYINT(4) default 0;
