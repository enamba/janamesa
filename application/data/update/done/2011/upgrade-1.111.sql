/**
* Database upgrade v1.111
* @author alex
* @since 10.02.2011
*/

alter table billing_customized add column preamble VARCHAR(255) DEFAULT 'Für die Vermittlung von Speisen und Getränken' AFTER content ;
alter table billing_customized_single add column preamble VARCHAR(255) DEFAULT 'Für die Vermittlung von Speisen und Getränken' AFTER content;
