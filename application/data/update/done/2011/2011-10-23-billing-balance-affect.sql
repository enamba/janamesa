alter table billing_balance add column affects TIMESTAMP DEFAULT 0;
update billing_balance set affects=created;