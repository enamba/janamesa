-- @author alex
-- @since 24.01.12

-- Typ 1, ohne Registrierungscode

update rabatt set referer='gutschein-at', type=1 where id=9911;
update rabatt set referer='nonvz', type=1 where id=2;

-- Typ 2, mit Registrierungscode


alter table rabatt_codes_verification add column rabattCodeIdTmp INT(11);
alter table rabatt_codes_verification add column rabattRefererTmp VARCHAR(255);

update rabatt set referer='dailydeal', type=2 where id=9908;
update rabatt set referer='teamdeal', type=2 where id=9916;
update rabatt set referer='promozebra', type=2 where id=9919;
update rabatt set referer='deallx', type=2 where id=10080;
    
insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`) 
(select code, 0, rabattId from rabatt_codes where rabattId in 
(9908, 9916, 9919, 10080)
and dailydeal is null and used=1);

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`, `rabattCodeIdTmp`, `rabattRefererTmp`) 
(select rc.dailydeal, 1, r.id, rc.id, r.referer from rabatt_codes rc join rabatt r on rc.rabattId=r.id where r.id in 
(9908, 9916, 9919, 10080)
and rc.dailydeal is not null and dailydeal <> 'deaktiviert' group by dailydeal, r.id);

CREATE INDEX rabattCodeId_index ON rabatt_check(rabattCodeId);
CREATE INDEX rabattCodeId_index ON rabatt_codes_verification(rabattCodeIdTmp);

update rabatt_check rch 
    join rabatt_codes_verification rcv on rch.rabattCodeId=rcv.rabattCodeIdTmp 
        set rch.referer=rcv.rabattRefererTmp, rabattVerificationId=rcv.id 
            where rcv.rabattId in 
            (9908, 9916, 9919, 10080);



