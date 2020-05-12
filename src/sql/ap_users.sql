create table ap_users
(
    id        int auto_increment
        primary key,
    username  varchar(255) null,
    token     int          null,
    lastname  varchar(255) null,
    firstname varchar(255) null,
    password  varchar(255) null
);

