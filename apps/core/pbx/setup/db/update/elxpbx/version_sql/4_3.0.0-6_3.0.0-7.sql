/* This column is checked by kamailio authentication */
ALTER TABLE sip ADD COLUMN `sippasswd` VARCHAR(80) DEFAULT NULL;
ALTER TABLE sip ADD COLUMN `kamailioname` VARCHAR(80) DEFAULT NULL;

/* Add parameters for Kamailio integration */
INSERT INTO sip_general (property_name,property_val,cathegory) VALUES ('bindaddr','127.0.0.1','general');
INSERT INTO sip_general (property_name,property_val,cathegory) VALUES ('bindport','5080','general');
INSERT INTO sip_general (property_name,property_val,cathegory) VALUES ('outboundproxy','127.0.0.1','general');
INSERT INTO sip_general (property_name,property_val,cathegory) VALUES ('outboundproxyport','5060','general');

/* This table is for announcement module */
CREATE TABLE announcement (
    id                  INT(11)      NOT NULL AUTO_INCREMENT,
    description         VARCHAR(50)  DEFAULT NULL,
    recording_id        INT(11)      DEFAULT NULL,
    allow_skip          ENUM('yes','no')  DEFAULT NULL,
    goto                VARCHAR(50)  NOT NULL, 
    destination         VARCHAR(255) DEFAULT NULL,
    return_ivr          ENUM('yes','no') NOT NULL default 'no',
    noanswer            ENUM('yes','no') NOT NULL default 'no',
    repeat_msg          VARCHAR(2)   NOT NULL default '',
    organization_domain VARCHAR(100) NOT NULL,
    PRIMARY KEY  (id),
    FOREIGN KEY (organization_domain) REFERENCES organization(domain) ON DELETE CASCADE,
    INDEX organization_domain (organization_domain)
) ENGINE = INNODB;

/* The following table stores IPs and domains from which incoming calls from 
 * global SIP trunks should be accepted */
CREATE TABLE global_domains
(
    domain  VARCHAR(100) NOT NULL UNIQUE
);

/* The following view allows Kamailio to authenticate incoming REGISTERs */
CREATE VIEW subscriber AS
(SELECT kamailioname AS username, organization_domain AS domain, sippasswd AS ha1, NULL AS ha1b 
FROM sip
WHERE organization_domain <> '')
UNION
(SELECT kamailioname AS username, domain, sippasswd AS ha1, NULL AS ha1b
FROM sip, organization
WHERE sip.organization_domain = '' AND domain <> '')
UNION
(SELECT kamailioname AS username, domain, sippasswd AS ha1, NULL AS ha1b
FROM sip, global_domains
WHERE sip.organization_domain = '');

/* This table is for shortcut_apps module */
CREATE TABLE shortcut_apps(
  id                  INT(11)      NOT NULL AUTO_INCREMENT,
  description         VARCHAR(50)  DEFAULT NULL,
  exten               VARCHAR(50)  DEFAULT NULL,
  goto                VARCHAR(50)  NOT NULL,
  destination         VARCHAR(255) DEFAULT NULL,
  organization_domain VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (organization_domain) REFERENCES organization(domain) ON DELETE CASCADE,
  INDEX organization_domain (organization_domain)
) ENGINE=INNODB;

/* This table is for shortcut_apps module */
CREATE TABLE other_destinations(
  id                  INT(11)      NOT NULL AUTO_INCREMENT,
  description         VARCHAR(50)  NOT NULL,
  destdial            VARCHAR(100) NOT NULL,
  organization_domain VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (organization_domain) REFERENCES organization(domain) ON DELETE CASCADE,
  INDEX organization_domain (organization_domain)
) ENGINE=INNODB;


/* Updates for using kamailio instead of asterisk for websocket support */
UPDATE http_ast SET property_val = '5060' WHERE property_name = 'bindport';
UPDATE http_ast SET property_val = '5061' WHERE property_name = 'tlsbindport';

/* Added new column ani_prefix, it is used for module ANI */
ALTER TABLE trunk_organization ADD COLUMN ani_prefix VARCHAR(10) NULL;
