-- @author allen
-- extended rabatt_codes_videobuster to rabatt_codes_external_codes for no more branding

RENAME TABLE rabatt_codes_videobuster TO rabatt_codes_external_mailing;
ALTER TABLE rabatt_codes_external_mailing 
 ADD COLUMN campaign VARCHAR(255) NOT NULL DEFAULT 'undefined';
UPDATE rabatt_codes_external_mailing SET campaign='videobuster';