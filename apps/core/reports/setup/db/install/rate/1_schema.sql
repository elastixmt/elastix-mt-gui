BEGIN TRANSACTION;
CREATE TABLE rate (
       id               INTEGER PRIMARY KEY, 
       name             varchar(200), 
       prefix           varchar(50), 
       rate             float, 
       rate_offset      float, 
       trunk            TEXT
);
COMMIT;
