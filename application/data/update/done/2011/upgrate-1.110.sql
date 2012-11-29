/**
* drop triggers for servicetypes
* @author alex
* since 09.02.2011
*/

DROP TRIGGER IF EXISTS `t1_meal_categories`;
DROP TRIGGER IF EXISTS `trigger_meal_categories_before_delete`;

DROP TRIGGER IF EXISTS `t1_restaurant_servicetype`;
DROP TRIGGER IF EXISTS `t2_restaurant_servicetype`;
DROP TRIGGER IF EXISTS `t3_restaurant_servicetype`;

DROP TRIGGER IF EXISTS `trigger_servicetypes_meal_categorys_nn_after_insert`;
DROP TRIGGER IF EXISTS `trigger_servicetypes_meal_categorys_nn_after_update`;
DROP TRIGGER IF EXISTS `trigger_servicetypes_meal_categorys_nn_after_delete`;