DROP TABLE IF EXISTS user;

CREATE TABLE user (
user_id int(11) NOT NULL AUTO_INCREMENT,
email varchar(45) DEFAULT NULL,
password varchar(45) NOT NULL,
zip int(11) NOT NULL,
last_login datetime DEFAULT NULL,
PRIMARY KEY (`user_id`),
UNIQUE KEY email_UNIQUE (`email`)
);

COMMIT;