use elxpbx;

CREATE TABLE IF NOT EXISTS fax_docs
(
    id             integer          NOT NULL AUTO_INCREMENT,
    pdf_file       varchar(255)     NOT NULL DEFAULT '',
    modemdev       varchar(255)     NOT NULL DEFAULT '',
    status         varchar(255)     NOT NULL DEFAULT '',
    commID         varchar(255)     NOT NULL DEFAULT '',
    errormsg       varchar(255)     NOT NULL DEFAULT '',
    company_name   varchar(255)     NOT NULL DEFAULT '',
    company_fax    varchar(255)     NOT NULL DEFAULT '',
    date           timestamp        NOT NULL,
    type           varchar(3)       default 'in',
    faxpath        varchar(255)     default '',
    id_user        integer          not null,
    PRIMARY KEY (id),
    FOREIGN KEY    (id_user) REFERENCES acl_user(id) ON DELETE CASCADE
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS `fax` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `organization_domain` varchar(100) NOT NULL,
      `context` varchar(40) NOT NULL,
      `exten` int(20) NOT NULL,
      `tech` varchar(20) NOT NULL,
      `dial` varchar(40) DEFAULT NULL,
      `device` varchar(40) NOT NULL,
      `rt` varchar(20) DEFAULT NULL,
      `clid_name` varchar(20) DEFAULT NULL,
      `clid_number` varchar(40) DEFAULT NULL, 
      `area_code` varchar(100) DEFAULT NULL,
      `country_code` varchar(100) DEFAULT NULL,
      `port` varchar(100) DEFAULT NULL,
      `dev_id` varchar(100) DEFAULT NULL,
      `fax_content` TEXT DEFAULT NULL,
      `fax_subject` TEXT DEFAULT NULL,
      `notify_email` TEXT DEFAULT NULL,
      PRIMARY KEY (`id`),
      FOREIGN KEY (organization_domain) REFERENCES organization(domain) ON DELETE CASCADE,
      UNIQUE KEY (`dev_id`),
      INDEX organization_domain (organization_domain)
)ENGINE = INNODB;