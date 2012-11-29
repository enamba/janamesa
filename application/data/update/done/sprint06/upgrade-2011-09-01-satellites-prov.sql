alter table restaurants add column kommSat DECIMAL(5,2) default 0;
alter table restaurants add column itemSat INT(4) default 0;
alter table restaurants add column feeSat INT(4) default 0;
update restaurants set kommSat=komm,itemSat=item,feeSat=fee;