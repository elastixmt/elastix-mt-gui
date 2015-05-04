BEGIN TRANSACTION;

CREATE TABLE serverFTP (
      id               integer       primary key    not null,
      server           varchar(20),
      port             integer,
      user             varchar(20),
      password         varchar(20),
      pathServer       varchar(20)
);

CREATE TABLE automatic_backup(
       id             integer     primary key,
       status         varchar(255)
);

COMMIT;