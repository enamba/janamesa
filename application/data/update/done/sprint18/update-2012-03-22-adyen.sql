drop table if exists adyen_transactions;
create table `adyen_transactions` ( 
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
`orderId` INT UNSIGNED NOT NULL,
`transactionId` VARCHAR(50) NOT NULL,
`reference` VARCHAR(100) DEFAULT NULL,
`redirect` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
`return` TIMESTAMP NULL,
`responseCode` VARCHAR(50) DEFAULT NULL,
`valid` TINYINT(4) DEFAULT 1,
`refunded` TINYINT(4) DEFAULT 0,
`refundedOn` TIMESTAMP NULL
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

alter table `adyen_transactions` add index(`orderId`);