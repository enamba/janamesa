-- @author Allen Frank
-- @since 23.01.2012
-- additional column for tags

ALTER TABLE test_case ADD COLUMN tag varchar(255);
ALTER TABLE test_case DROP COLUMN videoPath;