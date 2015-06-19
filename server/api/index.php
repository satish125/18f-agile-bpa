<?php

// Establish a session
$session_name = 'SESSION_ID'; // Set a custom session name
$secure = false; // Set to true if using https else leave as false
$httponly = true; // This stops javascript being able to access the session id 
ini_set('session.use_only_cookies', 1); // Forces sessions to only use cookies. 
ini_set('session.entropy_file', '/dev/urandom'); // better session id's
ini_set('session.entropy_length', '512'); // and going overkill with entropy length for maximum security
$cookieParams = session_get_cookie_params(); // Gets current cookies params.

session_set_cookie_params($cookieParams["lifetime"], "/", $cookieParams["domain"], $secure, $httponly); 
session_name($session_name); // Sets the session name to the one set above.
session_start(); // Start the php session

require 'Slim/Slim.php';

ini_set('display_errors', '1');
error_reporting(-1);
    
$app = new Slim();

$app->post('/loginUser', 'loginUser');
$app->get('/getUser/:email', 'getUser');

$app->run();

class restResponse
{
    public $code = "";
    public $msg = "";
    public $payload = "";    
    
    function set($code_in, $msg_in, $payload_in)
    {
        $this->code = $code_in;
        $this->msg = $msg_in;
        $this->payload = $payload_in;
    }
    
    function toJSON() {
        echo json_encode($this);
    }
    
}

function loginUser() {
    $response = new restResponse;
    $session_id = session_id();
    try {
        $request = Slim::getInstance()->request();
        $body = json_decode($request->getBody());
        
        if (!property_exists($body, 'email')) {
            $response->set("invalid_parameter","Email parameter was not found", "");
            return;
        }
        
        if (!property_exists($body, 'password')) {
            $response->set("invalid_parameter","Password parameter was not found", "");
            return;
        }
        
        if ($body->email == "" OR $body->password == "") {
            $response->set("missing_value","User ID and Password is required", "");
            return;
        }
        
        $db = getConnection();
        $sql = "SELECT user_id, password, zip, last_login FROM user WHERE email=:email";

        $stmt = $db->prepare($sql);
        $stmt->bindParam("email", $body->email);
        $stmt->execute();
        
        $user_data = $stmt->fetchObject();

        if ($user_data == null) {
            $response->set("invalid_credentials","Invalid User ID and/or Password was entered", "");            
        }    
        else
        {
            if ($body->password == $user_data->password) {
                try {
                    $sql = "update user set last_login= now() WHERE email=:email";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam("email", $body->email);
                    $stmt->execute();

                    $sql = "insert into user_session (user_id, session_id, create_dttm) values (:user_id, :session_id, now())";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam("user_id", $user_data->user_id);
                    $stmt->bindParam("session_id", $session_id);                    
                    $stmt->execute();
                    
                    $response->set("success","User was authenticated", array("SESSION_ID" => $session_id) );
            
                } catch(PDOException $e) {
                    $response->set("system_failure","System error occurred, unable to login", "");    
                }
            }
            else
            {
                $response->set("invalid_credentials","Invalid User ID and/or Password was entered", "");    
            }
        } 
    } catch(PDOException $e) {
        $response->set("system_failure","System error occurred, unable to login", "");    
    } finally {
        $db = null;
        $response->toJSON();
    }
}

function getUser($email) {
    $response = new restResponse;    

    try {
        $sql = "SELECT email, zip, password  FROM user WHERE email=:email";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("email", $email);
        $stmt->execute();
        $user_data = $stmt->fetchObject();
        $db = null;
        
        if ($user_data == null) {
            $response->set("user_not_found","User was not found", "");            
        }    
        else
        {
            $response->set("success","User was found", $user_data);
        }
    } catch(PDOException $e) {
        $response->set("system_failure","System error occurred, unable to login", "");    
    } finally {
        $response->toJSON();
    }    
}

function getConnection() {
    $dbhost="127.0.0.1";
    $dbuser="4840w";
    $dbpass="4840w";
    $dbname="4840w";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);    
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}

?>