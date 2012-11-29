/**
*   Database upgrade v1.99
*   @author alex
*   @since 25.01.2011
*/

ALTER TABLE meal_categories add column parentMealCategoryId INT UNSIGNED  DEFAULT NULL AFTER categoryPictureId;