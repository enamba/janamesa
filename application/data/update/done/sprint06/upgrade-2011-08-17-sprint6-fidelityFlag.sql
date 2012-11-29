alter table rabatt add column fidelity tinyint(4) default 0;

update rabatt r set r.fidelity = 1 where r.info = '(Eingelöste Treuepunkte)' OR r.info = 'Treuepunkte einlösen';