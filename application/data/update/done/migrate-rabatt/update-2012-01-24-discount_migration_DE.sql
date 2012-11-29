
-- @author alex
-- @since 24.01.12

-- Typ 1, ohne Registrierungscode

insert into rabatt 
(`name`, `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, `referer`, `type`)
(select '5 Euro für Registrierung - discount', `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, 'discount', 1 from rabatt where id=4902);

insert into rabatt 
(`name`, `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, `referer`, `type`)
(select '5 Euro für Registrierung - pizza', `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, 'pizza', 1 from rabatt where id=4902);

insert into rabatt 
(`name`, `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, `referer`, `type`)
(select '5 Euro für Registrierung - discount-tv', `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, 'discount-tv', 1 from rabatt where id=4902);

insert into rabatt 
(`name`, `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, `referer`, `type`)
(select '5 Euro für Registrierung - germanwings', `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, 'germanwings', 1 from rabatt where id=4902);

insert into rabatt 
(`name`, `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, `referer`, `type`)
(select '5 Euro für Registrierung - dp_discount', `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, 'dp_discount', 1 from rabatt where id=4902);


-- typ 1, bereits vorhandene 

update rabatt set referer='studivz.net', type=1 where id=3973;
update rabatt set referer='meinvz.net', type=1 where id=4092;
update rabatt set referer='qype',       type=1 where id=5816;
update rabatt set referer='friendticker', type=1 where id=5818;
update rabatt set referer='netzclub',   type=1 where id=10340;
update rabatt set referer='bigpoint',   type=1 where id=10339;
update rabatt set referer='fb',         type=1 where id=25260;
update rabatt set referer='qypedeals',  type=1 where id=5815;

update `rabatt` set end= '2011-12-31' where id=3973;
update `rabatt` set end= '2011-12-31' where id=4092;

-- typ 3, referer setzen für die vorhandene Aktionen

update rabatt set referer='vs',             type=3 where id=22110;
update rabatt set referer='virtualnights',  type=3 where id=25209;
update rabatt set referer='prinzdeal',      type=3 where id=22350;
update rabatt set referer='post',           type=3 where id=22363;
update rabatt set referer='simfy',          type=3 where id=22672;
update rabatt set referer='filmabend',      type=3 where id=25345;
update rabatt set referer='allmaxx',        type=3 where id=11397;

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`) values ('lieferando2012ax3', 0, 22110);
insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`) values ('vnmyd2012hny',      0, 25209);
insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`) values ('lieferandoprinz',   0, 22350);
insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`) values ('lieferando7e3f',    0, 22363);
insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`) values ('musikmachthungrig', 0, 22672);
insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`) values ('lieferandofa7',     0, 25345);
insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`) values ('a11maxx',           0, 11397);


-- typ 3 erstellt aus der alten 5-Euro Gutsschein Aktion #4902

insert into rabatt 
(`name`, `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, `referer`, `type`)
(select '5 Euro für Registrierung - sparbon', `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, 'sparbon', 3 from rabatt where id=4902);

insert into rabatt 
(`name`, `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, `referer`, `type`)
(select '5 Euro für Registrierung - tamara', `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, 'tamara', 3 from rabatt where id=4902);

insert into rabatt 
(`name`, `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, `referer`, `type`)
(select '5 Euro für Registrierung - technik2go', `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, 'technik2go', 3 from rabatt where id=4902);

insert into rabatt 
(`name`, `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, `referer`, `type`)
(select '5 Euro für Registrierung - ledtrading', `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, 'ledtrading', 3 from rabatt where id=4902);

insert into rabatt 
(`name`, `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, `referer`, `type`)
(select '5 Euro für Registrierung - videoworld', `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, 'videoworld', 3 from rabatt where id=4902);

insert into rabatt 
(`name`, `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, `referer`, `type`)
(select '5 Euro für Registrierung - abigarrado', `rrepeat`, `countUsage`, `kind`, `rabatt`, `status`, `minAmount`, `onlyPrivate`, `onlyCompany`, `onlyRestaurant`, `onlyCustomer`, `onlyPremium`, `onlyCanteen`, `onlyIphone`, `info`, `noCash`, `expirationInfo`, `notStartedInfo`, `start`, `end`, `fidelity`, 'abigarrado', 3 from rabatt where id=4902);

insert into rabatt_codes_verification 
(`registrationCode`, `send`, `rabattId`)
(select 'leckersparbon', 0, id from rabatt where name = '5 Euro für Registrierung - sparbon');

insert into rabatt_codes_verification 
(`registrationCode`, `send`, `rabattId`)
(select 'tamarasessen', 0, id from rabatt where name = '5 Euro für Registrierung - tamara');

insert into rabatt_codes_verification 
(`registrationCode`, `send`, `rabattId`)
(select 'food2eat', 0, id from rabatt where name = '5 Euro für Registrierung - technik2go');

insert into rabatt_codes_verification 
(`registrationCode`, `send`, `rabattId`)
(select 'leckerschmecker', 0, id from rabatt where name = '5 Euro für Registrierung - ledtrading');

