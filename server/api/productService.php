<?php

function productsGetUser() {
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
            $response->set("not_logged_on","You are not currently logged into the system", array());
            return;
        }

        $sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $iamdata = $stmt->fetchObject();

        if ($iamdata == null) {
            $response->set("service_failure","product api keys are not configured", array());
            return;
        }

        $sql = "SELECT user_id FROM user WHERE user_id=:user_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $sessionData->user_id);
        $stmt->execute();
        $userData = $stmt->fetchObject();

        if ($userData == null) {
            $response->set("user_not_found","User was not found", array());
            return;
        }
        $userId = $iamdata->client_id ."_". $userData->user_id;
        $url = "https://api.iamdata.co:443/v1/users/" .$userId. "?client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;

        $options = array(
            "http" => array(
                "header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
                "method"  => "GET"
            ),
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            $response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            $response->set("service_failure", "Service failed to return data", array() );
        }
    } catch(Exception $e) {
        $response->set("system_failure","System error occurred, unable to return data", array());
    } finally {
        $db = null;
        $response->toJSON();
    }
}

function productsDeleteUser() {
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
            $response->set("not_logged_on","You are not currently logged into the system", array());
            return;
        }

        $sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $iamdata = $stmt->fetchObject();

        if ($iamdata == null) {
            $response->set("service_failure","product api keys are not configured", array());
            return;
        }

        $sql = "SELECT user_id FROM user WHERE user_id=:user_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $sessionData->user_id);
        $stmt->execute();
        $userData = $stmt->fetchObject();

        if ($userData == null) {
            $response->set("user_not_found","User was not found", array());
            return;
        }
        $userId = $iamdata->client_id ."_". $userData->user_id;
        $url = "https://api.iamdata.co:443/v1/users?id=" .$userId. "&client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;

        $options = array(
            "http" => array(
                "header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
                "method"  => "DELETE"
            ),
        );

        $context = stream_context_create($options);

        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            if (!property_exists($bigArr, 'result')) {
                if (!property_exists($bigArr, 'message')) {
                    $response->set("service_failure","Service failed to return data", array());
                    return;
                } else {
                    $response->set("service_failure", $bigArr->message, array());
                    return;
                }
            } else {
                $response->set("success", "User ID has been deleted", array() );
            }
        } else {
            $response->set("service_failure", "Service failed to delete data", array() );
        }
    } catch(Exception $e) {
        $response->set("system_failure","System error occurred, unable to delete data", array());
    } finally {
        $db = null;
        $response->toJSON();
    }
}

function productsAddUser() {
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
            $response->set("not_logged_on","You are not currently logged into the system", array());
            return;
        }

        $sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $iamdata = $stmt->fetchObject();

        if ($iamdata == null) {
            $response->set("service_failure","product api keys are not configured", array());
            return;
        }

        $sql = "SELECT user_id, email, zip FROM user WHERE user_id=:user_id";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $sessionData->user_id);
        $stmt->execute();
        $userData = $stmt->fetchObject();

        if ($userData == null) {
            $response->set("user_not_found","User was not found", array());
            return;
        }

        $userId = $iamdata->client_id ."_". $userData->user_id;
        $url = "https://api.iamdata.co:443/v1/users?client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;

        $data = array("email" => $userData->email, "zip" => $userData->zip, "user_id" => $userId);

        $jsonData = json_encode($data);

        $options = array(
            'http' => array(
                'protocol_version' => 1.1,
                'user_agent'       => 'phpRestAPIservice',
                'method'           => 'POST',
                'header'           => "Content-type: application/json\r\n".
                                      "Connection: close\r\n" .
                                      "Content-length: " . strlen($jsonData) . "\r\n",
                'content'          => $jsonData,
            ),
        );

        $context = stream_context_create($options);

        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            $response->set("success", "Data successfully added in service", $bigArr );
        } else {
            $response->set("service_failure", "Service failed to add data", array() );
        }

    } catch(Exception $e) {
        $response->set("system_failure", "System error occurred, unable to add data", array());
    } finally {
        $db = null;
        $response->toJSON();
    }
}

