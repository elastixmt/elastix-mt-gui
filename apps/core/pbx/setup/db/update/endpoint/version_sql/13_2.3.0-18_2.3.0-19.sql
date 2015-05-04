insert into vendor (name,description)values("Damall","Damall Technology Co");
insert into model (name,description,id_vendor,iax_support)values("D-3310","D-3310",(select id from vendor where name="Damall"),'0');
insert into mac (id_vendor,value,description)values((select id from vendor where name="Damall"),"7c:14:76","Damall");

insert into vendor (name,description)values("Elastix","PaloSanto Solution");
insert into model (name,description,id_vendor,iax_support)values("LXP200","LXP200",(select id from vendor where name="Elastix"),'0');

insert into vendor (name,description)values("Atlinks","Atlinks");
insert into model (name,description,id_vendor,iax_support)values("ALCATEL Temporis IP800","ALCATEL Temporis IP800",(select id from vendor where name="Atlinks"),'0');
insert into mac (id_vendor,value,description)values((select id from vendor where name="Atlinks"),"74:65:D1","Atlinks");
