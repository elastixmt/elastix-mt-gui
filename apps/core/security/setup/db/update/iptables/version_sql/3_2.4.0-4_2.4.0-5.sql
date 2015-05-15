/* Esta secuencia de instrucciones agrega los puertos para el servicio DHCP. La secuencia
 * asume que existe la regla SIP que define el tráfico para Asterisk. El resultado final es
 * que la posición de todas las reglas desde la SIP se desplaza una posición para hacer
 * lugar a la regla DHCP. */
INSERT INTO port(name,protocol,details,comment) VALUES ('DHCPD', 'UDP', '67:68', '67:68');
UPDATE filter SET rule_order = rule_order + 1 WHERE rule_order >= (
    SELECT rule_order FROM filter WHERE dport = (SELECT id FROM port WHERE name = 'SIP')
);
INSERT INTO filter (traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated)
VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','UDP','ANY',
    (SELECT id FROM port WHERE name = 'DHCPD'),
    '','','ACCEPT',
    (SELECT rule_order FROM filter WHERE dport = (SELECT id FROM port WHERE name = 'SIP')) - 1,
    1);
UPDATE tmp_execute SET exec_in_sys = 0;
