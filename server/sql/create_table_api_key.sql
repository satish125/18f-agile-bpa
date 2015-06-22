use 4840w;


DROP TABLE IF EXISTS api_key;

CREATE TABLE `api_key` (
  `service_name` VARCHAR(50) NOT NULL,
  `api_key` VARCHAR(200) NOT NULL,
  PRIMARY KEY (`service_name`),
  UNIQUE INDEX `service_name_UNIQUE` (`service_name` ASC));
    
COMMIT;