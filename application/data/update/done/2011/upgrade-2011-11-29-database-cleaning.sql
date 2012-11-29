-- @author alex

DROP TABLE `canteen`;

DROP TABLE `canteen_category`;

DROP TABLE `canteen_company`;

DROP TABLE `canteen_order`;

DROP TABLE `canteen_order_consolidate`;

DROP TABLE `customer_credit`;

DROP TABLE `customer_credit_transaction`;

DROP TABLE `customer_invite`;

DROP TABLE `gprs_printer`;

DROP TABLE `linkcheck`;

DROP TABLE `mining_transaction`;

DROP TABLE `options`;

DROP TABLE `order_group_nn`;

DROP TABLE `order_repeat`;

DROP TABLE `order_repeat_nn`;

DROP TABLE `orte`;

DROP TABLE `proposal`;

DROP TABLE `restaurant_areas`;

DROP TABLE `restaurant_google_categories`;

DROP TABLE `restaurant_gprs_printer`;

DROP TABLE `salesperson_company`;

DROP TABLE `salesperson_contract`;

DROP TABLE `salespersons_workschedule`;

DROP TABLE `worker`;

DROP TABLE `xmas`;


ALTER TABLE `admin_access_rights` DROP COLUMN `access`;

ALTER TABLE `customers` DROP COLUMN `premium`, 
                        DROP COLUMN `debit`;

ALTER TABLE `meals`     DROP COLUMN `duration`, 
                        DROP COLUMN `recommendation`;

ALTER TABLE `orders`    DROP COLUMN `bread`, 
                        DROP COLUMN `tip`, 
                        DROP COLUMN `single`, 
                        DROP COLUMN `paymentResp`, 
                        DROP COLUMN `creditAmount`;

ALTER TABLE `restaurants` 
                        DROP COLUMN `inhaber`,
                        DROP COLUMN `archiv`,
                        DROP COLUMN `stornocode`,
                        DROP COLUMN `freeUntil`,
                        DROP COLUMN `notifyOpen`,
                        DROP COLUMN `express`,
                        DROP COLUMN `directLink`,
                        DROP COLUMN `pizzabox`,
                        DROP COLUMN `serviette`,
                        DROP COLUMN `bag`,
                        DROP COLUMN `demandssitepay`,
                        DROP COLUMN `demandssite`,
                        DROP COLUMN `demandssiteinfo`,
                        DROP COLUMN `demandssmsprinter`,
                        DROP COLUMN `demandssmsprinterinfo`,
                        DROP COLUMN `investprintingcost`,
                        DROP COLUMN `investprintingcostinfo`;

DROP TABLE department_tree;

