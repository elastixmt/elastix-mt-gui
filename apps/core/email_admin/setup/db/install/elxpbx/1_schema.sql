USE elxpbx; 

CREATE TABLE IF NOT EXISTS email_list
(
    id                       INTEGER AUTO_INCREMENT,
    organization_domain      VARCHAR(100) NOT NULL,
    listname                 VARCHAR(50),
    password                 VARCHAR(15),
    mailadmin                VARCHAR(150),
    PRIMARY KEY (id),
    UNIQUE INDEX listname (listname,organization_domain),
    INDEX organization_domain (organization_domain),
    FOREIGN KEY (organization_domain) REFERENCES organization(domain) ON DELETE CASCADE
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS member_list
(
    id              INTEGER NOT null AUTO_INCREMENT ,
    mailmember      VARCHAR(150),
    id_emaillist    INTEGER,
    namemember      VARCHAR(50),
    PRIMARY KEY (id),
    FOREIGN KEY(id_emaillist) REFERENCES email_list(id) ON DELETE CASCADE
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS vacations (
    id int(11) NOT NULL AUTO_INCREMENT,
    id_user int(11) NOT NULL,
    id_recording int(10) unsigned NULL,
    email_subject varchar(150) NOT NULL,
    email_body text,
    init_date DATE,
    end_date DATE,
    vacation varchar(5) DEFAULT 'no',
    PRIMARY KEY (id),
    FOREIGN KEY (id_user) REFERENCES acl_user (id) ON DELETE CASCADE, 
    FOREIGN KEY (id_recording) REFERENCES recordings (uniqueid) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS email_statistics(
    id                       integer not null AUTO_INCREMENT,
    date                     datetime,
    unix_time                integer,
    total                    integer,
    type                     integer,
    organization_domain varchar(100) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (organization_domain) REFERENCES organization(domain) ON DELETE CASCADE
) ENGINE = INNODB;

CREATE TABLE IF NOT EXISTS email_relay(
    name varchar(150) NOT NULL,
    value varchar(150),
    PRIMARY KEY (name)
) ENGINE = INNODB;
