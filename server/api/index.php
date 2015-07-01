<?php

 // Set a custom session name
$session_name = 'SESSION_ID'; 

// Set to true if using https else leave as false
$secure = true;

// This stops javascript being able to access the session id
$httponly = true;

// Forces sessions to only use cookies.
ini_set('session.use_only_cookies', 1);

 // better session id's
ini_set('session.entropy_file', '/dev/urandom');

// and going overkill with entropy length for maximum security
ini_set('session.entropy_length', '512');

 // Gets current cookies params.
$cookieParams = session_get_cookie_params();

session_set_cookie_params($cookieParams["lifetime"], "/", $cookieParams["domain"], $secure, $httponly);

// Sets the session name to the one set above.
session_name($session_name);

// Start the php session
session_start();

require 'Slim/Slim.php';
require 'userService.php';
require 'openFDAService.php';
require 'productService.php';

ini_set('display_errors', '1');
error_reporting(-1);

$app = new Slim();

// User Services
$app->post('/user/login', 'userLogin');
$app->get('/user/get', 'userGet');
$app->post('/user/register', 'userRegister');
$app->get('/user/logout', 'userLogout');

// openFDA Services
$app->get('/openFDA/recentRecalls/:type/:days/:limit', 'openFDARecentRecalls');
$app->post('/openFDA/productMatch/:type/:days/:minMatchingScore/:minQualityScore', 'openFDAProductMatch');

// Product Services
$app->get('/products/getUser', 'productsGetUser');
$app->delete('/products/deleteUser', 'productsDeleteUser');
$app->post('/products/addUser', 'productsAddUser');
$app->get('/products/getStores', 'productsGetStores');
$app->get('/products/getProduct/:productId', 'productsGetProduct');
$app->get('/products/getUserStores/:page', 'productsGetUserStores');
$app->get('/products/getUserStore/:userStoreId', 'productsGetUserStore');
$app->get('/products/getUserPurchases/:dayLimit/:page', 'productsGetUserPurchases');
$app->post('/products/addUserStore', 'productsAddUserStore');
$app->delete('/products/deleteUserStore/:userStoreId','productsDeleteUserStore');
$app->put('/products/updateUserStore', 'productsUpdateUserStore');

$app->run();

class restResponse{
	public $code = "";
	public $msg = "";
	public $payload = "";

	function set($code, $msg, $payload){
        $this->code = $code;
		$this->msg = $msg;
		$this->payload = $payload;
    }

	function toJSON() {
		echo json_encode($this);
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