function productsAddUserLocalAPI() {
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
            $response->set("not_logged_on","You are not currently logged into the system", array());
            return;
        }
        
        $sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $iamdata = $stmt->fetchObject();
        
        if ($iamdata == null) {
            $response->set("service_failure","product api keys are not configured", array());
            return;
        }
        
        $sql = "SELECT user_id, email, zip FROM user WHERE user_id=:user_id";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $sessionData->user_id);
        $stmt->execute();
        $userData = $stmt->fetchObject();

        if ($userData == null) {
            $response->set("user_not_found","User was not found", array());
            return;
        }

        $userId = $iamdata->client_id ."_". $userData->user_id;
        $url = "https://api.iamdata.co:443/v1/users?client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;

        $data = array("email" => $userData->email, "zip" => $userData->zip, "user_id" => $userId);

        $jsonData = json_encode($data);

        $options = array(
            'http' => array(
                'protocol_version' => 1.1,
                'user_agent'       => 'phpRestAPIservice',
                'method'           => 'POST',
                'header'           => "Content-type: application/json\r\n".
                                      "Connection: close\r\n" .
                                      "Content-length: " . strlen($jsonData) . "\r\n",
                'content'          => $jsonData,
            ),
        );

        $context = stream_context_create($options);

        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            $response->set("success", "Data successfully added in service", $bigArr );
        } else {
            $response->set("service_failure", "Service failed to add data", array() );
        }

    } catch(Exception $e) {
        $response->set("system_failure", "System error occurred, unable to add data", array());
    } finally {
        $db = null;
        return $response;
    }
}

function productsGetStores() {
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
            $response->set("not_logged_on","You are not currently logged into the system", array());
            return;
        }

        $sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $iamdata = $stmt->fetchObject();

        if ($iamdata == null) {
            $response->set("service_failure","product api keys are not configured", array());
            return;
        }

        $url = "https://api.iamdata.co:443/v1/stores/?client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;

        $options = array(
            "http" => array(
                "header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
                "method"  => "GET"
            ),
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            $results = $bigArr["result"];

            //filter for objects with canScrape true
            $results = array_filter($results, function($obj){
                if ($obj["can_scrape"] == 0) return false;
                return true;
            });

            $response->set("success", "Data successfully fetched from service", $results );
        } else {
            $response->set("service_failure", "Service failed to return data", array() );
        }
    } catch(Exception $e) {
        $response->set("system_failure",$e->getMessage(), array());
    } finally {
        $db = null;
        $response->toJSON();
    }
}

function productsGetUserStores($page) {
    $response = new restResponse;
    $sessionId = session_id();
    $pageSize = 50;

    if ($page === NULL) {
        $pageNumber = "1";
    } else {
        $pageNumber = trim($page);
    }

    try {
        $db = getConnection();

        $sql = "SELECT user_id FROM user_session where session_id=:session_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("session_id", $sessionId);
        $stmt->execute();
        $sessionData = $stmt->fetchObject();

        if ($sessionData == null) {
            $response->set("not_logged_on","You are not currently logged into the system", array());
            return;
        }

        $sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $iamdata = $stmt->fetchObject();

        if ($iamdata == null) {
            $response->set("service_failure","product api keys are not configured", array());
            return;
        }

        $sql = "SELECT user_id FROM user WHERE user_id=:user_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $sessionData->user_id);
        $stmt->execute();
        $userData = $stmt->fetchObject();

        if ($userData == null) {
            $response->set("user_not_found","User was not found", array());
            return;
        }

        $userId = $iamdata->client_id ."_". $userData->user_id;
        $url = "https://api.iamdata.co:443/v1/users/" .$userId. "/stores?page=" .$pageNumber. "&per_page=" .$pageSize. "&client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;

        $options = array(
            "http" => array(
                "header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
                "method"  => "GET"
            ),
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            $response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            $response->set("service_failure", "Service failed to return data", array() );
        }
    } catch(Exception $e) {
        $response->set("system_failure","System error occurred, unable to return data", array());
    } finally {
        $db = null;
        $response->toJSON();
    }
}

function productsGetUserStore($userStoreId) {
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
            $response->set("not_logged_on","You are not currently logged into the system", array());
            return;
        }

        $sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $iamdata = $stmt->fetchObject();

        if ($iamdata == null) {
            $response->set("service_failure","product api keys are not configured", array());
            return;
        }

        $sql = "SELECT user_id FROM user WHERE user_id=:user_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $sessionData->user_id);
        $stmt->execute();
        $userData = $stmt->fetchObject();

        if ($userData == null) {
            $response->set("user_not_found","User was not found", array());
            return;
        }

        $userId = $iamdata->client_id ."_". $userData->user_id;
        $url = "https://api.iamdata.co:443/v1/users/" .$userId. "/stores/" .$userStoreId. "?client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;

        $options = array(
            "http" => array(
                "header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
                "method"  => "GET"
            ),
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            $response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            $response->set("service_failure", "Service failed to return data", array() );
        }
    } catch(Exception $e) {
        $response->set("system_failure","System error occurred, unable to return data", array());
    } finally {
        $db = null;
        $response->toJSON();
    }
}

