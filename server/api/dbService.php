<?php

class DbService {
    protected $dbConnection;
    protected $sessionId;

    // MySQL Connection constants
    const DB_HOST = "127.0.0.1";
    const DB_NAME = "4840w";
    const DB_USERNAME = "4840w";
    const DB_PASSWORD = "4840w";

    // Status Codes
    const ERROR_CODE = "error";
    const NO_DATA_FOUND_CODE = "no_data_found";
    const SUCCESS_CODE = "success";
    const INVALID_CREDENTIALS_CODE = "invald_credentials";

    // Messages
    const USER_NOT_FOUND_MSG = "User not found";

    function __construct() {
        // Open database connection
        $this->dbConnection = new PDO("mysql:host=" .static::DB_HOST. ";dbname=" .static::DB_NAME, static::DB_USERNAME, static::DB_PASSWORD);
        $this->dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->sessionId = session_id();
    }

    function __destruct() {
        // Close database connection
        $this->dbConnection = null;
    }

    function getProductApiKey() {
        try {
            // Retrieve IAM DATA Client Keys
            $sql = "SELECT client_id, client_secret FROM iamdata_properties";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchObject();
            if ($results == null) {
                $results = (object) ['code' => static::NO_DATA_FOUND_CODE,
                                     'msg' => 'System configuration error, product api keys not found'];
            } else {
                $results->code = static::SUCCESS_CODE;
                $results->msg = 'Retrieved product api keys';
            }
        } catch(Exception $e) {
            $results = (object) ['code' => static::ERROR_CODE,
                                 'msg' => $e->getMessage()];
        } finally {
            return $results;
        }
    }

    function getOpenFdaApiKey() {
        try {
            // Retrieve Open FDA api key
            $sql = "SELECT api_key FROM api_key WHERE service_name LIKE 'OPEN_FDA%' ORDER BY rand() LIMIT 1";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchObject();
            if ($results == null) {
                $results = (object) ['code' => static::NO_DATA_FOUND_CODE,
                                     'msg' => 'System configuration error, openFDA api keys not found'];
            } else {
                $results->code = static::SUCCESS_CODE;
                $results->msg = 'Retrieved openFDA api keys';
            }
        } catch(Exception $e) {
            $results = (object) ['code' => static::ERROR_CODE,
                                 'msg' => $e->getMessage()];
        } finally {
            return $results;
        }
    }

    function getUserByUserId($userId) {
        try {
            $sql = "SELECT user_id, email, zip FROM user WHERE user_id=:user_id";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindParam("user_id", $userId);
            $stmt->execute();
            $results = $stmt->fetchObject();
            if ($results == null) {
                $results = (object) ['code' => static::NO_DATA_FOUND_CODE,
                                     'msg' => static::USER_NOT_FOUND];
            } else {
                $results->code = static::SUCCESS_CODE;
                $results->msg = 'Retrieved user';
            }
        } catch(Exception $e) {
            $results = (object) ['code' => static::ERROR_CODE,
                                 'msg' => $e->getMessage()];
        } finally {
            return $results;
        }
    }

    function getUserByEmail($email) {
        try {
            $sql = "SELECT user_id, email, zip FROM user WHERE email=:email";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindParam("email", $email);
            $stmt->execute();
            $results = $stmt->fetchObject();

            if ($results == null) {
                $results = (object) ['code' => static::NO_DATA_FOUND_CODE,
                                     'msg' => static::USER_NOT_FOUND_MSG];
            } else {
                $results->code = static::SUCCESS_CODE;
                $results->msg = 'Retrieved user';
            }
        } catch(Exception $e) {
            $results = (object) ['code' => static::ERROR_CODE,
                                 'msg' => $e->getMessage()];
        } finally {
            return $results;
        }
    }

    function getUserBySessionId() {
        try {
            $sql = "SELECT user_id FROM user_session where session_id=:session_id";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindParam("session_id", $this->sessionId);
            $stmt->execute();

            $sessionData = $stmt->fetchObject();

            if ($sessionData == null) {
                $results = (object) ['code' => static::NO_DATA_FOUND_CODE,
                                     'msg' => static::USER_NOT_FOUND_MSG];
            } else {
                $sql = "SELECT user_id, email, zip FROM user where user_id=:user_id";
                $stmt = $this->dbConnection->prepare($sql);
                $stmt->bindParam("user_id", $sessionData->user_id);
                $stmt->execute();
                $results = $stmt->fetchObject();

                if ($results == null) {
                    $results = (object) ['code' => static::NO_DATA_FOUND_CODE,
                                         'msg' => static::USER_NOT_FOUND_MSG];
                } else {
                    $results->code = static::SUCCESS_CODE;
                    $results->msg = 'Retrieved user';
                }
            }
        } catch(Exception $e) {
            $results = (object) ['code' => static::ERROR_CODE,
                                 'msg' => $e->getMessage()];
        } finally {
            return $results;
        }
    }

