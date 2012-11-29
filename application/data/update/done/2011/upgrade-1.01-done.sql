/**
 * Increase version
 */
INSERT INTO `version` VALUES (NULL, NULL);

/**
 * Database upgrade v1.1
 *
 * @author vpriem
 */
ALTER TABLE `admin_access_rights`
    ADD INDEX `fkGroupId` (`groupId`),
    DROP INDEX `resourceId`,
    ADD INDEX `fkResourceId` (`resourceId`);

ALTER TABLE `admin_access_users`
    ADD INDEX `fkGroupId` (`groupId`),
    DROP INDEX `email`,
    ADD UNIQUE `uqEmail` (`email`);

ALTER TABLE `billing`
    ADD INDEX `fkCostcenterId` (`costcenterId`);

ALTER TABLE `billing_assets`
    ADD INDEX `fkCompanyId` (`companyId`),
    ADD INDEX `fkRestaurantId` (`restaurantId`);

ALTER TABLE `companys`
    ADD INDEX `fkContactId` (`contactId`),
    ADD INDEX `fbBillingContactId` (`billingContactId`),
    DROP INDEX `plz`,
    ADD INDEX `idxPlz` (`plz`),
    ADD INDEX `idxDeleted` (`deleted`);

ALTER TABLE `company_addresses`
    ADD INDEX `fkCompanyId` (`companyId`),
    DROP INDEX `plz` ,
    ADD INDEX `idxPlz` (`plz`);

ALTER TABLE `company_addresses_info`
    ADD INDEX `fkCustomerId` (`customerId`),
    ADD INDEX `fkAddressId` (`addressId`);

ALTER TABLE `company_addresses_nn`
    ADD INDEX `fkBudgetId` (`budgetId`),
    ADD INDEX `fkAddressId` (`addressId`),
    ADD INDEX `idxBudgetIdAddressId` (`budgetId`, `addressId`);

ALTER TABLE `company_bonus`
    ADD INDEX `fkCustomerId` (`customerId`), 
    ADD INDEX `fkCompanyId` (`companyId`);

ALTER TABLE `company_budgets`
    ADD INDEX `fkCompanyId` (`companyId`);

ALTER TABLE `company_budgets_times`
    ADD INDEX `fkBudgetId` (`budgetId`),
    ADD INDEX `fkCompanyId` (`companyId`);

ALTER TABLE `company_ordercodes`
    ADD INDEX `fkCompanyId` (`companyId`);

ALTER TABLE `contacts`
    DROP INDEX `plz`,
    ADD INDEX `idxPlz` (`plz`);

ALTER TABLE `courier`
    ADD INDEX `fkContactId` (`contactId`);

ALTER TABLE `courier_location`
    ADD INDEX `fkCourierId` (`courierId`);

ALTER TABLE `courier_restaurant`
    ADD INDEX `fkCourierId` (`courierId`),
    ADD INDEX `fkRestaurantId` (`restaurantId`);

ALTER TABLE `customers`
    ADD INDEX `idxEmailDeleted` (`email`, `deleted`),
    ADD INDEX `idxDeleted` (`deleted`);

ALTER TABLE `customer_company`
    ADD INDEX `fkCustomerId` (`customerId`),
    ADD INDEX `fkCompanyId` (`companyId`),
    ADD INDEX `fkBudgetId` (`budgetId`);

ALTER TABLE `customer_credit`
    ADD INDEX `fkCustomerId` (`customerId`);

ALTER TABLE `customer_credit_transaction`
    ADD INDEX `fkCustomerId` (`customerId`);

ALTER TABLE `customer_favourite_meals`
    ADD INDEX `fkCustomerId` (`customerId`),
    ADD INDEX `fkMealId` (`mealId`);

ALTER TABLE `customer_invite`
    ADD INDEX `fkCustomerId` (`customerId`),
    DROP INDEX `customerId`,
    ADD UNIQUE `uqCustomerIdEmail` (`customerId` , `email`);

ALTER TABLE `customer_locations`
    ADD INDEX `fkCustomerId` (`customerId`),
    DROP INDEX `plz`,
    ADD INDEX `idxPlz` (`plz`);

ALTER TABLE `customer_messages`
    ADD INDEX `fkCustomerId` (`customerId`);

ALTER TABLE `customer_tracked`
    ADD INDEX `fkCustomerId` (`customerId`),
    ADD INDEX `fkTrackingCodeId` (`trackingCodeId`),
    ADD INDEX `fkOrderId` (`orderId`);

ALTER TABLE `department`
    ADD INDEX `fkCompanyId` (`companyId`);

