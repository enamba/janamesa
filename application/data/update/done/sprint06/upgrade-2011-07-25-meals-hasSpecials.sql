
-- @author alex
-- @since 25.07.11
-- meal types and ingredients

ALTER TABLE meal_sizes_nn ADD COLUMN hasSpecials TINYINT(4) NOT NULL DEFAULT 1 AFTER pfand;

