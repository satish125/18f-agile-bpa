<?php


function getProductUser($userId) {
	$response = new restResponse;

    try {
		$sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $iamdata = $stmt->fetchObject();
        
		$sql = "SELECT user_id FROM user WHERE user_id=:user_id";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $userId);
        $stmt->execute();
        $user_data = $stmt->fetchObject();
        $db = null;
        
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
		$response->toJSON();
	}
}

function deleteProductUser($userId) {
	$response = new restResponse;

    try {
		$sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $iamdata = $stmt->fetchObject();
        
		$sql = "SELECT user_id FROM user WHERE user_id=:user_id";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $userId);
        $stmt->execute();
        $user_data = $stmt->fetchObject();
        $db = null;
        
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
		$response->toJSON();
	}
}

function setProductUser($userId, $email, $zipcode) {
	$response = new restResponse;

    try {    
		$request = Slim::getInstance()->request();
		$body = json_decode($request->getBody());

		if (!property_exists($body, 'userId')) {
			$response->set("invalid_parameter","User Id parameter was not found", "");
			return;
		}
        
		if (!property_exists($body, 'email')) {
			$response->set("invalid_parameter","Email parameter was not found", "");
			return;
		}

		if (!property_exists($body, 'zipcode')) {
			$response->set("invalid_parameter","password parameter was not found", "");
			return;
		}

		$sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $iamdata = $stmt->fetchObject();
        
		$sql = "SELECT user_id FROM user WHERE email=:user_id";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $body->email);
        $stmt->execute();
        $user_data = $stmt->fetchObject();
        $db = null;
        
        if ($user_data == null) {
			$response->set("user_not_found","User was not found", "");
            return;
		}

?>