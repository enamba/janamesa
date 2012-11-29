-- @author Allen Frank <frank@lieferando.de>
-- @since 17-04-2012
-- additional field for the storno-response from adyen

ALTER TABLE `adyen_transactions`
    ADD `refundResponse` VARCHAR(255) DEFAULT NULL AFTER `refundedOn`;