alter table restaurants add ktoBank VARCHAR(255) default NULL after ktoNr;
alter table restaurants add ktoAgentur VARCHAR(255) default NULL after ktoBank;
alter table restaurants add ktoDigit VARCHAR(40) default NULL after ktoNr;