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
require 'userService.php';
require 'recallService.php';
require 'productService.php';

ini_set('display_errors', '1');
error_reporting(-1);

$app = new Slim();

// User Services
$app->post('/loginUser', 'loginUser');
$app->get('/getUser/:email', 'getUser');

// This needs to be removed
$app->post('/addProduct', 'addProduct');

// Recall Services
$app->get('/recentRecalls/:type/:days/:limit', 'recentRecalls');

// Product Services
$app->get('/getProductUser/:userId', 'getProductUser');
$app->delete('/deleteProductUser/:userId', 'deleteProductUser');

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