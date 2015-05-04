ALTER TABLE queue_member ADD COLUMN member_order INT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE queue_member ADD UNIQUE KEY queue_ordered_interface (queue_name, member_order, interface);
ALTER TABLE queue_member DROP KEY queue_interface;

/* Add and fill columns required for WebRTC accounts */
ALTER TABLE sip ADD COLUMN `encryption` ENUM ('yes','no') DEFAULT NULL,
                ADD COLUMN `avpf` ENUM ('yes','no') DEFAULT NULL,
                ADD COLUMN `force_avp` ENUM ('yes','no') DEFAULT NULL,
                ADD COLUMN `icesupport` ENUM ('yes','no') DEFAULT NULL,
                ADD COLUMN `dtlsenable` ENUM ('yes','no') DEFAULT NULL,
                ADD COLUMN `dtlsverify` ENUM ('yes','no') DEFAULT NULL,
                ADD COLUMN `dtlscertfile` VARCHAR(255) DEFAULT NULL,
                ADD COLUMN `dtlsprivatekey` VARCHAR(255) DEFAULT NULL,
                ADD COLUMN `dtlssetup` VARCHAR(16) DEFAULT NULL;
UPDATE sip SET encryption = 'yes', avpf = 'yes', force_avp = 'yes',
    icesupport = 'yes', `dtlsenable` = 'yes', `dtlsverify` = 'no',
    `dtlscertfile` = '/etc/pki/tls/certs/localhost_asterisk.crt',
    `dtlsprivatekey` = '/etc/pki/tls/private/localhost_asterisk.key',
    `dtlssetup` = 'actpass'
WHERE NAME LIKE '%IM_%';

/* Kamailio talks to Asterisk via UDP, so nobody should use anything else */
UPDATE sip SET transport = 'udp' WHERE transport IS NOT NULL;
