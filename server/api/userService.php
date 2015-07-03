<?php

require 'passwordHash.php';

class UserService extends ServiceTemplate{
	public static function userLogin() {
		$response = new restResponse;
		$sessionId = session_id();
	    try {
			$request = \Slim\Slim::getInstance()->request();
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
			$sql = "SELECT user_id, password, zip FROM user WHERE UPPER(email)=:email";

	        $stmt = $db->prepare($sql);
	        $email = strtoupper($body->email);
	        $stmt->bindParam("email", $email);
	        $stmt->execute();

	        $userData = $stmt->fetchObject();

	        if ($userData == null) {
				$response->set("invalid_user_id_password","Email address and/or password was invalid", array());
			} else {
				if ($body->password == validateHashedPassword($body->password, $userData->password)) {
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
				} else {
					$response->set("invalid_user_id_password","Email address and/or password was invalid", array());
				}
	        }
	    } catch(Exception $e) {
			$response->set("system_failure", $e->getMessage(), array());
	    } finally {
			$db = null;
			$response->toJSON();
		}
	}

	public static function userRegister() {
	    $response = new restResponse;
		$sessionId = session_id();
	    try {
			$request = \Slim\Slim::getInstance()->request();
			$body = json_decode($request->getBody());
	        
			if (!property_exists($body, 'email')) {
				$response->set("invalid_parameter","Email Address is required", array());
				return;
			}
	        
			if (!property_exists($body, 'zipcode')) {
				$response->set("invalid_parameter","Zipcode is required", array());
				return;
			}

			if (!property_exists($body, 'password')) {
				$response->set("invalid_parameter","Password is required", array());
				return;
			}        
	        
	        parent::$db = getConnection();
	        $email = strtoupper($body->email);
	        
	        // Check if the user already exists in the database
	        $sql = "SELECT email, zip, password FROM user WHERE upper(email)=:email";
	        $stmt = parent::$db->prepare($sql);
	        $stmt->bindParam("email", $email);
	        $stmt->execute();
	        parent::$userData = $stmt->fetchObject();
	        
			if (parent::$userData != null) {
				$response->set("user_already_exists","User with Email address already exists", array());
			} else {
	            
	            // Has Password
	            $passwordHash = createHashedPassword($body->password);
	            
	            // Create user record 
	            $sql = "insert into user (email, zip, password, last_login) values (:email, :zip, :password, now())";
				$stmt = parent::$db->prepare($sql);
				$stmt->bindParam("email",  $body->email);
				$stmt->bindParam("zip",  $body->zipcode);
				$stmt->bindParam("password",  $passwordHash);
				$stmt->execute();

	            // Retrieve user data
				$sql = "SELECT user_id, email, zip FROM user WHERE upper(email)=:email";
				$stmt = parent::$db->prepare($sql);
				$stmt->bindParam("email", $email);
				$stmt->execute();
				parent::$userData = $stmt->fetchObject();

	            // Create session for user 
				$sql = "insert into user_session (user_id, session_id, create_dttm) values (:user_id, :session_id, now())";
				$stmt = parent::$db->prepare($sql);
				$stmt->bindParam("user_id", parent::$userData->user_id);
				$stmt->bindParam("session_id", $sessionId);
				$stmt->execute();
	            
	            // Auto register user in products service (iamdata)
	            $productAddUser = ProductService::productsAddUserLocalAPI();
	         
	            if ($productAddUser->code !== "success") {
	                $response->set($productAddUser->code, $productAddUser->msg, $productAddUser->payload);
	                return;
	            }         
	            
			    $response->set("success","User was registered", parent::$userData);            
	       }

	    } catch(Exception $e) {
	        $response->set("system_failure","System error occurred, unable save user".$e->getMessage(), array());
	    } finally {
	        try {
	            if ($response->code !== "success") {
	                $sql = "DELETE FROM user_session WHERE session_id=:session_id";
	                $stmt = parent::$db->prepare($sql);
	                $stmt->bindParam("session_id", $sessionId);
	                $stmt->execute();
	                
	                $sql = "DELETE FROM user WHERE user_id=:user_id";
	                $stmt = parent::$db->prepare($sql);
	                $stmt->bindParam("user_id", $userData->user_id);
	                $stmt->execute();                
	            }
	        } catch(Exception $e) {
	            // Do nothing, we tried to clean up the account, so just give up at this point
	        }
	        
			parent::$db = null;
			$response->toJSON();
		}

	}

	public static function userGet() {
		if(!parent::init(true, true, false)){
			return;
		}
		parent::$response->set("success","User is logged into the system", parent::$userData);
		parent::$response->toJSON();
	}

	public static function userLogout() {
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
}



?>