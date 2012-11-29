-- @author Matthias Laug
-- add individuals charges for each online transaction

alter table restaurants
    add column chargePercentage DECIMAL(4,2) DEFAULT 0,
    add column chargeFix DECIMAL(6,2) DEFAULT 45,
    add column chargeStart DATE DEFAULT NULL;