BEGIN TRANSACTION;

UPDATE model SET name='AT530', description='AT530' WHERE name='AT 530';
UPDATE model SET name='AT320', description='AT320' WHERE name='AT 320';
UPDATE model SET name='AT620', description='AT620' WHERE name='AT 620R';
INSERT INTO model (name,description,id_vendor,iax_support) VALUES ('AT610','AT610',(SELECT id FROM vendor WHERE name='Atcom'),'1');
INSERT INTO model (name,description,id_vendor,iax_support) VALUES ('AT640','AT640',(SELECT id FROM vendor WHERE name='Atcom'),'1');

COMMIT;
