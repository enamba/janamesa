/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.8 Branch
 * Triggers to speed up calling on landing page
 * @author Matthias Laug
 */

alter table restaurants drop servicetypeId;
CREATE TABLE `restaurant_servicetype` (
  `id` int(11) NOT NULL auto_increment,
  `restaurantId` int(11) not null,
  `servicetypeId` int(11) not null,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DELIMITER |
create trigger t1_restaurant_servicetype
after insert on servicetypes_meal_categorys_nn
FOR EACH ROW BEGIN
    DECLARE s INT;
    SELECT count(*) into s from servicetypes_meal_categorys_nn where servicetypeId=NEW.servicetypeId;
    IF s <= 0
        THEN INSERT INTO restaurant_servicetype (restaurantId,servicetypeId) values((select r.id from restaurants r inner join meal_categories mc on mc.restaurantId=r.id where mc.id=NEW.mealCategoryId),NEW.servicetypeId);
    END IF;
END;

|

DELIMITER |
create trigger t2_restaurant_servicetype
after update on servicetypes_meal_categorys_nn
FOR EACH ROW
BEGIN
    DECLARE s INT;
    REPLACE INTO restaurant_servicetype (restaurantId,servicetypeId) values((select r.id from restaurants r inner join meal_categories mc on mc.restaurantId=r.id where mc.id=NEW.mealCategoryId),NEW.servicetypeId);
    SELECT count(*) into s from servicetypes_meal_categorys_nn where servicetypeId=OLD.servicetypeId;
    IF s <= 0
        THEN delete from restaurant_servicetype where restaurantId=(select r.id from restaurants r inner join meal_categories mc on mc.restaurantId=r.id where mc.id=OLD.mealCategoryId) and servicetypeId=OLD.servicetypeId;
    END IF;
END;

|

DELIMITER |
create trigger t3_restaurant_servicetype
after delete on servicetypes_meal_categorys_nn
FOR EACH ROW
BEGIN
    DECLARE s INT;
    SELECT count(*) into s from servicetypes_meal_categorys_nn where servicetypeId=OLD.servicetypeId;
    IF s <= 0
        THEN delete from restaurant_servicetype where restaurantId=(select r.id from restaurants r inner join meal_categories mc on mc.restaurantId=r.id where mc.id=OLD.mealCategoryId) and servicetypeId=OLD.servicetypeId;
    END IF;
END;

|
DELIMITER ;

insert into restaurant_servicetype (restaurantId,servicetypeId) select distinct r.id,smcn.servicetypeId from restaurants r inner join meal_categories mc on mc.restaurantId=r.id inner join servicetypes_meal_categorys_nn smcn on smcn.mealCategoryId=mc.id;

/**
 * Database upgrade v1.8
 * Triggers to speed up calling on service page
 * @author Matthias Laug
 */
alter table restaurants add column ratingQuality int(11) default 5;
alter table restaurants add column ratingDelivery int(11) default 5;
alter table restaurant_ratings add column restaurantId int(11) not null;
update restaurant_ratings rr inner join orders o on o.id=rr.orderId set rr.restaurantId=o.restaurantId;
DELIMITER |

create trigger t1_restaurants
after insert on restaurant_ratings
FOR EACH ROW
BEGIN
    DECLARE c INT;
    DECLARE q INT;
    DECLARE d INT;
    DECLARE finalQ INT;
    DECLARE finalD INT;
    SELECT count(*) into c from restaurant_ratings where restaurantId=NEW.restaurantId;
    SELECT sum(quality) into q from restaurant_ratings where restaurantId=NEW.restaurantId;
    SELECT sum(delivery) into d from restaurant_ratings where restaurantId=NEW.restaurantId;
    SET finalQ = q/c;
    SET finalD = d/c;
    update restaurants set ratingQuality=finalQ, ratingDelivery=finalD where id=NEW.restaurantId;
END;

|

create trigger t2_restaurants
after update on restaurant_ratings
FOR EACH ROW
BEGIN
    DECLARE c INT;
    DECLARE q INT;
    DECLARE d INT;
    DECLARE finalQ INT;
    DECLARE finalD INT;
    SELECT count(*) into c from restaurant_ratings where restaurantId=NEW.restaurantId;
    SELECT sum(quality) into q from restaurant_ratings where restaurantId=NEW.restaurantId;
    SELECT sum(delivery) into d from restaurant_ratings where restaurantId=NEW.restaurantId;
    SET finalQ = q/c;
    SET finalD = d/c;
    update restaurants set ratingQuality=finalQ, ratingDelivery=finalD where id=NEW.restaurantId;
END;

|

create trigger t3_restaurants
after delete on restaurant_ratings
FOR EACH ROW
BEGIN
    DECLARE c INT;
    DECLARE q INT;
    DECLARE d INT;
    DECLARE finalQ INT;
    DECLARE finalD INT;
    SELECT count(*) into c from restaurant_ratings where restaurantId=OLD.restaurantId;
    SELECT sum(quality) into q from restaurant_ratings where restaurantId=OLD.restaurantId;
    SELECT sum(delivery) into d from restaurant_ratings where restaurantId=OLD.restaurantId;
    SET finalQ = q/c;
    SET finalD = d/c;
    update restaurants set ratingQuality=finalQ, ratingDelivery=finalD where id=OLD.restaurantId;
END;

|
DELIMITER ;