ALTER TABLE `department_customer`
    DROP INDEX `customer`,
    ADD INDEX `fkCustomerId` (`customerId`),
    DROP INDEX `department`,
    ADD INDEX `fkDepartmentId` (`departmentId`);

ALTER TABLE `department_projectnumbers`
    ADD INDEX `fkProjectnumbersId` (`projectnumbersId`),
    ADD INDEX `fkDepartmentId` (`departmentId`);

ALTER TABLE `department_tree`
    ADD INDEX `fkParentId` (`parentId`),
    ADD INDEX `fkChildId` (`childId`);

ALTER TABLE `favourites`
    ADD INDEX `fkCustomerId` (`customerId`),
    ADD INDEX `fkOrderId` (`orderId`);

ALTER TABLE `feedback`
    ADD INDEX `fkCustomerId` (`customerId`);

ALTER TABLE `meals`
    ADD INDEX `fkCategoryId` (`categoryId`),
    ADD INDEX `fkRestaurantId` (`restaurantId`),
    DROP INDEX `meal_index_desc` ,
    ADD INDEX `idxDescription` (`description` (255));

ALTER TABLE `meal_categories`
    ADD INDEX `fkRestaurantId` (`restaurantId`),
    ADD INDEX `fkCategoryPictureId` (`categoryPictureId`);

ALTER TABLE `meal_extras`
    ADD INDEX `fkRestaurantId` (`restaurantId`),
    ADD INDEX `fkCategoryId` (`categoryId`),
    ADD INDEX `fkGroupId` (`groupId`);

ALTER TABLE `meal_extras_groups`
    ADD INDEX `fkRestaurantId` (`restaurantId`);

ALTER TABLE `meal_extras_relations`
    ADD INDEX `fkExtraId` (`extraId`),
    ADD INDEX `fkMealId` (`mealId`),
    ADD INDEX `fkCategoryId` (`categoryId`),
    ADD INDEX `fkSizeId` (`sizeId`);

ALTER TABLE `meal_ingredients_nn`
    ADD INDEX `fkIngredientId` (`ingredientId`),
    ADD INDEX `fkMealId` (`mealId`),
    ADD INDEX `fkOptionId` (`optionId`);

ALTER TABLE `meal_options`
    ADD INDEX `fkRestaurantId` (`restaurantId`);

ALTER TABLE `meal_options_nn`
    ADD INDEX `fkOptionId` (`optionId`),
    ADD INDEX `fkOptionRowId` (`optionRowId`),
    ADD INDEX `idxOptionIdOptionRowId` (`optionId`, `optionRowId`);

ALTER TABLE `meal_options_rows`
    ADD INDEX `fkMealId` (`mealId`),
    ADD INDEX `fkCategoryId` (`categoryId`),
    ADD INDEX `fkRestaurantId` (`restaurantId`);

ALTER TABLE `meal_options_rows_nn`
    ADD INDEX `fkRestaurantId` (`restaurantId`),
    ADD INDEX `fkMealId` (`mealId`),
    ADD INDEX `fkOptionRowId` (`optionRowId`);

ALTER TABLE `meal_sizes`
    ADD INDEX `fkCategoryId` (`categoryId`);

ALTER TABLE `meal_sizes_nn`
    DROP INDEX `meal_sizes_index`,
    ADD INDEX `fkSizeId` (`sizeId`),
    DROP INDEX `meal_meal_index`,
    ADD INDEX `fkMealId` (`mealId`),
    ADD INDEX `idxMealIdSizeId` (`mealId`, `sizeId`);

ALTER TABLE `orders`
    ADD INDEX `fkRestaurantId` (`restaurantId`),
    ADD INDEX `fkCustomerId` (`customerId`),
    ADD INDEX `fkRabattCodeId` (`rabattCodeId`);

ALTER TABLE `orders_bucket_meals`
    DROP INDEX `order_bucket`,
    ADD INDEX `fkOrderId` (`orderId`),
    ADD INDEX `fkCustomerId` (`customerId`),
    ADD INDEX `fkMealId` (`mealId`),
    ADD INDEX `fkSizeId` (`sizeId`);

ALTER TABLE `orders_bucket_meals_extras`
    DROP INDEX `order_bucket_item_ext` ,
    ADD INDEX `fkBucketItemId` (`bucketItemId`),
    ADD INDEX `fkExtraId` (`extraId`);

ALTER TABLE `orders_bucket_meals_options`
    DROP INDEX `order_bucket_item_opt` ,
    ADD INDEX `fkBucketItemId` (`bucketItemId`),
    ADD INDEX `fkOptionId` (`optionId`);

ALTER TABLE `orders_customer_notregistered`
    ADD INDEX `fkOrderId` (`orderId`);

