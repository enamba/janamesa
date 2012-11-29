
-- @author alex
-- @since 30.03.2011
-- additional field for restaurant

alter table rabatt add onlyRestaurant TINYINT UNSIGNED NULL DEFAULT 0 after onlyCompany;
