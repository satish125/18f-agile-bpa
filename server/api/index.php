<?php
require 'Slim/Slim.php';

$app = new Slim();

$app->get('/loginService/:email',  'getUser');


$app->run();



function getUser($email) {
    $sql = "SELECT * FROM user WHERE email=:email";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("email", $email);
        $stmt->execute();
        $wine = $stmt->fetchObject();
        $db = null;
        if ($wine ==   null) {
		    echo "{\"result\":\"failure\"}";
		}else
		{
			echo json_encode($wine);


        }
        updateLastLogin($email);

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function updateLastLogin($email) {
    $sql = "update user set last_login= now() WHERE email=:email";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("email", $email);
        $stmt->execute();

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}




echo 'Hello World PHP test';



function getConnection() {
    $dbhost="127.0.0.1";
    $dbuser="4840w";
    $dbpass="4840w";
    $dbname="localhost";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}



?>
