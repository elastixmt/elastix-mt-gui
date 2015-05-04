BEGIN TRANSACTION;

CREATE TABLE card (
    id           INTEGER PRIMARY KEY,
    id_card      INTEGER,
    type         varchar (80),
    additonal    varchar (80)
);

CREATE TABLE echo_canceller (
    id              INTEGER PRIMARY KEY,
    num_port        varchar(10),
    name_port       varchar(10),
    echocanceller   varchar (10),
    id_card         INTEGER,
    FOREIGN KEY(id_card) REFERENCES card(id)
);

CREATE TABLE span_parameter (
       id               INTEGER PRIMARY KEY,
       span_num         INTEGER,
       timing_source    INTEGER,
       linebuildout     INTEGER,
       framing          varchar(10),
       coding           varchar(10),
       id_card          INTEGER,
       FOREIGN KEY(id_card) REFERENCES card(id)
);

CREATE TABLE card_parameter (
        id            INTEGER PRIMARY KEY,
        manufacturer  varchar(40),
        num_serie     varchar(40),
        id_card       INTEGER,
        FOREIGN KEY(id_card) REFERENCES card(id)
);

CREATE TABLE car_system (
        id          INTEGER PRIMARY KEY, 
        hwd         varchar (80), 
        module      varchar (80),
        vendor      varchar (80),
        num_serie   varchar(40),
        data        varchar(200) 
);

COMMIT;
