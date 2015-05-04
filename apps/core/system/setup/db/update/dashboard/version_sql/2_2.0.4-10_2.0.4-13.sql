ALTER TABLE activated_applet_by_user ADD COLUMN username varchar(100);

DELETE FROM activated_applet_by_user;