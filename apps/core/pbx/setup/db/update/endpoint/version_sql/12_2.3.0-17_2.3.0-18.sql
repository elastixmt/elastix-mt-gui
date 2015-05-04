insert into vendor (name,description) values ("Fanvil","Fanvil Technology Co.");
insert into model (name,description,id_vendor,iax_support) values ("C62","C62",(select id from vendor where name="Fanvil"),'1');
insert into mac (id_vendor,value,description) values ((select id from vendor where name="Fanvil"),"00:A8:59","Fanvil");

insert into vendor (name,description)values("Escene","Escene Communication");
insert into model (name,description,id_vendor,iax_support)values("ES620","ES620",(select id from vendor where name="Escene"),'0');
insert into mac (id_vendor,value,description)values((select id from vendor where name="Escene"),"00:26:8B","Escene");
