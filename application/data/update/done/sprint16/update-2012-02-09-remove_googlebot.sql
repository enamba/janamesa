-- @author alex
-- @since 09.02.12

ALTER TABLE `links` DROP `googlebot`;
ALTER TABLE `restaurants` DROP `metaGooglebot`;

-- Test:
-- SELECT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'lieferando.de' AND COLUMN_NAME like '%googlebot%'