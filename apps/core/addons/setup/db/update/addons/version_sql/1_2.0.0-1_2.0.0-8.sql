BEGIN TRANSACTION;
CREATE TABLE addons_cache
(
       name_rpm         varchar(20),
       status           int,
       observation      varchar(100)
);
COMMIT;
