-- States of printer
-- @author Alex Vait <vait@lieferando.de>
-- @since 30.08.2012

drop table if exists printer_states;

create table `printer_states` ( 
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
    `state` VARCHAR(255) NOT NULL,
    UNIQUE uqState (`state`)
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

drop table if exists printer_states_history;

create table `printer_states_history` ( 
    `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
    `stateId` INT(11) NOT NULL,
    `printerId` INT(11) NOT NULL,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

insert into printer_states (state) values 
    ('Im Lager - Neu'),
    ('Im Lager - Gebraucht'),
    ('Warten auf Einrichtung'),
    ('Warte auf Retour - Austausch'),
    ('Kein Interesse mehr - Defekt'),
    ('Kein Interesse mehr - Inhaberwechsel'),
    ('Kein Interesse mehr - Gekündigt/Betriebsaufgabe'),
    ('Kein Interesse mehr - Nutzt Fax/E-Mail/API'),
    ('Retour ungeklärt'),
    ('Warte auf Retour - Storno'),
    ('In Reparatur (Gronic)'),
    ('Testdrucker/Showprinter'),
    ('Online');

alter table `printer_topup` add `stateId` INT(11) after `firmware`;


