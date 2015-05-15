use elxpbx;

CREATE TABLE contacts (
id int(11) NOT NULL AUTO_INCREMENT,
iduser int(11) NOT NULL,
name varchar(35) NOT NULL,
last_name varchar(35) NOT NULL,
work_phone varchar(50)NOT NULL,
cell_phone varchar(50),
home_phone varchar(50),
fax1 varchar(50),
fax2 varchar(50),
email varchar(30),
province varchar(100),
city varchar(100),
address varchar(100),
company varchar(30),
company_contact varchar(100),
contact_rol varchar(50),
notes text,
picture varchar(50),
status varchar(30) DEFAULT 'isPrivate',
PRIMARY KEY (id),
FOREIGN KEY (iduser) REFERENCES acl_user (id) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=latin1;

