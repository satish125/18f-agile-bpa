use 4840w;
DROP TABLE IF EXISTS user;

CREATE TABLE user (
user_id int(11) NOT NULL AUTO_INCREMENT,
email varchar(45) DEFAULT NULL,
password varchar(65536) NOT NULL,
zip int(11) NOT NULL,
last_login datetime DEFAULT NULL,
PRIMARY KEY (`user_id`),
UNIQUE KEY email_UNIQUE (`email`)
);

insert into user (email, password, zip) values('demo@crgt.com', 'sha256:1000:38qWaNJiaOSoBHyxF6xd3cND6kuN8+BC:r9vD5Q2uD5Pp4Ndt0DuVg9g7M17KTNYf', '20151');
COMMIT;