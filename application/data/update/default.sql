--
-- default sql script fuer tabellen, bitte alles auskommentieren
-- damit es waehrend eines builds nichts ausgefuehrt wird


-- the default collation MUST be set adding a varchar, char or anything text like
-- or if we create a table
-- CHARACTER SET utf8 COLLATE utf8_general_ci

-- e.g. alter table orders add column searchTerm varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci default null;

-- create table `test` ( 
-- `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
-- `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
-- ) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
 
-- bitte folgende Namens-Konventionen für den ScriptNamen einhalten !!!!!!
-- <YYYY-MM-DD>-<TOPIC>-<Ticket-Nr>.sql