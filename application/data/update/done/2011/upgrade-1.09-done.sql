/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.9
 * Indexes improvements
 *
 * @author vpriem
 */
ALTER TABLE `company_budgets_ydcard`
    ADD INDEX `fkBudgetId` (`budgetId`),
    ADD INDEX `fkCompanyId` (`companyId`);

ALTER TABLE `salesperson_company`
    ADD INDEX `fkSalespersonId` (`salespersonId`),
    ADD INDEX `fkCompanyId` (`companyId`);

ALTER TABLE `linkcheck`
    ADD INDEX `fkCompanyId` (`restaurantId`);

ALTER TABLE `restaurant_servicetype`
    ADD INDEX `fkRestaurantId` (`restaurantId`),
    ADD INDEX `fkServicetypeId` (`servicetypeId`);

ALTER TABLE `restaurant_ratings`
    ADD INDEX `fkRestaurantId` (`restaurantId`);

/**
 * Ratings import
 */
UPDATE `restaurants` r
SET r.ratingQuality = (
    SELECT SUM(rr.quality) / COUNT(rr.id)
    FROM restaurant_ratings rr
    WHERE rr.restaurantId = r.id
),
r.ratingDelivery = (
    SELECT SUM(rr.delivery) / COUNT(rr.id)
    FROM restaurant_ratings rr
    WHERE rr.restaurantId = r.id
);


/**
 * Ratings triggers improvements
 */
DROP TRIGGER t1_restaurants;
DROP TRIGGER t2_restaurants;
DROP TRIGGER t3_restaurants;

DELIMITER |

CREATE TRIGGER t1_restaurants
AFTER INSERT ON `restaurant_ratings`
FOR EACH ROW
BEGIN
    UPDATE `restaurants` r
    SET r.ratingQuality = (
        SELECT SUM(rr.quality) / COUNT(rr.id)
        FROM restaurant_ratings rr
        WHERE rr.restaurantId = NEW.restaurantId
    ),
    r.ratingDelivery = (
        SELECT SUM(rr.delivery) / COUNT(rr.id)
        FROM restaurant_ratings rr
        WHERE rr.restaurantId = NEW.restaurantId
    )
    WHERE r.id = NEW.restaurantId;
END;

|

CREATE TRIGGER t2_restaurants
AFTER UPDATE ON `restaurant_ratings`
FOR EACH ROW
BEGIN
    UPDATE `restaurants` r
    SET r.ratingQuality = (
        SELECT SUM(rr.quality) / COUNT(rr.id)
        FROM restaurant_ratings rr
        WHERE rr.restaurantId = NEW.restaurantId
    ),
    r.ratingDelivery = (
        SELECT SUM(rr.delivery) / COUNT(rr.id)
        FROM restaurant_ratings rr
        WHERE rr.restaurantId = NEW.restaurantId
    )
    WHERE r.id = NEW.restaurantId;
END;

|

CREATE TRIGGER t3_restaurants
AFTER DELETE ON `restaurant_ratings`
FOR EACH ROW
BEGIN
    UPDATE `restaurants` r
    SET r.ratingQuality = (
        SELECT SUM(rr.quality) / COUNT(rr.id)
        FROM restaurant_ratings rr
        WHERE rr.restaurantId = OLD.restaurantId
    ),
    r.ratingDelivery = (
        SELECT SUM(rr.delivery) / COUNT(rr.id)
        FROM restaurant_ratings rr
        WHERE rr.restaurantId = OLD.restaurantId
    )
    WHERE r.id = OLD.restaurantId;
END;

|
DELIMITER ;