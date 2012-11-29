
-- @author alex
-- @since 30.03.2011
-- additional field for restaurant

alter table orders add column saleChannelReferrer text CHARACTER SET utf8 COLLATE utf8_general_ci default null;
alter table orders add column saleChannelValue text CHARACTER SET utf8 COLLATE utf8_general_ci default null;
