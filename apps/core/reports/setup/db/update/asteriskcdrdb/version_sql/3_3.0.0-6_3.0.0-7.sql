CREATE TABLE queue_log (
  id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  time datetime default NULL,
  callid varchar(32) NOT NULL default '',
  queuename varchar(32) NOT NULL default '',
  agent varchar(32) NOT NULL default '',
  event varchar(32) NOT NULL default '',
  data1 varchar(100) NOT NULL default '',
  data2 varchar(100) NOT NULL default '',
  data3 varchar(100) NOT NULL default '',
  data4 varchar(100) NOT NULL default '',
  data5 varchar(100) NOT NULL default '',
  PRIMARY KEY (`id`)
);

