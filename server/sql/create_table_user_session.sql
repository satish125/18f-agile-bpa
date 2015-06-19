use 4840w;

DROP TABLE IF EXISTS user_session;


CREATE TABLE user_session (
  user_session_id INT NOT NULL AUTO_INCREMENT,
  user_id INT NULL,
  session_id VARCHAR(100) NULL,
  create_dttm DATETIME NULL,
  logout_dttm DATETIME NULL,
  PRIMARY KEY (user_session_id),
  INDEX index_session_user_id (user_id ASC),
  INDEX index_session_session_id (session_id ASC));

    
COMMIT;
