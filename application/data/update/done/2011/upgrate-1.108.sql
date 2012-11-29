/**
* drop billNotify
* @author alex
* since 08.02.2011
*/

ALTER TABLE restaurants CHANGE billDeliver billDeliver CHAR(5);
UPDATE restaurants SET billDeliver = billNotify;
ALTER TABLE restaurants DROP COLUMN billNotify;