insert into rabatt_codes_verification 
(`registrationCode`, `send`, `rabattId`)
(select 'lieferandokr5ms', 0, id from rabatt where name = '5 Euro für Registrierung - videoworld');

insert into rabatt_codes_verification 
(`registrationCode`, `send`, `rabattId`)
(select 'abigarrado5', 0, id from rabatt where name = '5 Euro für Registrierung - abigarrado');



-- Typ 2, mit Registrierungscode

alter table rabatt_codes_verification add column rabattCodeIdTmp INT(11);
alter table rabatt_codes_verification add column rabattRefererTmp VARCHAR(255);

update rabatt set referer='meinfoto', type=2 where id=9743;
update rabatt set referer='dailydeal', type=2 where id=9908;
update rabatt set referer='rebuy', type=2 where id=5817;
update rabatt set referer='promozebra', type=2 where id=10887;
update rabatt set referer='wergehthin', type=2 where id=10966;
update rabatt set referer='gutschein', type=2 where id=16177;
update rabatt set referer='gutscheinregister', type=2 where id=11592;
update rabatt set referer='qdeals', type=2 where id=12029;
update rabatt set referer='sofortueberweisung', type=2 where id=10969;
update rabatt set referer='feuerwehr', type=2 where id=13737;
update rabatt set referer='lan', type=2 where id=14420;
update rabatt set referer='payback', type=2 where id=15683;
update rabatt set referer='o2', type=2 where id=20631;
update rabatt set referer='moviepilot', type=2 where id=16772;
update rabatt set referer='lx', type=2 where id=20974;
update rabatt set referer='eteo', type=2 where id=17216;
update rabatt set referer='videobuster', type=2 where id=17807;
update rabatt set referer='itt', type=2 where id=18711;
update rabatt set referer='dealticket', type=2 where id=18239;
update rabatt set referer='dealgigant', type=2 where id=20294;
update rabatt set referer='o2sms', type=2 where id=24500;
update rabatt set referer='hilfsanta', type=2 where id=24601;
update rabatt set referer='preis24-Gratishandy', type=2 where id=24584;
update rabatt set referer='memarmelade', type=2 where id=25954;
update rabatt set referer='limango', type=2 where id=29407;
update rabatt set referer='rheinmaindeal', type=2 where id=29495;


insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`) 
(select code, 0, rabattId from rabatt_codes where rabattId = 5817 and dailydeal is null and used=1);

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`) 
(select code, 0, rabattId from rabatt_codes where rabattId = 12029 and dailydeal is null and used=1);

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`) 
(select code, 0, rabattId from rabatt_codes where rabattId = 15683 and dailydeal is null and used=1);

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`) 
(select code, 0, rabattId from rabatt_codes where rabattId = 20974 and dailydeal is null and used=1);

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`) 
(select code, 0, rabattId from rabatt_codes where rabattId = 17216 and dailydeal is null and used=1);

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`) 
(select code, 0, rabattId from rabatt_codes where rabattId = 17807 and dailydeal is null and used=1);

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`) 
(select code, 0, rabattId from rabatt_codes where rabattId = 20294 and dailydeal is null and used=1);

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`) 
(select code, 0, rabattId from rabatt_codes where rabattId = 25954 and dailydeal is null and used=1);

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`) 
(select code, 0, rabattId from rabatt_codes where rabattId = 29407 and dailydeal is null and used=1);

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`) 
(select code, 0, rabattId from rabatt_codes where rabattId = 29495 and dailydeal is null and used=1);


insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`, `rabattCodeIdTmp`, `rabattRefererTmp`) 
    (select rc.dailydeal, 1, r.id, rc.id, r.referer 
        from rabatt_codes rc join rabatt r on rc.rabattId=r.id 
            where r.id = 5817 and rc.dailydeal is not null and dailydeal <> 'deaktiviert' 
                group by dailydeal, r.id);

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`, `rabattCodeIdTmp`, `rabattRefererTmp`) 
    (select rc.dailydeal, 1, r.id, rc.id, r.referer 
        from rabatt_codes rc join rabatt r on rc.rabattId=r.id 
            where r.id = 12029 and rc.dailydeal is not null and dailydeal <> 'deaktiviert' 
                group by dailydeal, r.id);

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`, `rabattCodeIdTmp`, `rabattRefererTmp`) 
    (select rc.dailydeal, 1, r.id, rc.id, r.referer 
        from rabatt_codes rc join rabatt r on rc.rabattId=r.id 
            where r.id = 15683 and rc.dailydeal is not null and dailydeal <> 'deaktiviert' 
                group by dailydeal, r.id);

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`, `rabattCodeIdTmp`, `rabattRefererTmp`) 
    (select rc.dailydeal, 1, r.id, rc.id, r.referer 
        from rabatt_codes rc join rabatt r on rc.rabattId=r.id 
            where r.id = 20974 and rc.dailydeal is not null and dailydeal <> 'deaktiviert' 
                group by dailydeal, r.id);


insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`, `rabattCodeIdTmp`, `rabattRefererTmp`) 
    (select rc.dailydeal, 1, r.id, rc.id, r.referer 
        from rabatt_codes rc join rabatt r on rc.rabattId=r.id 
            where r.id = 17216 and rc.dailydeal is not null and dailydeal <> 'deaktiviert' 
                group by dailydeal, r.id);

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`, `rabattCodeIdTmp`, `rabattRefererTmp`) 
    (select rc.dailydeal, 1, r.id, rc.id, r.referer 
        from rabatt_codes rc join rabatt r on rc.rabattId=r.id 
            where r.id = 17807 and rc.dailydeal is not null and dailydeal <> 'deaktiviert' 
                group by dailydeal, r.id);

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`, `rabattCodeIdTmp`, `rabattRefererTmp`) 
    (select rc.dailydeal, 1, r.id, rc.id, r.referer 
        from rabatt_codes rc join rabatt r on rc.rabattId=r.id 
            where r.id = 20294 and rc.dailydeal is not null and dailydeal <> 'deaktiviert' 
                group by dailydeal, r.id);

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`, `rabattCodeIdTmp`, `rabattRefererTmp`) 
    (select rc.dailydeal, 1, r.id, rc.id, r.referer 
        from rabatt_codes rc join rabatt r on rc.rabattId=r.id 
            where r.id = 25954 and rc.dailydeal is not null and dailydeal <> 'deaktiviert' 
                group by dailydeal, r.id);

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`, `rabattCodeIdTmp`, `rabattRefererTmp`) 
    (select rc.dailydeal, 1, r.id, rc.id, r.referer 
        from rabatt_codes rc join rabatt r on rc.rabattId=r.id 
            where r.id = 29407 and rc.dailydeal is not null and dailydeal <> 'deaktiviert' 
                group by dailydeal, r.id);

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`, `rabattCodeIdTmp`, `rabattRefererTmp`) 
    (select rc.dailydeal, 1, r.id, rc.id, r.referer 
        from rabatt_codes rc join rabatt r on rc.rabattId=r.id 
            where r.id = 29495 and rc.dailydeal is not null and dailydeal <> 'deaktiviert' 
                group by dailydeal, r.id);



CREATE INDEX rabattCodeId_index ON rabatt_check(rabattCodeId);
CREATE INDEX rabattCodeId_index ON rabatt_codes_verification(rabattCodeIdTmp);

update rabatt_check rch 
    join rabatt_codes_verification rcv on rch.rabattCodeId=rcv.rabattCodeIdTmp 
        set rch.referer=rcv.rabattRefererTmp, rabattVerificationId=rcv.id 
            where rcv.rabattId = 5817;

update rabatt_check rch 
    join rabatt_codes_verification rcv on rch.rabattCodeId=rcv.rabattCodeIdTmp 
        set rch.referer=rcv.rabattRefererTmp, rabattVerificationId=rcv.id 
            where rcv.rabattId = 12029;

update rabatt_check rch 
    join rabatt_codes_verification rcv on rch.rabattCodeId=rcv.rabattCodeIdTmp 
        set rch.referer=rcv.rabattRefererTmp, rabattVerificationId=rcv.id 
            where rcv.rabattId = 15683;

update rabatt_check rch 
    join rabatt_codes_verification rcv on rch.rabattCodeId=rcv.rabattCodeIdTmp 
        set rch.referer=rcv.rabattRefererTmp, rabattVerificationId=rcv.id 
            where rcv.rabattId = 20974;

update rabatt_check rch 
    join rabatt_codes_verification rcv on rch.rabattCodeId=rcv.rabattCodeIdTmp 
        set rch.referer=rcv.rabattRefererTmp, rabattVerificationId=rcv.id 
            where rcv.rabattId = 17216;

update rabatt_check rch 
    join rabatt_codes_verification rcv on rch.rabattCodeId=rcv.rabattCodeIdTmp 
        set rch.referer=rcv.rabattRefererTmp, rabattVerificationId=rcv.id 
            where rcv.rabattId = 17807;

update rabatt_check rch 
    join rabatt_codes_verification rcv on rch.rabattCodeId=rcv.rabattCodeIdTmp 
        set rch.referer=rcv.rabattRefererTmp, rabattVerificationId=rcv.id 
            where rcv.rabattId = 20294;

update rabatt_check rch 
    join rabatt_codes_verification rcv on rch.rabattCodeId=rcv.rabattCodeIdTmp 
        set rch.referer=rcv.rabattRefererTmp, rabattVerificationId=rcv.id 
            where rcv.rabattId = 25954;

update rabatt_check rch 
    join rabatt_codes_verification rcv on rch.rabattCodeId=rcv.rabattCodeIdTmp 
        set rch.referer=rcv.rabattRefererTmp, rabattVerificationId=rcv.id 
            where rcv.rabattId = 29407;

update rabatt_check rch 
    join rabatt_codes_verification rcv on rch.rabattCodeId=rcv.rabattCodeIdTmp 
        set rch.referer=rcv.rabattRefererTmp, rabattVerificationId=rcv.id 
            where rcv.rabattId = 29495;

