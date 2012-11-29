-- database update v. 1.76
-- author fhaferkorn
-- newslettertool views

DROP TABLE IF EXISTS `view_emails_all`;
DROP VIEW IF EXISTS `view_emails_all`;
CREATE VIEW `view_emails_all` AS
SELECT
    DISTINCT `email`
    FROM `orders_customer`
    ORDER BY `email`;

DROP TABLE IF EXISTS `view_emails_registered`;
DROP VIEW IF EXISTS `view_emails_registered`;
CREATE VIEW `view_emails_registered` AS
    SELECT DISTINCT `email`
    FROM `view_customers_registered`
    ORDER BY `email`;

DROP TABLE IF EXISTS `view_emails_notregistered`;
DROP VIEW IF EXISTS `view_emails_notregistered`;
CREATE VIEW `view_emails_notregistered` AS
    SELECT DISTINCT `email`
    FROM `view_customers_notregistered`
    ORDER BY `email`;

DROP TABLE IF EXISTS `view_emails_company`;
DROP VIEW IF EXISTS `view_emails_company`;
CREATE VIEW `view_emails_company` AS
    SELECT distinct `email`
    FROM customers c
        JOIN customer_company cc
        ON  cc.customerId = c.id
    WHERE c.deleted = 0
    ORDER BY `email`;