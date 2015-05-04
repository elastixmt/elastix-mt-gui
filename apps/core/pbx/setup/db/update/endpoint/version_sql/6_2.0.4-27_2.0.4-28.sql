ALTER TABLE model ADD COLUMN iax_support char;

UPDATE model SET iax_support='0';
UPDATE model SET iax_support='1' WHERE name='AT 530' OR name='AT 320' OR name='AT 620R';
