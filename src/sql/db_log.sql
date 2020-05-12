create table db_log
(
    id         int auto_increment
        primary key,
    mode       varchar(255) null,
    db_table   varchar(255) not null,
    entry_id   int          not null,
    `column`   varchar(255) not null,
    new_value  varchar(255) null,
    old_value  varchar(255) null,
    user_id    int          null,
    updated_at datetime     null,
    created_at datetime     null
);

