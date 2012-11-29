-- @author mlaug
-- alter the transaction to store more informations
truncate customer_fidelity_transaction;
alter table customer_fidelity_transaction 
    drop column orderId,
    drop column comment,
    drop column time,
    add column created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    add column transactionData TEXT NOT NULL,
    modify column email VARCHAR(255) not null,
    add column status tinyint(4) default 0,
    drop column `transaction`, 
    add column `action` varchar(255) not null, 
    add column points int(11) default 0,
    add index(email);

-- migrate everything
insert into customer_fidelity_transaction (email,created,transactionData,status,action,points) select email,now(),'initiale migration',0,'manual',GREATEST(0,(points*12.5)) from customer_fidelity_points group by email;

-- drop the table, but before we import initial balance
-- drop table customer_fidelity_points;