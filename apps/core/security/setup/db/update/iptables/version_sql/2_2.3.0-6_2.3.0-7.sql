CREATE TABLE portknock_eth
(
    eth_in      varchar(15) NOT NULL,   /* Interfaz (eth0,eth1) */
    udp_port    integer     NOT NULL    /* Puerto a usar para las escuchas */
);

CREATE TABLE portknock_user_auth
(
    id          integer     PRIMARY KEY,
    id_user     integer     NOT NULL,   /* ID de usuario en acl.db acl_user.id */
    id_port     integer     NOT NULL,    /* ID de regla en port.id */
    
    FOREIGN KEY (id_port) REFERENCES port(id) 
);

CREATE TABLE portknock_user_current_rule
(
    id                  integer     PRIMARY KEY,
    eth_in              varchar(15) NOT NULL,   /* Interfaz por la que vino la petición */
    ip_source           varchar(50) NOT NULL,   /* IP desde la que vino la petición */
    id_portknock_auth   integer     NOT NULL,   /* ID de autorización en portknock_user_auth */
    rule_start          datetime    NOT NULL,   /* Fecha y hora en la que autorizó */
    
    FOREIGN KEY (id_portknock_auth) REFERENCES portknock_user_auth(id)
);
