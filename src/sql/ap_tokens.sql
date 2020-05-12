create table ap_tokens
(
    id            int auto_increment
        primary key,
    user_id       int          null,
    user_agent    varchar(512) null,
    ip_address    tinytext     null,
    token         tinytext     null,
    creation_time timestamp    null
);