ALTER TABLE `orders_location`
    ADD INDEX `fkOrderId` (`orderId`);

ALTER TABLE `orders_storno`
    ADD INDEX `fkRestaurantId` (`restaurantId`),
    ADD INDEX `fkCustomerId` (`customerId`);

ALTER TABLE `order_company_group`
    ADD INDEX `fkCustomerId` (`customerId`),
    ADD INDEX `fkCompanyId` (`companyId`),
    ADD INDEX `fkCostcenterId` (`costcenterId`);

ALTER TABLE `order_group_nn`
    ADD INDEX `fkCustomerId` (`customerId`),
    ADD INDEX `fkOrderId`(`orderId`),
    ADD INDEX `fkRabattCodeId` (`rabattCodeId`);

ALTER TABLE `order_repeat`
    ADD INDEX `fkOrderId` (`orderId`);

ALTER TABLE `order_repeat_nn`
    ADD INDEX `fkOrderId` (`orderId`),
    ADD INDEX `fkRepeatId` (`repeatId`),
    ADD INDEX `idxOrderIdRepeatId` (`orderId`, `repeatId`);

ALTER TABLE `order_status`
    ADD INDEX `fkOrderId` (`orderId`);

ALTER TABLE `orte`
    DROP INDEX `plz_2`,
    DROP INDEX `plz`,
    ADD UNIQUE `uqPlz` (`plz`);

ALTER TABLE `projectnumbers`
    ADD INDEX `fkCompanyId` (`companyId`);

ALTER TABLE `proposal`
    ADD INDEX `fkCustomerId` (`customerId`);

ALTER TABLE `rabatt_codes`
    ADD INDEX `fkRabattCodeId` (`rabattId`);

ALTER TABLE `restaurants`
    ADD INDEX `fkCategoryId` (`categoryId`),
    ADD INDEX `fkContactId` (`contactId`),
    DROP INDEX `plz` ,
    ADD INDEX `idxPlz` (`plz`),
    ADD INDEX `idxDeleted` (`deleted`);

ALTER TABLE `restaurant_areas`
    ADD INDEX `fkRestaurantId` (`restaurantId`),
    ADD INDEX `fkServicetypeId` (`servicetypeId`);

ALTER TABLE `restaurant_categories`
    ADD INDEX `fkGoogleCategoryId` (`googleCategoryId`);

ALTER TABLE `restaurant_company`
    ADD INDEX `fkRestaurantId` (`restaurantId`),
    ADD INDEX `fkCompanyId` (`companyId`);

ALTER TABLE `restaurant_db`
    DROP INDEX `statusId`,
    ADD INDEX `fkStatusId` (`statusId`);

ALTER TABLE `restaurant_google_categories`
    ADD UNIQUE `uqIdx` (`id`);

ALTER TABLE `restaurant_openings`
    ADD INDEX `fkRestaurantId` (`restaurantId`),
    ADD INDEX `idxRestaurantIdDay` (`restaurantId`, `day`);

ALTER TABLE `restaurant_openings_special`
    ADD INDEX `fkRestaurantId` (`restaurantId`),
    ADD INDEX `idxRestaurantIdSpecialDate` (`restaurantId`, `specialDate`);

ALTER TABLE `restaurant_plz`
    DROP INDEX `restaurantId`,
    ADD INDEX `fkRestaurantId` (`restaurantId`),
    DROP INDEX `plz` ,
    ADD INDEX `idxPlz` (`plz`);

ALTER TABLE `restaurant_ratings`
    ADD INDEX `fkOrderId` (`orderId`),
    ADD INDEX `fkCustomerId` (`customerId`);

ALTER TABLE `salesperson_restaurant`
    ADD INDEX `fkSalespersonId` (`salespersonId`),
    ADD INDEX`fkRestaurantId`  (`restaurantId`);

ALTER TABLE `servicetypes_meal_categorys_nn`
    DROP INDEX `servicetypeId` ,
    ADD INDEX `fkServicetypeId` (`servicetypeId`),
    DROP INDEX `mealCategoryId` ,
    ADD INDEX `fkMealCategoryId` (`mealCategoryId`), 
    ADD INDEX `idxServicetypeIdMealCategoryId` (`servicetypeId`, `mealCategoryId`);

ALTER TABLE `supportNumber`
    ADD INDEX `idxActive` (`active`);

ALTER TABLE `tracking_code`
    ADD INDEX `fkCampaignId` (`campaignId`),
    ADD INDEX `fkLandingpageId` (`landingpageId`);

ALTER TABLE `user_rights`
    ADD INDEX `fkCustomerId` (`customerId`);