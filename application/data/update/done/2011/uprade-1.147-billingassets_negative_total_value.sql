-- @author alex
-- @since 14.06.11

alter table billing_assets modify `total` int(10) DEFAULT NULL COMMENT 'Nettobetrag';
alter table billing_assets modify `couriertotal` int(10) DEFAULT NULL COMMENT 'Nettobetrag';
