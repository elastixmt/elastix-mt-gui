ALTER TABLE did CHANGE COLUMN organization_code organization_code VARCHAR(100);
ALTER TABLE extension
    CHANGE COLUMN dial dial VARCHAR(170),
    CHANGE COLUMN device device VARCHAR(165) NOT NULL,
    CHANGE COLUMN elxweb_device elxweb_device VARCHAR(170);
ALTER TABLE fax
    CHANGE COLUMN dial dial VARCHAR(170),
    CHANGE COLUMN device device VARCHAR(165) NOT NULL;
ALTER TABLE iax
    CHANGE COLUMN name name VARCHAR(170) NOT NULL,
    CHANGE COLUMN dial dial VARCHAR(170),
    CHANGE COLUMN context context VARCHAR(200),
    CHANGE COLUMN regcontext regcontext VARCHAR(200);
ALTER TABLE im CHANGE COLUMN device device VARCHAR(170) NOT NULL;
ALTER TABLE musiconhold CHANGE COLUMN name name VARCHAR(170) NOT NULL;
ALTER TABLE sip
    CHANGE COLUMN name name VARCHAR(170) NOT NULL,
    CHANGE COLUMN context context VARCHAR(200),
    CHANGE COLUMN mailbox mailbox VARCHAR(170),
    CHANGE COLUMN dial dial VARCHAR(170),
    CHANGE COLUMN subscribecontext subscribecontext VARCHAR(200),
    CHANGE COLUMN outofcall_message_context outofcall_message_context VARCHAR(200);
ALTER TABLE voicemail
    CHANGE COLUMN context context VARCHAR(200) NOT NULL,
    CHANGE COLUMN exitcontext exitcontext VARCHAR(200);
