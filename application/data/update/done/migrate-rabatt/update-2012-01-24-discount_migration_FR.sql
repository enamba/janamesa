-- @author alex
-- @since 24.01.12

-- Typ 1, ohne Registrierungscode

update rabatt set referer='nouveau-client', type=1 where id=3848;


-- Typ 2, mit Registrierungscode


alter table rabatt_codes_verification add column rabattCodeIdTmp INT(11);
alter table rabatt_codes_verification add column rabattRefererTmp VARCHAR(255);

update rabatt set referer='groupon-fr', type=2 where id=3826;
update rabatt set referer='igraal', type=2 where id=3899;
update rabatt set referer='tribalista', type=2 where id=3908;
update rabatt set referer='envies-en-ville', type=2 where id=3909;
    
insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`) 
(select code, 0, rabattId from rabatt_codes where rabattId in 
(3826, 3899, 3908, 3909)
and dailydeal is null and used=1);

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`, `rabattCodeIdTmp`, `rabattRefererTmp`) 
(select rc.dailydeal, 1, r.id, rc.id, r.referer from rabatt_codes rc join rabatt r on rc.rabattId=r.id where r.id in 
(3826, 3899, 3908, 3909)
and rc.dailydeal is not null and dailydeal <> 'deaktiviert' group by dailydeal, r.id);

CREATE INDEX rabattCodeId_index ON rabatt_check(rabattCodeId);
CREATE INDEX rabattCodeId_index ON rabatt_codes_verification(rabattCodeIdTmp);

update rabatt_check rch 
    join rabatt_codes_verification rcv on rch.rabattCodeId=rcv.rabattCodeIdTmp 
        set rch.referer=rcv.rabattRefererTmp, rabattVerificationId=rcv.id 
            where rcv.rabattId in 
            (3826, 3899, 3908, 3909);




