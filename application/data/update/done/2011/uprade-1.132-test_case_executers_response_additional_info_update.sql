
-- @author afrank
-- @since 17.05.11
-- @description add a field to test_case_executors_response,
-- which will contain informations about the browser, os, etc ($_SERVER['HTTP_USER_AGENT'])


ALTER TABLE test_case_executors_response ADD additionalInfo VARCHAR(255);