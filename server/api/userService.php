<?php

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

?>