    function doValidatePassword($email, $password) {
        try {
            $sql = "SELECT user_id, password FROM user WHERE email=:email";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindParam("email", $email);
            $stmt->execute();
            $results = $stmt->fetchObject();

            if ($results == null) {
                $results = (object) ['code' => static::NO_DATA_FOUND_CODE,
                                     'msg' => 'User not found'];
            } else {
                if ($results->password == validateHashedPassword($password, $results->password)) {
                    $results->code = static::SUCCESS_CODE;
                    $results->msg = 'Validated email and password';
                    $results->user_id = $results->user_id;
                } else {
                    $results->code = static::INVALID_CREDENTIALS_CODE;
                    $results->msg = 'Email address and/or password was invalid';
                }
            }
        } catch(Exception $e) {
            $results = (object) ['code' => static::ERROR_CODE,
                                 'msg' => $e->getMessage()];
        } finally {
            return $results;
        }
    }

    function doSessionLogin($userId) {
        try {
            $this->dbConnection->beginTransaction();

            $sql = "update user set last_login= now() WHERE user_id=:user_id";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindParam("user_id", $userId);
            $stmt->execute();

            if ( $stmt->rowCount() == 0 ) {
                throw new Exception('User was not found in the database');
            }

            $sql = "delete from user_session where session_id=:session_id";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindParam("session_id", $this->sessionId);
            $stmt->execute();

            $sql = "insert into user_session (user_id, session_id, create_dttm) values (:user_id, :session_id, now())";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindParam("user_id", $userId);
            $stmt->bindParam("session_id", $this->sessionId);
            $stmt->execute();

            $this->dbConnection->commit();

            $results = (object) ['code' => static::SUCCESS_CODE,
                                 'msg' => 'Updated last logon in user table'];
        } catch(Exception $e) {
            $this->dbConnection->rollBack();
            $results = (object) ['code' => static::ERROR_CODE,
                                 'msg' => $e->getMessage()];
        } finally {
            return $results;
        }
    }

    function registerUser($email, $zipcode, $password){
        try {
            $userId = null;
            $this->dbConnection->beginTransaction();

            // Create user record with a hashed password
            $sql = "insert into user (email, zip, password, last_login) values (:email, :zip, :password, now())";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindParam("email",  $email);
            $stmt->bindParam("zip",  $zipcode);
            $stmt->bindParam("password",  $password);
            $stmt->execute();

            // Retrieve the user sequence id
            $userId = $this->dbConnection->lastInsertId();

            // Create session for user
            $sql = "insert into user_session (user_id, session_id, create_dttm) values (:user_id, :session_id, now())";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindParam("user_id", $userId);
            $stmt->bindParam("session_id", $this->sessionId);
            $stmt->execute();

            $this->dbConnection->commit();

            $results = (object) ['code' => static::SUCCESS_CODE,
                                 'msg' => 'User was registered in the database',
                                 'user_id' => $userId];

        } catch(Exception $e) {
            $this->dbConnection->rollBack();
            $results = (object) ['code' => static::ERROR_CODE,
                                 'msg' => $e->getMessage()];
        } finally {
            return $results;
        }
    }

    function deleteUser($userId){
        try {
            $this->dbConnection->beginTransaction();

            $sql = "DELETE FROM user_session WHERE user_id =:user_id";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindParam("user_id", $userId);
            $stmt->execute();

            $sql = "DELETE FROM user WHERE user_id=:user_id";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindParam("user_id", $userId);
            $stmt->execute();

            $this->dbConnection->commit();

            $results = (object) ['code' => static::SUCCESS_CODE,
                                 'msg' => 'User was deleted from the database'];

        } catch(Exception $e) {
            $this->dbConnection->rollBack();
            $results = (object) ['code' => static::ERROR_CODE,
                                 'msg' => $e->getMessage()];
        } finally {
            return $results;
        }
    }

    function doSessionLogout() {
        try {
            $sql = "DELETE FROM user_session WHERE session_id=:session_id";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindParam("session_id", $this->sessionId);
            $stmt->execute();

            $results = (object) ['code' => static::SUCCESS_CODE,
                                 'msg' => 'User was logged out of the system'];

        } catch(Exception $e) {
            $this->dbConnection->rollBack();
            $results = (object) ['code' => static::ERROR_CODE,
                                 'msg' => $e->getMessage()];
        } finally {
            return $results;
        }
    }

}
?>