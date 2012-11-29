-- Database upgrade v1.94
-- @author mlaug
-- @since 11.01.2011

truncate table `mining_clickstream`;
ALTER TABLE `mining_clickstream` add column data TEXT DEFAULT NULL;