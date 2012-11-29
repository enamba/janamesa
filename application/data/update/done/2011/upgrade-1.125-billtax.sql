alter table billing modify column prov float(15,5) default 0;

alter table billing add column item1Value float(15,5) default 0;
alter table billing add column item2Value float(15,5) default 0;
alter table billing add column tax1Value float(15,5) default 0;
alter table billing add column tax2Value float(15,5) default 0;
alter table billing add column item1Key float(5,3) default 7;
alter table billing add column item2Key float(5,3) default 19;
update billing set item1Value=item7;
update billing set item2Value=item19;
update billing set tax1Value=tax7;
update billing set tax2Value=tax19;

alter table billing drop column kommBrutto;
alter table billing drop column item;
alter table billing drop column tax19;
alter table billing drop column tax7;
alter table billing drop column item19;
alter table billing drop column item7;
alter table billing drop column fee;
alter table billing drop column komm;

alter table billing_sub add column item1Value float(15,5) default 0;
alter table billing_sub add column item2Value float(15,5) default 0;
alter table billing_sub add column tax1Value float(15,5) default 0;
alter table billing_sub add column tax2Value float(15,5) default 0;
alter table billing_sub add column item1Key float(5,3) default 7;
alter table billing_sub add column item2Key float(5,3) default 19;
update billing_sub set item1Value=item7;
update billing_sub set item2Value=item19;
update billing_sub set tax1Value=tax7;
update billing_sub set tax2Value=tax19;
alter table billing_sub drop column tax19;
alter table billing_sub drop column tax7;
alter table billing_sub drop column item19;
alter table billing_sub drop column item7;


