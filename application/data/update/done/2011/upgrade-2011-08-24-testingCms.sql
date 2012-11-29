
-- Adds a column for prioritisation
-- @author afrank
-- @date 24-08-2011


ALTER TABLE test_case
 ADD COLUMN priority int DEFAULT 100;
UPDATE test_case set priority=2 where title like 'Registration%' and priority=100;
UPDATE test_case set priority=4 where title like 'Login%' and priority=100;
UPDATE test_case set priority=6 where title like 'Ordering%' and priority=100;
UPDATE test_case set priority=8 where title like 'UserSettings%' and priority=100;
UPDATE test_case set priority=12 where title like 'Backend%' and priority=100;
UPDATE test_case set priority=10 where priority=100;
