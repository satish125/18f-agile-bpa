<?php

function productsGetUser() {
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
        $user_id = $iamdata->client_id ."_". $user_data->user_id;
        $url = "https://api.iamdata.co:443/v1/users/" .$user_id. "?client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;

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
            $response->set("service_failure", "Service failed to return data", "" );            
        }
    } catch(Exception $e) {
		$response->set("system_failure","System error occurred, unable to return data", "");
    } finally {
        $db = null;        
		$response->toJSON();
	}
}

function productsDeleteUser() {
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
        $user_id = $iamdata->client_id ."_". $user_data->user_id;
        $url = "https://api.iamdata.co:443/v1/users?id=" .$user_id. "&client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;
        
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
            $response->set("service_failure", "Service failed to delete data", "" );            
        } 
    } catch(Exception $e) {
        $response->set("system_failure","System error occurred, unable to delete data", "");
    } finally {
        $db = null;
        $response->toJSON();
    }
}

function productsAddUser() {
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
        
        $sql = "SELECT user_id, email, zip FROM user WHERE user_id=:user_id";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $session_data->user_id);
        $stmt->execute();
        $user_data = $stmt->fetchObject();
        
        if ($user_data == null) {
			$response->set("user_not_found","User was not found", "");
            return;
        }
        
        $user_id = $iamdata->client_id ."_". $user_data->user_id;
        $url = "https://api.iamdata.co:443/v1/users?client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;
        
        $data = array("email" => $user_data->email, "zip" => $user_data->zip, "user_id" => $user_id);
        
        $json_data = json_encode($data);
        
        $options = array(
            'http' => array(
                'protocol_version' => 1.1,
                'user_agent'       => 'phpRestAPIservice',
                'method'           => 'POST',
                'header'           => "Content-type: application/json\r\n".
                                      "Connection: close\r\n" .
                                      "Content-length: " . strlen($json_data) . "\r\n",
                'content'          => $json_data,
            ),
        );
        
        $context = stream_context_create($options);
        
        $result = file_get_contents($url, false, $context);
        
        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            $response->set("success", "Data successfully added in service", $bigArr );
        } else {
            $response->set("service_failure", "Service failed to add data", "" );            
        }
        
    } catch(Exception $e) {
		$response->set("system_failure",$e->getMessage(), "");
    } finally {
        $db = null;
		$response->toJSON();
	}
}    

function productsGetStores() {
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
            $response->set("service_failure", "Service failed to return data", "" );            
        }
    } catch(Exception $e) {
		$response->set("system_failure","System error occurred, unable to return data", "");
    } finally {
        $db = null;        
		$response->toJSON();
	}
}    

function productsGetUserStores($page) {
	$response = new restResponse;
    $session_id = session_id();
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

        $user_id = $iamdata->client_id ."_". $user_data->user_id;
        $url = "https://api.iamdata.co:443/v1/users/" .$user_id. "/stores?page=" .$pageNumber. "&per_page=" .$pageSize. "&client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;

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
            $response->set("service_failure", "Service failed to return data", "" );            
        }
    } catch(Exception $e) {
		$response->set("system_failure","System error occurred, unable to return data", "");
    } finally {
        $db = null;        
		$response->toJSON();
	}
}

function productsGetUserStore($userStoreId) {
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

        $user_id = $iamdata->client_id ."_". $user_data->user_id;
        $url = "https://api.iamdata.co:443/v1/users/" .$user_id. "/stores/" .$userStoreId. "?client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;
        
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
            $response->set("service_failure", "Service failed to return data", "" );            
        }
    } catch(Exception $e) {
		$response->set("system_failure","System error occurred, unable to return data", "");
    } finally {
        $db = null;        
		$response->toJSON();
	}
}

function productsGetUserPurchases($daylimit, $page){
    $response = new restResponse;
    $session_id = session_id();

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
    
    $purchase_date_from = date("Ymd", strtotime("-".$days." days"));
    
    try{
        $db = getConnection();

        //get logged in user
        $sql = "SELECT user_id FROM user_session where session_id=:session_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("session_id", $session_id);
        $stmt->execute();
        $session_data = $stmt->fetchObject();
        
        if ($session_data == null) {
            $response->set("not_logged_on","You are not currently logged into the system", "");
            return;
        }

        //get client_id and secret
        $sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $iamdata = $stmt->fetchObject();
        
        if ($iamdata == null) {
            $response->set("service_failure","product api keys are not configured", "");
            return;
        }

        // Get user id
        $sql = "SELECT user_id FROM user WHERE user_id=:user_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $session_data->user_id);
        $stmt->execute();
        $user_data = $stmt->fetchObject();
        
        if ($user_data == null) {
            $response->set("user_not_found","User was not found", "");
            return;
        }

        //build the URL
        $user_id = $iamdata->client_id ."_". $user_data->user_id;
        $url = "https://api.iamdata.co:443/v1/users/" .$user_id. "/purchases?full_resp=true&purchase_date_from=".$purchase_date_from."&page=" .$pageNumber. "&per_page=" .$pageSize. "&client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;

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
            $response->set("service_failure", "Service failed to return data", "" );            
        }
    } catch(Exception $e) {

        $response->set("system_failure","System error occurred, unable to return data", "");
    } finally {
        $db = null;        
        $response->toJSON();
    }
}

