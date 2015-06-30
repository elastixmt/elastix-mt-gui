ALTER TABLE organization CHANGE COLUMN code code VARCHAR(100) NOT NULL;
ALTER TABLE org_history_register CHANGE COLUMN org_code org_code VARCHAR(100) NOT NULL;
