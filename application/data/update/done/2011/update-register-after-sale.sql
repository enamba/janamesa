/*
 * a marker to retain the information, that this customer has registered
 * after placing this order
 */


ALTER table `orders` ADD COLUMN registeredAfterSale TINYINT(4) DEFAULT 0;