<?php

function userLogin() {
	$response = new restResponse;
	$sessionId = session_id();
    try {
		$request = Slim::getInstance()->request();
		$body = json_decode($request->getBody());

		if (!property_exists($body, 'email')) {
			$response->set("invalid_parameter","Email address is required", array());
			return;
		}

		if (!property_exists($body, 'password')) {
			$response->set("invalid_parameter","Password is required", array());
			return;
		}
        $db = getConnection();
		$sql = "SELECT user_id, password, zip, last_login FROM user WHERE UPPER(email)=:email";

        $stmt = $db->prepare($sql);
        $email = strtoupper($body->email);
        $stmt->bindParam("email", $email);
        $stmt->execute();

        $userData = $stmt->fetchObject();

        if ($userData == null) {
			$response->set("invalid_user_id_password","Email address and/or password was invalid", array());
		}
		else
		{
			if ($body->password == $userData->password) {
				try {
					$sql = "update user set last_login= now() WHERE user_id=:user_id";
					$stmt = $db->prepare($sql);
					$stmt->bindParam("user_id", $userData->user_id);
					$stmt->execute();

					$sql = "delete from user_session where session_id=:session_id";
					$stmt = $db->prepare($sql);
					$stmt->bindParam("session_id", $sessionId);
					$stmt->execute();
                    
					$sql = "insert into user_session (user_id, session_id, create_dttm) values (:user_id, :session_id, now())";
					$stmt = $db->prepare($sql);
					$stmt->bindParam("user_id", $userData->user_id);
					$stmt->bindParam("session_id", $sessionId);
					$stmt->execute();

					$response->set("success","User was authenticated", array("SESSION_ID" => $sessionId) );

				} catch(Exception $e) {
					$response->set("system_failure","System error occurred, unable to login", array());
				}
			}
			else
			{
				$response->set("invalid_user_id_password","Email address and/or password was invalid", array());
			}
        }
    } catch(Exception $e) {
		$response->set("system_failure", "System error occurred, unable to login", array());
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
        
		if (!property_exists($body, 'email')) {
			$response->set("invalid_parameter","Email was not found", array());
			return;
		}
        
		if (!property_exists($body, 'zipcode')) {
			$response->set("invalid_parameter","Zipcode was not found", array());
			return;
		}

		if (!property_exists($body, 'password')) {
			$response->set("invalid_parameter","Password was not found", array());
			return;
		}        
        
        $db = getConnection();
        $email = strtoupper($body->email);
        
        // Check if the user already exists in the database
        $sql = "SELECT email, zip, password FROM user WHERE upper(email)=:email";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("email", $email);
        $stmt->execute();
        $userData = $stmt->fetchObject();
        
		if ($userData != null) {
			$response->set("user_already_exists","User with Email address already exists", array());
		}
		else
		{
            // Create user record 
            $sql = "insert into user (email, zip, password, last_login) values (:email, :zip, :password, now())";
			$stmt = $db->prepare($sql);
			$stmt->bindParam("email",  $body->email);
			$stmt->bindParam("zip",  $body->zipcode);
			$stmt->bindParam("password",  $body->password);
			$stmt->execute();

            // Retrieve user data
			$sql = "SELECT user_id, password, zip FROM user WHERE upper(email)=:email";
			$stmt = $db->prepare($sql);
			$stmt->bindParam("email", $email);
			$stmt->execute();
			$userData = $stmt->fetchObject();

            // Create session for user 
			$sql = "insert into user_session (user_id, session_id, create_dttm) values (:user_id, :session_id, now())";
			$stmt = $db->prepare($sql);
			$stmt->bindParam("user_id", $userData->user_id);
			$stmt->bindParam("session_id", $sessionId);
			$stmt->execute();
            
            // Auto register user in products service (iamdata)
            $productAddUser = productsAddUserLocalAPI();
         
            if ($productAddUser->code !== "success") {
                $response->set($productAddUser->code, $productAddUser->msg, $productAddUser->payload);
                return;
            }         
            
		    $response->set("success","User was registered", $userData);            
       }

    } catch(Exception $e) {
        $response->set("system_failure","System error occurred, unable save user", array());
    } finally {
        try {
            if ($response->code !== "success") {
                $sql = "DELETE FROM user_session WHERE session_id=:session_id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam("session_id", $sessionId);
                $stmt->execute();
                
                $sql = "DELETE FROM user WHERE user_id=:user_id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam("user_id", $userData->user_id);
                $stmt->execute();                
            }
        } catch(Exception $e) {
            // Do nothing, we tried to clean up the account, so just give up at this point
        }
        
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
            $response->set("not_logged_on","User is not logged into the system", array());
            return;
        }  
        
        $sql = "SELECT user_id, email, zip FROM user WHERE user_id=:user_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $sessionData->user_id);
        $stmt->execute();
        $userData = $stmt->fetchObject();

        if ($userData == null) {
			$response->set("user_not_found","User was not found", array());
		}
		else
		{
			$response->set("success","User is logged into the system", $userData);
        }
    } catch(Exception $e) {
		$response->set("system_failure", "System error occurred, unable get user", array());
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
        $response->set("success","User logged out successfully", array());
    	$db = null;
		$response->toJSON();
	}
}

?>