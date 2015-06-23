<?php

function getProductUser() {
	$response = new restResponse;
    $session_id = session_id();    

    try {
        $db = getConnection();

		$sql = "SELECT user_id FROM user_session where session_id=:session_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("session_id", $session_id);
        $stmt->execute();
        $session_data = $stmt->fetchObject();
        
        if ($session_data == null) {
			$response->set("not_logged_on","You are not currently logged into the system", "");
            return;
		}    
        
		$sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $iamdata = $stmt->fetchObject();
        
        if ($iamdata == null) {
			$response->set("service_failure","product api keys are not configured", "");
            return;
		}          
        
		$sql = "SELECT user_id FROM user WHERE user_id=:user_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $session_data->user_id);
        $stmt->execute();
        $user_data = $stmt->fetchObject();
        
        if ($user_data == null) {
			$response->set("user_not_found","User was not found", "");
            return;
		}
        $user_id = $iamdata->client_id ."_". $userId;
        $url = "https://api.iamdata.co:443/v1/users/" .$user_id. "?client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;
        $data = array("key1" => "value1", "key2" => "value2");
        $options = array(
            "http" => array(
                "header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
                "method"  => "GET",
                "content" => http_build_query($data),
            ),
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if (!$result === false) {
            $bigArr = json_decode($result, true, 20);
            $res = $bigArr["result"];
            $json = json_encode($res);
            $response->set("success", "Data successfully fetched from service", $json );
        } else {
            $response->set("service_failure", "Service failed to return data", "" );            
        }
    } catch(Exception $e) {
		$response->set("system_failure","System error occurred, unable to return data", "");
    } finally {
        $db = null;        
		$response->toJSON();
	}
}

function deleteProductUser() {
	$response = new restResponse;
    $session_id = session_id();

    try {
        $db = getConnection();        
        
		$sql = "SELECT user_id FROM user_session where session_id=:session_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("session_id", $session_id);
        $stmt->execute();
        $session_data = $stmt->fetchObject();
        
        if ($session_data == null) {
			$response->set("not_logged_on","You are not currently logged into the system", "");
            return;
		}        
        
		$sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $iamdata = $stmt->fetchObject();
        
        if ($iamdata == null) {
			$response->set("service_failure","product api keys are not configured", "");
            return;
		}            
        
		$sql = "SELECT user_id FROM user WHERE user_id=:user_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $session_data->user_id);
        $stmt->execute();
        $user_data = $stmt->fetchObject();
        
        if ($user_data == null) {
			$response->set("user_not_found","User was not found", "");
            return;
		}
        $user_id = $iamdata->client_id ."_". $userId;
        $url = "https://api.iamdata.co:443/v1/users?id=" .$user_id. "&client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;
        
        $data = array("key1" => "value1", "key2" => "value2");
        $options = array(
            "http" => array(
                "header"  => "Accept: application/json; \r\nContent-type: application/x-www-form-urlencoded\r\n",
                "method"  => "DELETE",
                "content" => http_build_query($data),
            ),
        );
        $context = stream_context_create($options);

        $result = file_get_contents($url, false, $context);
        
        if (!$result === false) {
            $bigArr = json_decode($result, true, 20);
            if (!property_exists($bigArr, 'result')) {
                if (!property_exists($bigArr, 'message')) {
                    $response->set("service_failure","Service failed to return data", "");
                    return;
                } else {
                    $response->set("service_failure", $bigArr->message, "");
                    return; 
                }
            } else {
                $response->set("success", "User ID has been deleted", "" );   
            }
        } else {
            $response->set("service_failure", "Service failed to return data", "" );            
        } 
    } catch(Exception $e) {
		$response->set("system_failure","System error occurred, unable to return data", "");
    } finally {
        $db = null;
		$response->toJSON();
	}
}

function setProductUser() {
	$response = new restResponse;
    $session_id = session_id();    

    try {    
		$request = Slim::getInstance()->request();
		$body = json_decode($request->getBody());
        $db = getConnection();        

		$sql = "SELECT user_id FROM user_session where session_id=:session_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("session_id", $session_id);
        $stmt->execute();
        $session_data = $stmt->fetchObject();
        
        if ($session_data == null) {
			$response->set("not_logged_on","You are not currently logged into the system", "");
            return;
		}            
        
		$sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $iamdata = $stmt->fetchObject();
        
        if ($iamdata == null) {
			$response->set("service_failure","product api keys are not configured", "");
            return;
		}            
        
		$sql = "SELECT user_id, email, zip FROM user WHERE email=:user_id";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $body->email);
        $stmt->execute();
        $user_data = $stmt->fetchObject();
        
        if ($user_data == null) {
			$response->set("user_not_found","User was not found", "");
            return;
		}
        
        $user_id = $iamdata->client_id ."_". $userId;
        $url = "https://api.iamdata.co:443/v1/users?client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;
        
        $data = array("key1" => "value1", "key2" => "value2");
        $options = array(
            "http" => array(
                "header"  => "Accept: application/json; \r\nContent-type: application/x-www-form-urlencoded\r\n",
                "method"  => "POST",
                "content" => http_build_query($data),
            ),
        );
        $context = stream_context_create($options);

        $result = file_get_contents($url, false, $context);
        
        
    } catch(Exception $e) {
		$response->set("system_failure","System error occurred, unable to return data", "");
    } finally {
        $db = null;
		$response->toJSON();
	}
}        

?>