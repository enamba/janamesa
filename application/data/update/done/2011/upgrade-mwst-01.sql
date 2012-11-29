/**
*   Database change to float mwst
*   @author alex
*   @since 31.01.2011
*/

ALTER TABLE billing_assets add column mwstTmp FLOAT UNSIGNED AFTER mwst;
UPDATE billing_assets SET mwstTmp=mwst;
AlTER TABLE billing_assets DROP COLUMN mwst;
AlTER TABLE billing_assets CHANGE mwstTmp mwst FLOAT UNSIGNED;

ALTER TABLE billing_assets add column couriermwstTmp FLOAT UNSIGNED AFTER couriertotal;
UPDATE billing_assets SET couriermwstTmp=couriermwst;
AlTER TABLE billing_assets DROP COLUMN couriermwst;
AlTER TABLE billing_assets CHANGE couriermwstTmp couriermwst FLOAT UNSIGNED;

ALTER TABLE meal_categories add column mwstTmp FLOAT UNSIGNED AFTER mwst;
UPDATE meal_categories SET mwstTmp=mwst;
AlTER TABLE meal_categories DROP COLUMN mwst;
AlTER TABLE meal_categories CHANGE mwstTmp mwst FLOAT UNSIGNED;

ALTER TABLE meal_extras add column mwstTmp FLOAT UNSIGNED AFTER mwst;
UPDATE meal_extras SET mwstTmp=mwst;
AlTER TABLE meal_extras DROP COLUMN mwst;
AlTER TABLE meal_extras CHANGE mwstTmp mwst FLOAT UNSIGNED;

ALTER TABLE meal_options add column mwstTmp FLOAT UNSIGNED AFTER mwst;
UPDATE meal_options SET mwstTmp=mwst;
AlTER TABLE meal_options DROP COLUMN mwst;
AlTER TABLE meal_options CHANGE mwstTmp mwst FLOAT UNSIGNED;

ALTER TABLE meals add column mwstTmp FLOAT UNSIGNED AFTER mwst;
UPDATE meals SET mwstTmp=mwst;
AlTER TABLE meals DROP COLUMN mwst;
AlTER TABLE meals CHANGE mwstTmp mwst FLOAT UNSIGNED;




/**
*  additional fields
*  @author mlaug
*  @since 08.03.2011
*
*/

ALTER TABLE orders_bucket_meals add column taxTmp FLOAT UNSIGNED AFTER tax;
UPDATE orders_bucket_meals SET taxTmp=tax;
AlTER TABLE orders_bucket_meals DROP COLUMN tax;
AlTER TABLE orders_bucket_meals CHANGE taxTmp tax FLOAT UNSIGNED;

ALTER TABLE orders_bucket_meals_extras add column taxTmp FLOAT UNSIGNED AFTER tax;
UPDATE orders_bucket_meals_extras SET taxTmp=tax;
AlTER TABLE orders_bucket_meals_extras DROP COLUMN tax;
AlTER TABLE orders_bucket_meals_extras CHANGE taxTmp tax FLOAT UNSIGNED;

ALTER TABLE orders_bucket_meals_options add column taxTmp FLOAT UNSIGNED AFTER tax;
UPDATE orders_bucket_meals_options SET taxTmp=tax;
AlTER TABLE orders_bucket_meals_options DROP COLUMN tax;
AlTER TABLE orders_bucket_meals_options CHANGE taxTmp tax FLOAT UNSIGNED;

