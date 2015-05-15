BEGIN TRANSACTION;
CREATE TABLE addons(
       id           integer     primary key,
       name         varchar(20),
       name_rpm     varchar(100),
       version      varchar(20),
       release      varchar(20),
       developed_by varchar(100),
       update_st    int(1) default 0
);
COMMIT;