function productsGetUserPurchases($daylimit, $page){
    $response = new restResponse;
    $sessionId = session_id();
    $pageSize = 50;

    if ($page === NULL) {
        $pageNumber = "1";
    } else {
        $pageNumber = trim($page);
    }

    if ($daylimit === NULL) {
        $days = "30";
    } else {
        $days = trim($daylimit);
    }

    $purchaseDateFrom = date("Ymd", strtotime("-".$days." days"));

    try{
        $db = getConnection();

        //get logged in user
        $sql = "SELECT user_id FROM user_session where session_id=:session_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("session_id", $sessionId);
        $stmt->execute();
        $sessionData = $stmt->fetchObject();

        if ($sessionData == null) {
            $response->set("not_logged_on","You are not currently logged into the system", array());
            return;
        }

        //get client_id and secret
        $sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $iamdata = $stmt->fetchObject();

        if ($iamdata == null) {
            $response->set("service_failure","product api keys are not configured", array());
            return;
        }

        // Get user id
        $sql = "SELECT user_id FROM user WHERE user_id=:user_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $sessionData->user_id);
        $stmt->execute();
        $userData = $stmt->fetchObject();

        if ($userData == null) {
            $response->set("user_not_found","User was not found", array());
            return;
        }

        //build the URL
        $userId = $iamdata->client_id ."_". $userData->user_id;
        $url = "https://api.iamdata.co:443/v1/users/" .$userId. "/purchases?full_resp=true&purchase_date_from=".$purchaseDateFrom."&page=" .$pageNumber. "&per_page=" .$pageSize. "&client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;

        $options = array(
            "http" => array(
                "header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
                "method"  => "GET"
            )
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            $response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            $response->set("service_failure", "Service failed to return data", array() );
        }
    } catch(Exception $e) {

        $response->set("system_failure", "System error occurred, unable to return data", array());
    } finally {
        $db = null;
        $response->toJSON();
    }
}

function productsAddUserStore() {
    $response = new restResponse;
    $sessionId = session_id();

    try {
        $request = Slim::getInstance()->request();
        $body = json_decode($request->getBody());

        if (!property_exists($body, 'store_id')) {
            $response->set("invalid_parameter","store_id parameter was not found", array());
            return;
        }

        if (!property_exists($body, 'username')) {
            $response->set("invalid_parameter","username parameter was not found", array());
            return;
        }

        if (!property_exists($body, 'password')) {
            $response->set("invalid_parameter","password parameter was not found", array());
            return;
        }

        $db = getConnection();

        $sql = "SELECT user_id FROM user_session where session_id=:session_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("session_id", $sessionId);
        $stmt->execute();
        $sessionData = $stmt->fetchObject();

        if ($sessionData == null) {
            $response->set("not_logged_on","You are not currently logged into the system", array());
            return;
        }

        $sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $iamdata = $stmt->fetchObject();

        if ($iamdata == null) {
            $response->set("service_failure","product api keys are not configured", array());
            return;
        }

        $sql = "SELECT user_id FROM user WHERE user_id=:user_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $sessionData->user_id);
        $stmt->execute();
        $userData = $stmt->fetchObject();

        if ($userData == null) {
            $response->set("user_not_found","User was not found", array());
            return;
        }

        $userId = $iamdata->client_id ."_". $userData->user_id;
        $url = "https://api.iamdata.co:443/v1/users/" .$userId. "/stores?client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;

        $data = array("store_id" => $body->store_id, "username" => $body->username, "password" => $body->password);

        $jsonData = json_encode($data);

        $options = array(
            'http' => array(
                'protocol_version' => 1.1,
                'user_agent'       => 'phpRestAPIservice',
                'method'           => 'POST',
                'header'           => "Content-type: application/json\r\n".
                                      "Connection: close\r\n" .
                                      "Content-length: " . strlen($jsonData) . "\r\n",
                'content'          => $jsonData,
            ),
        );

        $context = stream_context_create($options);

        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            $response->set("success", "Data successfully added to service", $bigArr );
        } else {
            $response->set("service_failure", "Service failed to add data", array() );
        }
    } catch(Exception $e) {
        $response->set("system_failure","System error occurred, unable to add data ERROR:".$e->getMessage(), array());
    } finally {
        $db = null;
        $response->toJSON();
    }
}

