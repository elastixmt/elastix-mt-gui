BEGIN TRANSACTION;
CREATE TABLE action_tmp 
(
       name_rpm varchar(20), 
	   action_rpm varchar(20), 
	   data_exp varchar(100), 
	   user varchar(20)
);
COMMIT;
