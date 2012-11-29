

-- @author alex
-- @since 07.06.2012
-- additional field for restaurants

alter table restaurants add `ustIdNr` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci default NULL after ktoSwift;
