

-- @author alex
-- @since 30.03.2011
-- additional fields for restaurants

alter table restaurants add `ktoSwift` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci default NULL after ktoBlz;
alter table restaurants add `ktoIban`  VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci default NULL after ktoBlz;
