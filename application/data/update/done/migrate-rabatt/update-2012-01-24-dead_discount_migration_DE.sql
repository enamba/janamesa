
-- @author alex
-- @since 30.01.12

-- Typ 2, mit Registrierungscode, abgelaufene Rabattaktionen

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`) 
(select code, 0, rabattId from rabatt_codes where rabattId in 
(9743, 9908, 10887, 10966, 16177, 11592, 10969, 13737, 14420, 20631, 16772, 18711, 18239, 24500, 24601, 24584)
and dailydeal is null and used=1);

insert into rabatt_codes_verification (`registrationCode`, `send`, `rabattId`, `rabattCodeIdTmp`, `rabattRefererTmp`) 
(select rc.dailydeal, 1, r.id, rc.id, r.referer from rabatt_codes rc join rabatt r on rc.rabattId=r.id where r.id in 
(9743, 9908, 10887, 10966, 16177, 11592, 10969, 13737, 14420, 20631, 16772, 18711, 18239, 24500, 24601, 24584)
and rc.dailydeal is not null and dailydeal <> 'deaktiviert' group by dailydeal, r.id);

CREATE INDEX rabattCodeId_index ON rabatt_check(rabattCodeId);
CREATE INDEX rabattCodeId_index ON rabatt_codes_verification(rabattCodeIdTmp);

update rabatt_check rch 
    join rabatt_codes_verification rcv on rch.rabattCodeId=rcv.rabattCodeIdTmp 
        set rch.referer=rcv.rabattRefererTmp, rabattVerificationId=rcv.id 
            where rcv.rabattId in 
            (9743, 9908, 10887, 10966, 16177, 11592, 10969, 13737, 14420, 20631, 16772, 18711, 18239, 24500, 24601, 24584);


