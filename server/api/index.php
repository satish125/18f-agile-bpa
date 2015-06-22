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
$app->post('/addProduct', 'addProduct');
$app->get('/recentRecalls/:type/:days/:limit', 'recentRecalls');
$app->post('/registerUser', 'registerUser');

$app->run();

class restResponse
{
	public $code = "";
	public $msg = "";
	public $payload = "";

	function set($code, $msg, $payload)
    {
        $this->code = $code;
		$this->msg = $msg;
		$this->payload = $payload;
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
			$response->set("invalid_parameter","email parameter was not found", "");
			return;
		}

		if (!property_exists($body, 'password')) {
			$response->set("invalid_parameter","password parameter was not found", "");
			return;
		}
        $db = getConnection();
		$sql = "SELECT user_id, password, zip, last_login FROM user WHERE email=:email";

        $stmt = $db->prepare($sql);
        $stmt->bindParam("email", $body->email);
        $stmt->execute();

        $user_data = $stmt->fetchObject();

        if ($user_data == null) {
			$response->set("invalid_user_id_password","Email address and/or password was invalid", "");
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
				$response->set("invalid_user_id_password","Email address and/or password was invalid", "");
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
    	$db = null;
		$response->toJSON();
	}
}


function addProduct() {
	$response = new restResponse;

    $sql = "insert into user_product (user_id, product, vendor, upc_code, create_dttm) values (:user_id, :product, :vendor, :upc_code, now())";

    try {
    	$request = Slim::getInstance()->request();
		$body = json_decode($request->getBody());
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id",  $body->user_id);
        $stmt->bindParam("product",  $body->product);
        $stmt->bindParam("vendor",  $body->vendor);
        $stmt->bindParam("upc_code",  $body->upc_code);


        $stmt->execute();

       $response->set("success","product inserted", "");

    } catch(PDOException $e) {
        $response->set("system_failure","System error occurred, unable save product", "");
    }finally {
    	$db = null;
		$response->toJSON();
	}
}

function registerUser() {
	$response = new restResponse;

    $sql = "insert into user (email, zip, password) values (:email, :zip, :password)";

    try {
    	$request = Slim::getInstance()->request();
		$body = json_decode($request->getBody());
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("email",  $body->email);
        $stmt->bindParam("zip",  $body->zipcode);
        $stmt->bindParam("password",  $body->password);


        $stmt->execute();

       $response->set("success","User inserted.", "");

    } catch(PDOException $e) {
        $response->set("system_failure","System error occurred, unable save user", "");
    }finally {
    	$db = null;
		$response->toJSON();
	}

}
	
function recentRecalls($type, $days, $limit) {
	$response = new restResponse;


	$start = date("Ymd", strtotime("-".$days." days"));
	$end = date("Ymd");


	$key = "dkjmH4qrI5pMYoj8hN0SCR8mhESAPGg8XxBH169b";
	$url = "https://api.fda.gov/".$type."/enforcement.json?api_key=" .$key. "&search=report_date:[" .$start. "+TO+" .$end. "]&limit=".$limit; //


	try {
		$data = array("key1" => "value1", "key2" => "value2");
		$options = array(
<<<<<<< HEAD
			"http" => array(
			"header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
			"method"  => "GET",
			"content" => http_build_query($data),
=======
				"http" => array(
				"header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
				"method"  => "GET",
				"content" => http_build_query($data),
>>>>>>> 6be025bc2f06620f9fd438be5883628f220a895b
			),
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		$bigArr = json_decode($result,true,20);
		$res = $bigArr["results"];
		$json = json_encode($res);
<<<<<<< HEAD
		$response->set("success","Data successfully fetched from service", $json );
=======
		$json1 = json_decode($json);


		$response->set("success","Data successfully fetched from service", $json1 );
>>>>>>> 6be025bc2f06620f9fd438be5883628f220a895b
	} catch(PDOException $e) {
		$response->set("system_failure","System error occurred, unable fetch data", "");
	} finally {
		$response->toJSON();
	}
<<<<<<< HEAD
}

	
=======

}

>>>>>>> .merge_file_a02984
>>>>>>> 6be025bc2f06620f9fd438be5883628f220a895b
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