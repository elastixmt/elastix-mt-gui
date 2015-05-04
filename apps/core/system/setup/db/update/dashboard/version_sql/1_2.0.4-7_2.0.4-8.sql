ALTER TABLE applet ADD COLUMN icon varchar(50);

UPDATE applet set code = 'Applet_SystemResources', icon = 'memory.png' where id = 1;
UPDATE applet set code = 'Applet_ProcessesStatus', icon = 'semaf.gif' where id = 2;
UPDATE applet set code = 'Applet_HardDrives', icon = 'hd.png', name = 'Hard Drives' where id = 3;
UPDATE applet set code = 'Applet_PerformanceGraphic', icon = 'graf.gif' where id = 4;
UPDATE applet set code = 'Applet_News', icon = 'RSS.png' where id = 5;
UPDATE applet set code = 'Applet_CommunicationActivity', icon = 'communication.png' where id = 6;
UPDATE applet set code = 'Applet_Calendar', icon = 'calendar.png' where id = 7;
UPDATE applet set code = 'Applet_Calls', icon = 'call.png' where id = 8;
UPDATE applet set code = 'Applet_Emails', icon = 'email.png' where id = 9;
UPDATE applet set code = 'Applet_Faxes', icon = 'fax.png' where id = 10;
UPDATE applet set code = 'Applet_Voicemails', icon = 'voicemail.png' where id = 11;
UPDATE applet set code = 'Applet_System', icon = 'system.png' where id = 12;
UPDATE applet set code = 'Applet_TelephonyHardware', icon = 'pci.png' where id = 13;

