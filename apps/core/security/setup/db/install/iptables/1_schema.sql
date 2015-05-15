CREATE TABLE filter(
    id                  integer       primary key,
    traffic             varchar(15)   not null,
    eth_in              varchar(15)   not null,
    eth_out             varchar(15)   not null,
    ip_source           varchar(50),
    ip_destiny          varchar(50),
    protocol            varchar(10)   not null,
    sport               varchar(20),
    dport               varchar(20),
    icmp_type           varchar(50),
    number_ip           varchar(25),
    target              varchar(15)   not null,
    rule_order          integer       not null,
    activated           integer       not null default 1,
    state               varchar(50) 
);
CREATE TABLE port(
    id                  integer       primary key,
    name                varchar(15)   not null,
    protocol            varchar(7)    not null,
    details             varchar(50)   not null,
    comment             varchar(100)
);
CREATE TABLE tmp_execute(
    id                  integer       primary key,
    exec_in_sys         integer       not null,
    first_time          integer       not null
);

INSERT INTO port(name,protocol,details,comment) VALUES ('HTTP','TCP','80','80');
INSERT INTO port(name,protocol,details,comment) VALUES ('HTTPS','TCP','443','443');
INSERT INTO port(name,protocol,details,comment) VALUES ('POP3','TCP','110','110');
INSERT INTO port(name,protocol,details,comment) VALUES ('IMAPS','TCP','993','993');
INSERT INTO port(name,protocol,details,comment) VALUES ('SSH','TCP','22','22');
INSERT INTO port(name,protocol,details,comment) VALUES ('SMTP','TCP','25','25');
INSERT INTO port(name,protocol,details,comment) VALUES ('POP3S','TCP','995','995');
INSERT INTO port(name,protocol,details,comment) VALUES ('JABBER/XMPP','TCP','5222','5222');
INSERT INTO port(name,protocol,details,comment) VALUES ('OpenFire','TCP','9090','9090');
INSERT INTO port(name,protocol,details,comment) VALUES ('IMAP','TCP','143','143');
INSERT INTO port(name,protocol,details,comment) VALUES ('SIP','UDP','5004:5082','5004:5082');
INSERT INTO port(name,protocol,details,comment) VALUES ('SIP','UDP','10000:20000','10000:20000');
INSERT INTO port(name,protocol,details,comment) VALUES ('MGCP','UDP','2727','2727');
INSERT INTO port(name,protocol,details,comment) VALUES ('IAX','UDP','4569','4569');
INSERT INTO port(name,protocol,details,comment) VALUES ('IAX1','UDP','5036','5036');
INSERT INTO port(name,protocol,details,comment) VALUES ('DNS','UDP','53','53');
INSERT INTO port(name,protocol,details,comment) VALUES ('TFTP','UDP','69','69');

INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('INPUT','lo','','0.0.0.0/0','0.0.0.0/0','ALL','','','','','ACCEPT',1,1);
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','ICMP','','','ANY','','ACCEPT',2,1);
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','UDP','ANY','11','','','ACCEPT',3,1);
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','UDP','ANY','14','','','ACCEPT',4,1);
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','UDP','ANY','15','','','ACCEPT',5,1);
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','UDP','ANY','12','','','ACCEPT',6,1);
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','UDP','ANY','13','','','ACCEPT',7,1);
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','UDP','16','ANY','','','ACCEPT',8,1);
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','UDP','ANY','17','','','ACCEPT',9,1);
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','TCP','ANY','5','','','ACCEPT',10,1);
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','TCP','ANY','6','','','ACCEPT',11,1);
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','TCP','ANY','1','','','ACCEPT',12,1);
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','TCP','ANY','3','','','ACCEPT',13,1);
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','TCP','ANY','10','','','ACCEPT',14,1);
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','TCP','ANY','2','','','ACCEPT',15,1);
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','TCP','ANY','4','','','ACCEPT',16,1);
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','TCP','ANY','7','','','ACCEPT',17,1);
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','TCP','ANY','8','','','ACCEPT',18,1);
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','TCP','ANY','9','','','ACCEPT',19,1);
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated,state) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','STATE','','','','','ACCEPT',20,1,'Established,Related');
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','ALL','','','','','REJECT',21,1);
INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated) VALUES ('FORWARD','ANY','ANY','0.0.0.0/0','0.0.0.0/0','ALL','','','','','REJECT',22,1);

INSERT INTO tmp_execute(exec_in_sys,first_time) VALUES (0,1);
