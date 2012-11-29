/**
* Database upgrade v1.105
* @author mlaug
* @since 31.01.2011
*/

alter table billing add column ebanking INT DEFAULT 0;
