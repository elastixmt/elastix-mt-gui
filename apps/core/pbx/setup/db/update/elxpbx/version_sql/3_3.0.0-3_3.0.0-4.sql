ALTER TABLE ring_group add column `rg_pickup` enum ('yes','no') default 'no';
ALTER TABLE ring_group modify column `rg_strategy` varchar(50) NOT NULL;

ALTER TABLE sip add column `outofcall_message_context` varchar(100) DEFAULT NULL;
ALTER TABLE sip MODIFY COLUMN `fullcontact` varchar(100) DEFAULT NULL;
ALTER TABLE sip MODIFY COLUMN `defaultuser` varchar(100) DEFAULT NULL;
ALTER TABLE sip MODIFY COLUMN `mohinterpret` varchar(100) DEFAULT NULL;
ALTER TABLE sip MODIFY COLUMN `mohsuggest` varchar(100) DEFAULT NULL;
ALTER TABLE sip MODIFY COLUMN `host` varchar(100) DEFAULT 'dynamic';
ALTER TABLE sip_settings add column `outofcall_message_context` varchar(100) DEFAULT 'im-sip';
ALTER TABLE sip_settings MODIFY COLUMN `defaultuser` varchar(100) DEFAULT NULL;
ALTER TABLE sip_settings MODIFY COLUMN `mohinterpret` varchar(100) DEFAULT NULL;
ALTER TABLE sip_settings MODIFY COLUMN `mohsuggest` varchar(100) DEFAULT NULL;
ALTER TABLE sip_settings MODIFY COLUMN `host` varchar(100) DEFAULT 'dynamic';

insert into sip_general (property_name,property_val,cathegory) values ('accept_outofcall_message','yes','general');
insert into sip_general (property_name,property_val,cathegory) values ('auth_message_requests','yes','general');

ALTER TABLE iax MODIFY COLUMN `mohinterpret` varchar(100) DEFAULT NULL;
ALTER TABLE iax MODIFY COLUMN `mohsuggest` varchar(100) DEFAULT NULL;
ALTER TABLE iax MODIFY COLUMN `host` varchar(100) DEFAULT 'dynamic';
ALTER TABLE iax_settings MODIFY COLUMN `mohinterpret` varchar(100) DEFAULT NULL;
ALTER TABLE iax_settings MODIFY COLUMN `mohsuggest` varchar(100) DEFAULT NULL;
ALTER TABLE iax_settings MODIFY COLUMN `host` varchar(100) DEFAULT 'dynamic';

ALTER TABLE extension MODIFY COLUMN `device` varchar(100) NOT NULL;
ALTER TABLE extension MODIFY COLUMN `context` varchar(100) DEFAULT NULL;
ALTER TABLE extension MODIFY COLUMN `exten` varchar(100) DEFAULT NULL;
ALTER TABLE extension MODIFY COLUMN `voicemail` varchar(100) DEFAULT 'novm';
ALTER TABLE extension ADD COLUMN `enable_chat` enum ('yes','no') default 'no';
ALTER TABLE extension ADD COLUMN `elxweb_device` varchar(100) DEFAULT NULL;
ALTER TABLE extension ADD COLUMN `clid_name` varchar(100) DEFAULT NULL;
ALTER TABLE extension ADD COLUMN `clid_number` varchar(100) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `im` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `display_name` varchar(100) NOT NULL,
      `alias` varchar(100) DEFAULT NULL,
      `device` varchar(100) NOT NULL,
      `id_exten` int(20) DEFAULT NULL, 
      `organization_domain` varchar(100) NOT NULL,
      PRIMARY KEY (`id`),
      FOREIGN KEY (device) REFERENCES sip(name) ON DELETE CASCADE,
      FOREIGN KEY (id_exten) REFERENCES extension(id) ON DELETE CASCADE,
      FOREIGN KEY (organization_domain) REFERENCES organization(domain) ON DELETE CASCADE,
      INDEX organization_domain (organization_domain)
)ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS http_ast (
    id int(11) NOT NULL AUTO_INCREMENT,
    property_name varchar(250),
    property_val varchar(250),
    PRIMARY KEY (id),
    UNIQUE KEY property_name (property_name)
) ENGINE = INNODB;
insert into http_ast (property_name,property_val) values ('enabled','yes');
insert into http_ast (property_name,property_val) values ('bindport','8088');
insert into http_ast (property_name,property_val) values ('bindaddr','0.0.0.0');
insert into http_ast (property_name,property_val) values ('prefix','asterisk');
insert into http_ast (property_name,property_val) values ('tlsenable','yes');
insert into http_ast (property_name,property_val) values ('tlsbindaddr','0.0.0.0');
insert into http_ast (property_name,property_val) values ('tlsbindport','8089');

CREATE TABLE IF NOT EXISTS elx_chat_config (
    id int(11) NOT NULL AUTO_INCREMENT,
    property_name varchar(250),
    property_val varchar(250),
    PRIMARY KEY (id),
    UNIQUE KEY property_name (property_name)
) ENGINE = INNODB;
insert into elx_chat_config (property_name,property_val) values ('type_connection','ws');
insert into elx_chat_config (property_name,property_val) values ('register','yes');
insert into elx_chat_config (property_name,property_val) values ('no_answer_timeout','60');
insert into elx_chat_config (property_name,property_val) values ('register_expires','600');
insert into elx_chat_config (property_name,property_val) values ('trace_sip','no');
insert into elx_chat_config (property_name,property_val) values ('use_preloaded_route','no');
insert into elx_chat_config (property_name,property_val) values ('connection_recovery_min_interval','2');
insert into elx_chat_config (property_name,property_val) values ('connection_recovery_max_interval','2');
insert into elx_chat_config (property_name,property_val) values ('hack_via_tcp','no');
insert into elx_chat_config (property_name,property_val) values ('hack_ip_in_contact','no');
