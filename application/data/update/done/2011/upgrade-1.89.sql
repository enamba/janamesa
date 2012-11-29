/**
*   Database upgrade v1.88
*   @author alex
*   @since 20.12.2010
*/

alter table mining_clickstream add column referer varchar(500) default 'UNKNOWN';
