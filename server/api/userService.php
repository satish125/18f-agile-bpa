<?php

function userLogin() {
	$response = new restResponse;
	$sessionId = session_id();
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

        $userData = $stmt->fetchObject();

        if ($userData == null) {
			$response->set("invalid_user_id_password","Email address and/or password was invalid", "");
		}
		else{
			if ($body->password == $userData->password) {
				try {
					$sql = "update user set last_login= now() WHERE email=:email";
					$stmt = $db->prepare($sql);
					$stmt->bindParam("email", $body->email);
					$stmt->execute();

					$sql = "insert into user_session (user_id, session_id, create_dttm) values (:user_id, :session_id, now())";
					$stmt = $db->prepare($sql);
					$stmt->bindParam("user_id", $userData->user_id);
					$stmt->bindParam("session_id", $sessionId);
					$stmt->execute();

					$response->set("success","User was authenticated", array("SESSION_ID" => $sessionId) );

				} catch(PDOException $e) {
					$response->set("system_failure","System error occurred, unable to login", "");
				}
			}
			else{
				$response->set("invalid_user_id_password","Email address and/or password was invalid", "");
			}
        }
    } catch(Exception $e) {
		$response->set("system_failure","System error occurred, unable to login", "");
    } finally {
		$db = null;
		$response->toJSON();
	}
}

function userRegister() {
    $response = new restResponse;
	$sessionId = session_id();
    try {
		$request = Slim::getInstance()->request();
		$body = json_decode($request->getBody());
        $db = getConnection();
        
        // Check if the user already exists in the database
        $sql = "SELECT email, zip, password FROM user WHERE email=:email";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("email",  $body->email);
        $stmt->execute();
        $userData = $stmt->fetchObject();
        
		if ($userData != null) {
			$response->set("user_already_exists","User with Email address already exists", "");
		}
		else{
            // Create user record 
            $sql = "insert into user (email, zip, password, last_login) values (:email, :zip, :password, now())";
			$stmt = $db->prepare($sql);
			$stmt->bindParam("email",  $body->email);
			$stmt->bindParam("zip",  $body->zipcode);
			$stmt->bindParam("password",  $body->password);
			$stmt->execute();

            // Retrieve user data
			$sql = "SELECT user_id, password, zip FROM user WHERE email=:email";
			$stmt = $db->prepare($sql);
			$stmt->bindParam("email", $body->email);
			$stmt->execute();
			$userData = $stmt->fetchObject();

            // Create session for user 
			$sql = "insert into user_session (user_id, session_id, create_dttm) values (:user_id, :session_id, now())";
			$stmt = $db->prepare($sql);
			$stmt->bindParam("user_id", $userData->user_id);
			$stmt->bindParam("session_id", $sessionId);
			$stmt->execute();
            
		    $response->set("success","User was registered", $userData);            
       }

    } catch(Exception $e) {
        $response->set("system_failure","System error occurred, unable save user", "");
    }finally {
		$db = null;
		$response->toJSON();
	}

}

function userGet() {
	$response = new restResponse;
    $sessionId = session_id();
    
    try {
        $db = getConnection();        
        
        $sql = "SELECT user_id FROM user_session where session_id=:session_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("session_id", $sessionId);
        $stmt->execute();
        $sessionData = $stmt->fetchObject();
        
        if ($sessionData == null) {
            $response->set("not_logged_on","User is not logged into the system", "");
            return;
        }  
        
		$sql = "SELECT user_id, email, zip FROM user WHERE user_id=:user_id";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $sessionData->user_id);
        $stmt->execute();
        $userData = $stmt->fetchObject();

        if ($userData == null) {
			$response->set("user_not_found","User was not found", "");
		}
		else{
			$response->set("success","User is logged into the system", $userData);
        }
    } catch(Exception $e) {
		$response->set("system_failure", "System error occurred, unable get user", "");
    } finally {
    	$db = null;
		$response->toJSON();
	}
}

function userLogout() {
	$response = new restResponse;
	$sessionId = session_id();
    try {
		$sql = "DELETE FROM user_session WHERE session_id=:session_id";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("session_id", $sessionId);
        $stmt->execute();
    } catch(Exception $e) {
		// Do nothing
    } finally {
        // Always respond with success
        $response->set("success","User logged out successfully", "");
    	$db = null;
		$response->toJSON();
	}
}

?>