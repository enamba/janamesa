/**
 * Database upgrade v1.11
 *
 * @author vait
 */

ALTER TABLE restaurants ADD COLUMN `billingContactId` int(11);
ALTER TABLE contacts ADD COLUMN `billingName` VARCHAR(255);

UPDATE restaurants r SET r.billingContactId = r.contactId where r.billingContactId is NULL;

ALTER TABLE meals ADD COLUMN `excludeFromMinCost` tinyint(1) default 0;
ALTER TABLE meal_categories ADD COLUMN `excludeFromMinCost` tinyint(1) default 0;