function productsAddUserStore() {
	$response = new restResponse;
    $session_id = session_id();

    try {
		$request = Slim::getInstance()->request();
		$body = json_decode($request->getBody());

		if (!property_exists($body, 'store_id')) {
			$response->set("invalid_parameter","store_id parameter was not found", "");
			return;
		}

        if (!property_exists($body, 'username')) {
			$response->set("invalid_parameter","username parameter was not found", "");
			return;
		}

		if (!property_exists($body, 'password')) {
			$response->set("invalid_parameter","password parameter was not found", "");
			return;
		}
        
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

        $user_id = $iamdata->client_id ."_". $user_data->user_id;
        $url = "https://api.iamdata.co:443/v1/users/" .$user_id. "/stores?client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;

        $data = array("store_id" => $body->store_id, "username" => $body->username, "password" => $body->password);
        
        $json_data = json_encode($data);
        
        $options = array(
            'http' => array(
                'protocol_version' => 1.1,
                'user_agent'       => 'phpRestAPIservice',
                'method'           => 'POST',
                'header'           => "Content-type: application/json\r\n".
                                      "Connection: close\r\n" .
                                      "Content-length: " . strlen($json_data) . "\r\n",
                'content'          => $json_data,
            ),
        );
        
        $context = stream_context_create($options);
        
        $result = file_get_contents($url, false, $context);
        
        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            $response->set("success", "Data successfully added to service", $bigArr );
        } else {
            $response->set("service_failure", "Service failed to add data", "" );            
        }
    } catch(Exception $e) {
		$response->set("system_failure","System error occurred, unable to add data", "");
    } finally {
        $db = null;        
		$response->toJSON();
	}
}

function productsDeleteUserStore($userStoreId) {
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

        $user_id = $iamdata->client_id ."_". $user_data->user_id;
        
        $url = "https://api.iamdata.co:443/v1/users/" .$user_id. "/stores/" .$userStoreId. "?client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;
        
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
            $response->set("service_failure", "Service failed to delete data", "" );            
        }
    } catch(Exception $e) {
		$response->set("system_failure","System error occurred, unable to delete data", "");
    } finally {
        $db = null;        
		$response->toJSON();
	}
}

function productsUpdateUserStore() {
	$response = new restResponse;
    $session_id = session_id();

    try {
		$request = Slim::getInstance()->request();
		$body = json_decode($request->getBody());

		if (!property_exists($body, 'user_store_id')) {
			$response->set("invalid_parameter","user_store_id parameter was not found", "");
			return;
		}

        if (!property_exists($body, 'username')) {
			$response->set("invalid_parameter","username parameter was not found", "");
			return;
		}

		if (!property_exists($body, 'password')) {
			$response->set("invalid_parameter","password parameter was not found", "");
			return;
		}
        
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

        $user_id = $iamdata->client_id ."_". $user_data->user_id;
        $url = "https://api.iamdata.co:443/v1/users/" .$user_id. "/stores?client_id=" .$iamdata->client_id. "&client_secret=" .$iamdata->client_secret;

        $data = array("user_store_id" => $body->user_store_id, "username" => $body->username, "password" => $body->password);
        
        $json_data = json_encode($data);
        
        $options = array(
            'http' => array(
                'protocol_version' => 1.1,
                'user_agent'       => 'phpRestAPIservice',
                'method'           => 'PUT',
                'header'           => "Content-type: application/json\r\n".
                                      "Connection: close\r\n" .
                                      "Content-length: " . strlen($json_data) . "\r\n",
                'content'          => $json_data,
            ),
        );
        
        $context = stream_context_create($options);
        
        $result = file_get_contents($url, false, $context);
        
        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            $response->set("success", "Data successfully updated in service", $bigArr );
        } else {
            $response->set("service_failure", "Service failed to update data", "" );            
        }
    } catch(Exception $e) {
		$response->set("system_failure","System error occurred, unable to update data", "");
    } finally {
        $db = null;        
		$response->toJSON();
	}
}

?>