function productsDeleteUserStore($userStoreId) {
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
            $response->set("not_logged_on","You are not currently logged into the system", array());
            return;
        }

        $sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $iamdata = $stmt->fetchObject();

        if ($iamdata == null) {
            $response->set("service_failure","product api keys are not configured", array());
            return;
        }

        $sql = "SELECT user_id FROM user WHERE user_id=:user_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $sessionData->user_id);
        $stmt->execute();
        $userData = $stmt->fetchObject();

        if ($userData == null) {
            $response->set("user_not_found","User was not found", array());
            return;
        }

        $userId = $iamdata->client_id ."_". $userData->user_id;

        $url = "https://api.iamdata.co:443/v1/users/" .$userId. "/stores/" .$userStoreId. "?client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;

        $options = array(
            "http" => array(
                "header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
                "method"  => "DELETE"
            ),
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            $response->set("success", "Data successfully deleted from service", $bigArr );
        } else {
            $response->set("service_failure", "Service failed to delete data", array() );
        }
    } catch(Exception $e) {
        $response->set("system_failure","System error occurred, unable to delete data", array());
    } finally {
        $db = null;
        $response->toJSON();
    }
}

function productsUpdateUserStore() {
    $response = new restResponse;
    $sessionId = session_id();

    try {
        $request = Slim::getInstance()->request();
        $body = json_decode($request->getBody());

        if (!property_exists($body, 'user_store_id')) {
            $response->set("invalid_parameter","user_store_id parameter was not found", array());
            return;
        }

        if (!property_exists($body, 'username')) {
            $response->set("invalid_parameter","username parameter was not found", array());
            return;
        }

        if (!property_exists($body, 'password')) {
            $response->set("invalid_parameter","password parameter was not found", array());
            return;
        }

        $db = getConnection();

        $sql = "SELECT user_id FROM user_session where session_id=:session_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("session_id", $sessionId);
        $stmt->execute();
        $sessionData = $stmt->fetchObject();

        if ($sessionData == null) {
            $response->set("not_logged_on","You are not currently logged into the system", array());
            return;
        }

        $sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $iamdata = $stmt->fetchObject();

        if ($iamdata == null) {
            $response->set("service_failure","product api keys are not configured", array());
            return;
        }

        $sql = "SELECT user_id FROM user WHERE user_id=:user_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $sessionData->user_id);
        $stmt->execute();
        $userData = $stmt->fetchObject();

        if ($userData == null) {
            $response->set("user_not_found","User was not found", array());
            return;
        }

        $userId = $iamdata->client_id ."_". $userData->user_id;
        $url = "https://api.iamdata.co:443/v1/users/" .$userId. "/stores/" .$body->user_store_id. "/?client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;

        $data = array("username" => $body->username, "password" => $body->password);

        $jsonData = json_encode($data);

        $options = array(
            'http' => array(
                'protocol_version' => 1.1,
                'user_agent'       => 'phpRestAPIservice',
                'method'           => 'PUT',
                'header'           => "Content-type: application/json\r\n".
                                      "Connection: close\r\n" .
                                      "Content-length: " . strlen($jsonData) . "\r\n",
                'content'          => $jsonData,
            ),
        );

        $context = stream_context_create($options);

        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            $response->set("success", "Data successfully updated in service", $bigArr );
        } else {
            $response->set("service_failure", "Service failed to update data", array() );
        }
    } catch(Exception $e) {
        $response->set("system_failure","System error occurred, unable to update data", array());
    } finally {
        $db = null;
        $response->toJSON();
    }
}

function productsGetProduct($productId) {
    $response = new restResponse;


    try {
        $db = getConnection();

        $sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $iamdata = $stmt->fetchObject();

        if ($iamdata == null) {
            $response->set("service_failure","product api keys are not configured", array());
            return;
        }

        $url = "https://api.iamdata.co:443/v1/products/" .$productId. "?full_resp=true&client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;

        $options = array(
            "http" => array(
                "header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
                "method"  => "GET"
            ),
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            $response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            $response->set("service_failure", "Service failed to return data", array() );
        }
    } catch(Exception $e) {
        $response->set("system_failure","System error occurred, unable to return data", array());
    } finally {
        $db = null;
        $response->toJSON();
    }
}

function productsGetProductLocalAPI($productId) {
    $response = new restResponse;
    

    try {
        $db = getConnection();

        $sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $iamdata = $stmt->fetchObject();

        if ($iamdata == null) {
            $response->set("service_failure","product api keys are not configured", array());
            return;
        }

        $url = "https://api.iamdata.co:443/v1/products/" .$productId. "?full_resp=true&client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;

        $options = array(
            "http" => array(
                "header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
                "method"  => "GET"
            ),
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            $response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            $response->set("service_failure", "Service failed to return data", array() );
        }
    } catch(Exception $e) {
        $response->set("system_failure","System error occurred, unable to return data", array() );
    } finally {
        $db = null;
        return $response;
    }
}

?>