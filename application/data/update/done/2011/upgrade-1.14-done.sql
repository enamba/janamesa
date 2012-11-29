/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.14
 *
 * @author mlaug
 */


Drop trigger t1_restaurant_servicetype;
DELIMITER |
create trigger t1_restaurant_servicetype
after insert on servicetypes_meal_categorys_nn
FOR EACH ROW BEGIN
    DECLARE s INT;
    SELECT count(*) into s from restaurant_servicetype where servicetypeId=NEW.servicetypeId and restaurantId=(select restaurantId from meal_categories mc where mc.id=NEW.mealCategoryId);
    IF s <= 0
        THEN INSERT INTO restaurant_servicetype (restaurantId,servicetypeId) values((select r.id from restaurants r inner join meal_categories mc on mc.restaurantId=r.id where mc.id=NEW.mealCategoryId),NEW.servicetypeId);
    END IF;
END;


/**
 * ATTTENTION: put INDEXes into your created tables
 */