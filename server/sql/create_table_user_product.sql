use 4840w;

DROP TABLE IF EXISTS user_product;

CREATE TABLE user_product (
  user_product_id INT NOT NULL AUTO_INCREMENT,
  user_id INT NULL,
  product VARCHAR(500) NULL,
  vendor VARCHAR(100) NULL,
  upc_code VARCHAR(100) NULL,
  create_dttm DATETIME NULL,
  PRIMARY KEY (`user_product_id`),
  INDEX `fk_user_product_user_id_idx` (`user_id` ASC),
  CONSTRAINT `fk_user_product_user_id`
    FOREIGN KEY (`user_id`)
    REFERENCES user (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);
    
COMMIT;
