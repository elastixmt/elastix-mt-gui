USE asteriskcdrdb;

ALTER TABLE asteriskcdrdb.cdr add column intraforward varchar(250) default NULL;
ALTER TABLE asteriskcdrdb.cdr add column fromout enum('1','0') default '0';
ALTER TABLE asteriskcdrdb.cdr add column toout enum('1','0') default '0';
ALTER TABLE asteriskcdrdb.cdr add column organization_domain varchar(100) default NULL;
Alter table asteriskcdrdb.cdr ADD INDEX organization_domain (organization_domain);