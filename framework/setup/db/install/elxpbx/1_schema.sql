
-- Create da-- Create database       
CREATE DATABASE IF NOT EXISTS elxpbx;   
USE elxpbx;     
-- Database: `elxpbx`
   
-- Create user db
GRANT SELECT, UPDATE, INSERT, DELETE ON `elxpbx`.* to asteriskuser@localhost;

CREATE TABLE IF NOT EXISTS organization
(
    id                INTEGER  NOT NULL AUTO_INCREMENT,
    name              VARCHAR(150) NOT NULL,
    domain            VARCHAR(100) NOT NULL,
    email_contact     VARCHAR(100),
    country           VARCHAR(100) NOT NULL,
    city              VARCHAR(150) NOT NULL,
    address           VARCHAR(255),
    -- codigo de la organizacion usado en asterisk como identificador
    code              VARCHAR(20) NOT NULL, 
    -- codigo unico de la orgnizacion usado para identificarla de manera      unica dentro del sistema
    idcode            VARCHAR(50) NOT NULL,   
    state             VARCHAR(20) DEFAULT "active",  
    PRIMARY KEY (id),
    UNIQUE INDEX domain (domain),
    UNIQUE INDEX code (code),
    UNIQUE INDEX idcode (idcode)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS organization_properties
(
    id_organization   INTEGER     NOT NULL AUTO_INCREMENT, 
    property          VARCHAR(100) NOT NULL,
    value             TEXT NOT NULL,
    category          VARCHAR(50),
    PRIMARY KEY (id_organization,property),
    FOREIGN KEY (id_organization) REFERENCES organization(id)  ON DELETE CASCADE
) ENGINE = INNODB;

-- tabla org_email_template contiene
-- los parametros usados en el envio
-- de un mail a las organizaciones desde
-- el servidor elastix al momento de 
-- crear, eleminar o suspender 
-- una organizacion
CREATE TABLE IF NOT EXISTS org_email_template(
    from_email varchar(250) NOT NULL,
    from_name varchar(250) NOT NULL,
    subject varchar(250) NOT NULL,
    content TEXT NOT NULL,
    host_ip varchar(250) default "",
    host_domain varchar(250) default "",
    host_name varchar(250) default "",
    category varchar(250) NOT NULL,
    PRIMARY KEY (category)
) ENGINE = INNODB;

insert into org_email_template (from_email,from_name,subject,content,category) values("elastix@example.com","Elastix Admin","Create Company in Elastix Server",'Welcome to Elastix Server.<br>Your company {COMPANY_NAME} with domain {DOMAIN} has been created.<br>To start to configurate you elastix server go to {HOST_IP} and login into elastix as:<br>Username: admin@{DOMAIN}<br>Password: {USER_PASSWORD}',"create");
insert into org_email_template (from_email,from_name,subject,content,category) values("elastix@example.com","Elastix Admin","Deleted Company in Elastix Server","","delete");
insert into org_email_template (from_email,from_name,subject,content,category) values("elastix@example.com","Elastix Admin","Suspended Company in Elastix Server","","suspend");

-- tabla creada con propositos de auditoria que guarda las acciones tomadas
-- con respecto a una organizacion dentro del sistema
-- entiendese por acciones el crear, suspender, reactivar o eliminar una organizacion del sistema
CREATE TABLE IF NOT EXISTS org_history_events(
    id INTEGER  NOT NULL AUTO_INCREMENT,
    -- create,suspend,unsuspend,delete,
    event varchar(100) NOT NULL,
    -- codigo unico generado  para la organizacion 
    -- este codigo no se puede repetir dentro del sistema
    org_idcode VARCHAR(50),
    -- fecha en que ocurrio el evento
    event_date DATETIME NOT NULL,
    PRIMARY KEY (id)
) ENGINE = INNODB;

-- esta tabla contiene informacion de todas las organizaciones creadas en algun
-- momento dentro del sistema
CREATE TABLE IF NOT EXISTS org_history_register(
    id INTEGER  NOT NULL AUTO_INCREMENT,
    org_domain VARCHAR(100) NOT NULL, 
    org_code VARCHAR(20) NOT NULL, 
    -- codigo unico generado  para la organizacion 
    -- este codigo no se puede repetir dentro del sistema
    org_idcode VARCHAR(50) NOT NULL,
    -- fecha en que ocurrio el evento
    create_date DATETIME NOT NULL,
    delete_date DATETIME default NULL,
    PRIMARY KEY (id),
    UNIQUE INDEX orgIdcode (org_idcode)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS acl_resource
(
    id varchar(50) NOT NULL, -- menuid , es el unico identficador del recurso
    description varchar(100),
    IdParent varchar(50),
    Link varchar(250),
    Type varchar(20),
    order_no INTEGER,
    administrative enum('yes','no') default 'yes',
    organization_access enum('yes','no') default 'yes',
    PRIMARY KEY (id)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS organization_resource
(
    id INTEGER NOT NULL AUTO_INCREMENT,
    id_organization INTEGER NOT NULL,
    id_resource varchar(50) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE INDEX permission_org (id_organization,id_resource),
    FOREIGN KEY (id_organization) REFERENCES organization(id) ON DELETE CASCADE,
    FOREIGN KEY (id_resource) REFERENCES acl_resource(id) ON DELETE CASCADE
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS acl_group
(
    id INTEGER NOT NULL AUTO_INCREMENT ,
    name VARCHAR(200),
    description TEXT,
    id_organization INTEGER NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (id_organization) REFERENCES organization(id) ON DELETE CASCADE,
    UNIQUE INDEX name_group (id_organization,name)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS resource_action
(
    id INTEGER NOT NULL AUTO_INCREMENT,
    id_resource varchar(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (id_resource) REFERENCES acl_resource(id) ON DELETE CASCADE,
    UNIQUE INDEX resource_action (id_resource,action)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS group_resource_action
(
    id INTEGER NOT NULL AUTO_INCREMENT,
    id_group INTEGER NOT NULL,
    id_resource_action INTEGER NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (id_group) REFERENCES acl_group(id) ON DELETE CASCADE,
    FOREIGN KEY (id_resource_action) REFERENCES resource_action(id) ON DELETE CASCADE,
    UNIQUE INDEX permission_group (id_group,id_resource_action)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS acl_user
(
    id INTEGER NOT NULL AUTO_INCREMENT,
    username VARCHAR(150) NOT NULL,
    name VARCHAR(150),
    md5_password VARCHAR(100) NOT NULL,
    id_group INTEGER NOT NULL,
    extension VARCHAR(20),
    fax_extension VARCHAR(20),
    `picture_content` mediumblob,
    `picture_type` varchar(50) DEFAULT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (id_group) REFERENCES acl_group(id) ON DELETE CASCADE,
    UNIQUE INDEX username (username)
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS user_resource_action
(
    id INTEGER NOT NULL AUTO_INCREMENT,
    id_user INTEGER NOT NULL,
    id_resource_action INTEGER NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (id_user) REFERENCES acl_user(id) ON DELETE CASCADE,
    FOREIGN KEY (id_resource_action) REFERENCES resource_action(id) ON DELETE CASCADE,
    UNIQUE INDEX permission_user (id_user,id_resource_action)
) ENGINE = INNODB;


CREATE TABLE IF NOT EXISTS user_shortcut
(
    id           INTEGER     NOT NULL AUTO_INCREMENT ,
    id_user      INTEGER     NOT NULL,
    id_resource  varchar(50) NOT NULL,
    type         VARCHAR(50) NOT NULL,
    description  VARCHAR(50),
    PRIMARY KEY (id),
    FOREIGN KEY (id_user) REFERENCES acl_user(id) ON DELETE CASCADE,
    FOREIGN KEY (id_resource) REFERENCES acl_resource(id)  ON DELETE CASCADE
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS sticky_note
(
    id           INTEGER     NOT NULL AUTO_INCREMENT ,
    id_user      INTEGER     NOT NULL,
    id_resource  varchar(50) NOT NULL,
    date_edit    DATETIME    NOT NULL,
    description  TEXT,
    auto_popup   INTEGER NOT NULL DEFAULT '0',
    PRIMARY KEY (id),
    FOREIGN KEY (id_user) REFERENCES acl_user(id) ON DELETE CASCADE,
    FOREIGN KEY (id_resource) REFERENCES acl_resource(id)  ON DELETE CASCADE
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS user_properties
(
    id_user   INTEGER     NOT NULL AUTO_INCREMENT,
    property     VARCHAR(100) NOT NULL,
    value        TEXT NOT NULL,
    category     VARCHAR(50),
    PRIMARY KEY (id_user,property,category),
    FOREIGN KEY (id_user) REFERENCES acl_user(id) ON DELETE CASCADE
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS settings
(
    property               varchar(32) NOT NULL,
    value             varchar(32) NOT NULL,
    PRIMARY KEY (property)
) ENGINE = INNODB;

INSERT INTO settings VALUES('elastix_version_release', '3.0.0-alpha3');
INSERT INTO settings VALUES('fax_master', 'elastix@example.org');

INSERT INTO organization VALUES(1,'NONE','','','','','','','','active');
INSERT INTO organization_properties VALUES(1,'language','en','system');
INSERT INTO organization_properties VALUES(1,'default_rate',0.50,'system');
INSERT INTO organization_properties VALUES(1,'default_rate_offset',1,'system');
INSERT INTO organization_properties VALUES(1,'currency','$','system');
INSERT INTO organization_properties VALUES(1,'theme','elastixneo','system');
-- properties used for fax settings
INSERT INTO organization_properties VALUES(1,'fax_remite','fax@faxelastix.com','fax');
INSERT INTO organization_properties VALUES(1,'fax_remitente','Fax Elastix','fax');
INSERT INTO organization_properties VALUES(1,'fax_subject','Fax attached (ID: {NAME_PDF})','fax');
INSERT INTO organization_properties VALUES(1,'fax_content','Fax sent from {FAX_CID_NAME}. The phone number is {FAX_CID_NUMBER}. <br> This email has a fax attached with ID {NAME_PDF}.','fax');

INSERT INTO acl_group VALUES( 1,'superadmin','super elastix admin',1);
INSERT INTO acl_group VALUES( 2,'administrator','Administrator',1);
INSERT INTO acl_group VALUES( 3,'supervisor','Supervisor',1);
INSERT INTO acl_group VALUES( 4,'end_user','End User',1);
INSERT INTO acl_user (id,username,name,md5_password,id_group) VALUES(1,'admin','admin','7a5210c173ea40c03205a5de7dcd4cb0',1);