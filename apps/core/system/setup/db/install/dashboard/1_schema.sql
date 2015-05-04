BEGIN TRANSACTION;

CREATE TABLE default_applet_by_user(
      id         integer        primary key,
      id_applet  integer        not null,
      username   varchar(100)   not null,
      foreign key(id_applet)    references applet(id)
);
INSERT INTO "default_applet_by_user" VALUES(1, 1, 'admin');
INSERT INTO "default_applet_by_user" VALUES(2, 2, 'admin');
INSERT INTO "default_applet_by_user" VALUES(3, 3, 'admin');
INSERT INTO "default_applet_by_user" VALUES(4, 4, 'admin');
INSERT INTO "default_applet_by_user" VALUES(5, 5, 'admin');
INSERT INTO "default_applet_by_user" VALUES(6, 6, 'admin');
INSERT INTO "default_applet_by_user" VALUES(7, 7, 'admin');
INSERT INTO "default_applet_by_user" VALUES(8, 8, 'admin');
INSERT INTO "default_applet_by_user" VALUES(9, 9, 'admin');
INSERT INTO "default_applet_by_user" VALUES(10, 10, 'admin');
INSERT INTO "default_applet_by_user" VALUES(11, 11, 'admin');
INSERT INTO "default_applet_by_user" VALUES(12, 12, 'admin');
INSERT INTO "default_applet_by_user" VALUES(13, 5, 'no_admin');
INSERT INTO "default_applet_by_user" VALUES(14, 7, 'no_admin');
INSERT INTO "default_applet_by_user" VALUES(15, 8, 'no_admin');
INSERT INTO "default_applet_by_user" VALUES(16, 9, 'no_admin');
INSERT INTO "default_applet_by_user" VALUES(17, 10, 'no_admin');
INSERT INTO "default_applet_by_user" VALUES(18, 11, 'no_admin');
INSERT INTO "default_applet_by_user" VALUES(19, 13, 'admin');


CREATE TABLE applet(
      id         integer        primary key,
      code       varchar(100)   not null,
      name       varchar(100)
);
INSERT INTO "applet" VALUES(1, 'sys_resource', 'System Resources');
INSERT INTO "applet" VALUES(2, 'process_status', 'Processes Status');
INSERT INTO "applet" VALUES(3, 'hard_drivers', 'Hard Drivers');
INSERT INTO "applet" VALUES(4, 'performance', 'Performance Graphic');
INSERT INTO "applet" VALUES(5, 'news', 'News');
INSERT INTO "applet" VALUES(6, 'communicationActivity', 'Communication Activity');
INSERT INTO "applet" VALUES(7, 'calendar', 'Calendar');
INSERT INTO "applet" VALUES(8, 'asterisk_calls', 'Calls');
INSERT INTO "applet" VALUES(9, 'emails', 'Emails');
INSERT INTO "applet" VALUES(10, 'faxes', 'Faxes');
INSERT INTO "applet" VALUES(11, 'voicemails', 'Voicemails');
INSERT INTO "applet" VALUES(12, 'system', 'System');
INSERT INTO "applet" VALUES(13, 'telephony_hardware', 'Telephony Hardware');


CREATE TABLE activated_applet_by_user(
      id         integer        primary key,
      id_dabu    integer        not null,
      order_no   integer,
      foreign key(id_dabu)      references default_applet_by_user(id)
);
INSERT INTO "activated_applet_by_user" VALUES(28, 13, 1);
INSERT INTO "activated_applet_by_user" VALUES(29, 14, 2);
INSERT INTO "activated_applet_by_user" VALUES(30, 15, 3);
INSERT INTO "activated_applet_by_user" VALUES(31, 16, 4);
INSERT INTO "activated_applet_by_user" VALUES(32, 17, 5);
INSERT INTO "activated_applet_by_user" VALUES(33, 18, 6);
INSERT INTO "activated_applet_by_user" VALUES(34, 1, 1);
INSERT INTO "activated_applet_by_user" VALUES(35, 2, 2);
INSERT INTO "activated_applet_by_user" VALUES(36, 3, 3);
INSERT INTO "activated_applet_by_user" VALUES(37, 4, 4);
INSERT INTO "activated_applet_by_user" VALUES(38, 5, 5);

COMMIT;
