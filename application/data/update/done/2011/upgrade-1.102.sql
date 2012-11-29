/**
*   Database upgrade v1.102
*   @author alex
*   @since 31.01.2011
*/

ALTER TABLE salesperson_restaurant add column `signed` TIMESTAMP;
UPDATE salesperson_restaurant SET `signed`=`created`;