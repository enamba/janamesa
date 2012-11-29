drop table customer_fidelity_newsletter;
truncate newsletter_recipients;
alter table newsletter_recipients drop column normal;
alter table newsletter_recipients drop column special;
alter table newsletter_recipients drop column fidelity;
alter table newsletter_recipients add column affirmed tinyint(4) default 0;
alter table newsletter_recipients add column status tinyint(4) default 0;