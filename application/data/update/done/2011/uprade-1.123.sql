/*
 * default sql script fuer tabellen, bitte alles auskommentieren
 * damit es waehrend eines builds nichts ausgefuehrt wird
 */

-- @author Felix Haferkorn <haferkorn@lieferando.de>
-- @since 25.03.2011
-- additional fields for restaurant

Alter table restaurants add demandssiteinfo  VARCHAR( 255 ) NULL DEFAULT NULL after demandssite;
ALTER TABLE restaurants ADD demandssitepay INT(11) NULL DEFAULT 0 AFTER demandssiteinfo;

Alter table restaurants add demandssmsprinter TINYINT UNSIGNED NULL DEFAULT 0 after demandssiteinfo;
Alter table restaurants add demandssmsprinterinfo  VARCHAR( 255 ) NULL DEFAULT NULL after demandssmsprinter;

Alter table restaurants add investprintingcost TINYINT UNSIGNED NULL DEFAULT 0 after demandssmsprinterinfo;
Alter table restaurants add investprintingcostinfo  VARCHAR( 255 ) NULL DEFAULT NULL after investprintingcost;