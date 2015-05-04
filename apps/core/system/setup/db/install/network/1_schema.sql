BEGIN TRANSACTION;

CREATE TABLE dhcp_conf (
        id           INTEGER PRIMARY KEY,
        hostname     varchar(20),
        ipaddress    varchar(20),
        macaddress   varchar(20)
);

COMMIT;
