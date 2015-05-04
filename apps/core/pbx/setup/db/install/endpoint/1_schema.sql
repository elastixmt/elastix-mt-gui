BEGIN TRANSACTION;
CREATE TABLE model
(
       id          integer         primary key,
       name        varchar(255)    not null default '',
       description varchar(255)    not null default '',
       id_vendor   integer         not null,
       foreign key (id_vendor)     references vendor(id)
);
INSERT INTO "model" VALUES(1, '480i', '480i', 1);
INSERT INTO "model" VALUES(2, '480i CT', '480i CT', 1);
INSERT INTO "model" VALUES(3, '9133i', '9133i', 1);
INSERT INTO "model" VALUES(4, '53i', '53i', 1);
INSERT INTO "model" VALUES(5, '55i', '55i', 1);
INSERT INTO "model" VALUES(6, '57i', '57i', 1);
INSERT INTO "model" VALUES(7, '57i CT', '53i CT', 1);
INSERT INTO "model" VALUES(8, '7960', '7960', 2);
INSERT INTO "model" VALUES(9, '7940', '7940', 2);
INSERT INTO "model" VALUES(10, '7970', '7970', 2);
INSERT INTO "model" VALUES(11, '7971', '7971', 2);
INSERT INTO "model" VALUES(12, 'HT386', 'HT386', 3);
INSERT INTO "model" VALUES(13, 'GXP2000', 'GXP2000', 3);
INSERT INTO "model" VALUES(14, 'SPA921', 'SPA921', 4);
INSERT INTO "model" VALUES(15, 'SPA922', 'SPA922', 4);
INSERT INTO "model" VALUES(16, 'SPA941', 'SPA941', 4);
INSERT INTO "model" VALUES(17, 'SPA942', 'SPA942', 4);
INSERT INTO "model" VALUES(18, 'SPA962', 'SPA962', 4);
INSERT INTO "model" VALUES(19, 'IP 301', 'IP 301', 6);
INSERT INTO "model" VALUES(20, 'IP 330/320', 'IP 330/320', 6);
INSERT INTO "model" VALUES(21, 'IP 430', 'IP 430', 6);
INSERT INTO "model" VALUES(22, 'IP 501', 'IP 501', 6);
INSERT INTO "model" VALUES(23, 'IP 601', 'IP 601', 6);
INSERT INTO "model" VALUES(24, 'IP 550', 'IP 550', 6);
INSERT INTO "model" VALUES(25, 'IP 650', 'IP 650', 6);
INSERT INTO "model" VALUES(26, 'SoundStation IP 4000', 'SoundStation IP 4000', 6);
INSERT INTO "model" VALUES(27, '360', '360', 7);
INSERT INTO "model" VALUES(28, '320', '320', 7);
INSERT INTO "model" VALUES(29, 'SPA841', 'SPA841', 4);
INSERT INTO "model" VALUES(30, 'AT 530', 'AT 530', 10);
INSERT INTO "model" VALUES(31, 'AT 320', 'AT 320', 10);
INSERT INTO "model" VALUES(32, 'ST 2030', 'ST 2030', 8);
INSERT INTO "model" VALUES(33, 'ST 2022', 'ST 2022', 8);
INSERT INTO "model" VALUES(34, '7690', '7690', 2);
INSERT INTO "model" VALUES(35, '7961', '7961', 2);
INSERT INTO "model" VALUES(36, '7906', '7906', 2);
INSERT INTO "model" VALUES(37, '7931', '7931', 2);
INSERT INTO "model" VALUES(38, 'VSX7000A', 'VSX7000A', 6);
INSERT INTO "model" VALUES(39, 'SL75 WLAN', 'SL75 WLAN', 11);
INSERT INTO "model" VALUES(40, '9112i', '9112i', 1);
INSERT INTO "model" VALUES(41, '9143i', '9143i', 1);
INSERT INTO "model" VALUES(42, '9480i', '9480i', 1);
INSERT INTO "model" VALUES(43, '9480i CT', '9480i CT', 1);
INSERT INTO "model" VALUES(44, '51i', '51i', 1);
INSERT INTO "model" VALUES(45, '6730i', '6730i', 1);
INSERT INTO "model" VALUES(46, '6731i', '6731i', 1);
INSERT INTO "model" VALUES(47, '6755i', '6755i', 1);
INSERT INTO "model" VALUES(48, 'GXP2020', 'GXP2020', 3);
INSERT INTO "model" VALUES(49, '300', '300', 7);
CREATE TABLE parameter
(
       id          integer         primary key,
       id_endpoint integer         not null,
       name        varchar(255)    not null default '',
       value       varchar(255)    not null default '',
       foreign key (id_endpoint)   references endpoint(id)
);
CREATE TABLE mac
(
       id          integer         primary key,
       id_vendor   integer         not null,
       value       varchar(8)      not null default '--:--:--',
       description varchar(255)    not null default '',
       foreign key (id_vendor)     references vendor(id)
);
INSERT INTO "mac" VALUES(1, 1, '00:08:5D', 'Aastra');
INSERT INTO "mac" VALUES(2, 2, '00:03:6B', 'Cisco');
INSERT INTO "mac" VALUES(3, 2, '00:0D:29', 'Cisco 79xx');
INSERT INTO "mac" VALUES(4, 2, '00:17:0E', 'Cisco 79xx');
INSERT INTO "mac" VALUES(5, 3, '00:0B:82', 'Grandstream');
INSERT INTO "mac" VALUES(6, 4, '00:0E:08', 'Linksys/Sipura');
INSERT INTO "mac" VALUES(7, 5, '00:D0:1E', 'Pingtel - Generic');
INSERT INTO "mac" VALUES(8, 6, '00:04:F2', 'Polycom');
INSERT INTO "mac" VALUES(9, 7, '00:04:13', 'Snom360');
INSERT INTO "mac" VALUES(10, 8, '00:0E:50', 'Thomson - Generic');
INSERT INTO "mac" VALUES(11, 9, '00:E0:11', 'Uniden - Generic');
INSERT INTO "mac" VALUES(12, 10, '00:09:45', 'Atcom - Palmmicro Communication');
INSERT INTO "mac" VALUES(13, 8, '00:14:7F', 'Thomson - ST 2030');
INSERT INTO "mac" VALUES(14, 8, '00:18:F6', 'Thomson - ST 2022 ');
INSERT INTO "mac" VALUES(15, 2, '00:12:7F', 'Cisco - 7690 ');
INSERT INTO "mac" VALUES(16, 2, '00:12:43', 'Cisco - 7940 ');
INSERT INTO "mac" VALUES(17, 2, '00:1A:6D', 'Cisco - 7961 ');
INSERT INTO "mac" VALUES(18, 2, '00:1A:A1', 'Cisco - 7961 ');
INSERT INTO "mac" VALUES(19, 2, '00:21:55', 'Cisco - 7961 ');
INSERT INTO "mac" VALUES(20, 2, '00:1E:4A', 'Cisco  7906-7970 ');
INSERT INTO "mac" VALUES(21, 2, '00:1B:53', 'Cisco - 7931');
INSERT INTO "mac" VALUES(22, 2, '00:0D:ED', 'Cisco - 7960');
INSERT INTO "mac" VALUES(23, 2, '00:0F:23', 'Cisco - 7960');
INSERT INTO "mac" VALUES(24, 2, '00:0E:38', 'Cisco - 7960');
INSERT INTO "mac" VALUES(25, 2, '00:15:FA', 'Cisco - 7960');
INSERT INTO "mac" VALUES(26, 2, '00:19:AA', 'Cisco - 7940');
INSERT INTO "mac" VALUES(27, 2, '00:18:18', 'Cisco - 7940');
INSERT INTO "mac" VALUES(28, 2, '00:13:19', 'Cisco - 7940');
INSERT INTO "mac" VALUES(29, 2, '00:07:EB', 'Cisco - 7940');
INSERT INTO "mac" VALUES(30, 2, '00:0B:5F', 'Cisco - 7960');
INSERT INTO "mac" VALUES(31, 2, '00:13:C3', 'Cisco - 7960');
INSERT INTO "mac" VALUES(32, 2, '00:19:E7', 'Cisco - 7940');
INSERT INTO "mac" VALUES(33, 2, '00:18:73', 'Cisco - 7940');
INSERT INTO "mac" VALUES(34, 2, '00:12:00', 'Cisco - 7940');
INSERT INTO "mac" VALUES(35, 2, '00:14:A9', 'Cisco - 7940');
INSERT INTO "mac" VALUES(36, 6, '00:E0:DB', 'Polycom - VSX7000A');
INSERT INTO "mac" VALUES(37, 2, '00:16:46', 'Cisco - 7940');
INSERT INTO "mac" VALUES(38, 11, '00:01:E3', 'Siemens - SL75 WLAN');
INSERT INTO "mac" VALUES(39, 7, '00:04:23', 'Snom360');
INSERT INTO "mac" VALUES(40, 7, '00:04:25', 'Snom300');
INSERT INTO "mac" VALUES(41, 7, '00:04:27', 'Snom320');
CREATE TABLE vendor
(
       id          integer         primary key,
       name        varchar(255)    not null default '',
       description varchar(255)    not null default '',
       script      text
);
INSERT INTO "vendor" VALUES(1, 'Aastra', 'Aastra', '');
INSERT INTO "vendor" VALUES(2, 'Cisco', 'Cisco', '');
INSERT INTO "vendor" VALUES(3, 'Grandstream', 'Grandstream', '');
INSERT INTO "vendor" VALUES(4, 'Linksys', 'Linksys/Sipura', '');
INSERT INTO "vendor" VALUES(5, 'Pingtel', 'Generic', '');
INSERT INTO "vendor" VALUES(6, 'Polycom', 'Polycom', '');
INSERT INTO "vendor" VALUES(7, 'Snom', 'Snom360', '');
INSERT INTO "vendor" VALUES(8, 'Thomson', 'Generic', '');
INSERT INTO "vendor" VALUES(9, 'Uniden', 'Generic', '');
INSERT INTO "vendor" VALUES(10, 'Atcom', 'IP Phone', '');
INSERT INTO "vendor" VALUES(11, 'Siemens', 'Siemens', NULL);
CREATE TABLE endpoint(
       id          integer         primary key,
       id_device   varchar(255)    not null default '',
       desc_device varchar(255)    not null default '',
       account     varchar(255)    not null default '',
       secret      varchar(255)    not null default '',
       id_model    integer         not null,
       mac_adress  varchar(17)     not null default '--:--:--:--:--:--',
       id_vendor   integer         not null,
       edit_date   timestamp       not null,
       comment     varchar(255),
       foreign key (id_model)      references model(id), 
       foreign key (id_vendor)     references vendor(id) 
);
COMMIT;
