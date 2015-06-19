use 4840w;

DROP TABLE IF EXISTS user_session;

CREATE TABLE user_session(
  user_session_id INT NOT NULL AUTO_INCREMENT,
  user_id INT NULL,
  session_id VARCHAR(100) NULL,
  create_dttm DATETIME NULL,
  logout_dttm DATETIME NULL,
  PRIMARY KEY (`user_session_id`));

    
COMMIT;
