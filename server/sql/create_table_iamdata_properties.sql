use 4840w;

DROP TABLE IF EXISTS iamdata_properties;

CREATE TABLE `iamdata_properties` (
  `client_id` VARCHAR(50) NOT NULL,
  `client_secret` VARCHAR(200) NOT NULL);
    
COMMIT;