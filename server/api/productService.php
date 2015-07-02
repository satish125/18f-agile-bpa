<?php
class ProductService {
    private static $db;
    private static $sessionId;
    private static $response;
    private static $sessionData;
    private static $userData;
    private static $userId;
    private static $iamdataKeys;
    private static $iamdata;

    private static $getRequestOptions= array(
        "http" => array(
            "header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
            "method"  => "GET"
        ),
    );
    private static $deleteRequestOptions = array(
        "http" => array(
            "header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
            "method"  => "DELETE"
        ),
    );

    private static function init($doCheckUserExists=true, $doCheckHasSession=true){
        self::$response = new restResponse;
        self::$sessionId = session_id();

        try{
            self::$db = getConnection();
            if($doCheckHasSession){
                self::getSessionData();
            }
            self::getProductAPIKeys();
            if($doCheckUserExists){
                self::getUserData();
            }
        }
        catch(Exception $e) {
            self::$response->set("system_failure","System error occurred, unable to return data", array());
        } finally {
            self::$db = null;

            //if there is a code set, the init has failed
            if(property_exists(self::$response, '$code') && strlen(self::$response->$code) > 0){
                self::$response->toJson();
                return false;
            }
            return true;
        }
    }//init

    /**
     * may throw exception
     */
    private static function getSessionData(){
        $sql = "SELECT user_id FROM user_session where session_id=:session_id";
        $stmt = self::$db->prepare($sql);
        $stmt->bindParam("session_id", self::$sessionId);
        $stmt->execute();
        self::$sessionData = $stmt->fetchObject();

        if(self::$sessionData == null){
            self::$response->set("not_logged_on","You are not currently logged into the system", array());
        }
    }

    /**
     * may throw error
     */
    private static function getProductAPIKeys(){
        $sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        self::$iamdata = $stmt->fetchObject();
        if (self::$iamdata == null) {
            self::$response->set("service_failure","product api keys are not configured", array());
            return;
        }
        self::$iamdataKeys = "client_id=" .self::$iamdata->client_id. "&client_secret=" .self::$iamdata->client_secret;
    }

    /**
     * may throw error
     */
    private static function getUserData(){
        $sql = "SELECT user_id FROM user WHERE user_id=:user_id";
        $stmt = self::$db->prepare($sql);
        $stmt->bindParam("user_id", self::$sessionData->user_id);
        $stmt->execute();
        self::$userData = $stmt->fetchObject();
        if (self::$userData == null) {
            self::$response->set("user_not_found","User was not found", array());
            return;
        }
        self::$userId = self::$iamdata->client_id ."_". self::$userData->user_id;
    }

    private static function getJsonOptions($jsonData, $method="POST"){
        return array(
            'http' => array(
                'protocol_version' => 1.1,
                'user_agent'       => 'phpRestAPIservice',
                'method'           => $method,
                'header'           => "Content-type: application/json\r\n".
                                      "Connection: close\r\n" .
                                      "Content-length: " . strlen($jsonData) . "\r\n",
                'content'          => $jsonData,
            ),
        );
    }

    private static function checkParamsExist($body, $params){
        foreach($params as $param){
            if (!property_exists($body, $param)) {
                self::$response->set("invalid_parameter","username parameter was not found", array());
            }
        }
        return true;
    }

    public static function productsGetUser() {
        if(!self::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users/".self::$userId."?".self::$iamdataKeys;

        $context = stream_context_create(self::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            self::$response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            self::$response->set("service_failure", "Service failed to return data", array() );
        }

        self::$response->toJSON();
    }//productsGetUser

    public static function productsDeleteUser() {
        if(!self::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users?id=" .self::$userId. "&".self::$iamdataKeys;

        $context = stream_context_create(self::$deleteRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            if (!property_exists($bigArr, 'result')) {
                if (!property_exists($bigArr, 'message')) {
                    self::$response->set("service_failure","Service failed to return data", array());
                    return;
                } else {
                    self::$response->set("service_failure", $bigArr->message, array());
                    return;
                }
            } else {
                self::$response->set("success", "User ID has been deleted", array() );
            }
        } else {
            self::$response->set("service_failure", "Service failed to delete data", array() );
        }
        self::$response->toJSON();
    }//productsDeleteUser

    public static function productsAddUser() {
        if(!self::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users?".self::$iamdataKeys;

        $data = array("email" => $userData->email, "zip" => $userData->zip, "user_id" => $userId);

        $options = self::getJsonOptions(json_encode($data));
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            self::$response->set("success", "Data successfully added in service", $bigArr );
        } else {
            self::$response->set("service_failure", "Service failed to add data", array() );
        }
        self::$response->toJSON();
    }//productsAddUser

    public static function productsAddUserLocalAPI() {
        if(!self::init()){
            return self::$response;
        }

        $url = "https://api.iamdata.co:443/v1/users?".self::iamdataKeys;

        $data = array("email" => $userData->email, "zip" => $userData->zip, "user_id" => $userId);

        $options = self::getJsonOptions(json_encode($data));
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            self::$response->set("success", "Data successfully added in service", $bigArr );
        } else {
            self::$response->set("service_failure", "Service failed to add data", array() );
        }
        return self::$response;
    }//productsAddUserLocalAPI

    public static function productsGetUserPurchases($daylimit="30", $page="1"){
        if(!self::init()){
            return;
        }

        $pageSize = 50;
        $pageNumber = trim($page);
        $days = trim($daylimit);
        $purchaseDateFrom = date("Ymd", strtotime("-".$days." days"));

        //build the URL
        $url = "https://api.iamdata.co:443/v1/users/" .self::$userId. "/purchases?full_resp=true&purchase_date_from=".$purchaseDateFrom."&page=" .$pageNumber. "&per_page=" .$pageSize. "&".self::$iamdataKeys;

        $context = stream_context_create(self::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            self::$response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            self::$response->set("service_failure", "Service failed to return data", array() );
        }
        self::$response->toJSON();
    }//productsGetUserPurchases

    public static function productsGetStores() {
        if(!self::init(false)){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/stores/?".self::$iamdataKeys;

        $context = stream_context_create(self::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            $results = $bigArr["result"];

            //filter for objects with canScrape true
            $results = array_filter($results, function($obj){
                if ($obj["can_scrape"] == 0) {
                    return false;
                } else {
                    return true;
                }
            });

            self::$response->set("success", "Data successfully fetched from service", $results );
        } else {
            self::$response->set("service_failure", "Service failed to return data", array() );
        }
        self::$response->toJSON();
    }//productsGetStores

    public static function productsGetUserStores($page="1") {
        $pageSize = 50;
        $pageNumber = trim($page);

        if(!self::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users/" .self::$userId. "/stores?page=" .$pageNumber. "&per_page=" .$pageSize. "&".self::$iamdataKeys;

        $context = stream_context_create(self::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            self::$response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            self::$response->set("service_failure", "Service failed to return data", array() );
        }

        self::$response->toJSON();
    }//productsGetUserStores

    public static function productsGetUserStore($userStoreId) {
        if(!self::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users/" .self::$userId. "/stores/" .$userStoreId. "?".self::$iamdataKeys;

        $context = stream_context_create(self::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        self::$response->toJSON();
    }//productsGetUserStore

    

    public static function productsAddUserStore() {
        $request = Slim::getInstance()->request();
        $body = json_decode($request->getBody());

        self::checkParamsExist($body, ['store_id', 'username', 'password']);

        if(!self::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users/" .self::$userId. "/stores?".self::$iamdataKeys;

        $data = array("store_id" => $body->store_id, "username" => $body->username, "password" => $body->password);

        $options = self::getJsonOptions(json_encode($data));
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            self::$response->set("success", "Data successfully added to service", $bigArr );
        } else {
            self::$response->set("service_failure", "Service failed to add data", array() );
        }
        self::$response->toJSON();
    }//productsAddUserStore

    public static function productsDeleteUserStore($userStoreId) {
        if(!self::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users/" .self::$userId. "/stores/" .$userStoreId. "?".self::$iamdataKeys;

        $context = stream_context_create(self::$deleteRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            self::$response->set("success", "Data successfully deleted from service", $bigArr );
        } else {
            self::$response->set("service_failure", "Service failed to delete data", array() );
        }
        self::$response->toJSON();
    }//productsDeleteUserStore

    public static function productsUpdateUserStore() {
        $request = Slim::getInstance()->request();
        $body = json_decode($request->getBody());

        self::checkParamsExist($body, ['user_store_id', 'username', 'password']);

        if(!self::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users/" .self::$userId. "/stores/" .$body->user_store_id. "/?".self::$iamdataKeys;

        $data = array("username" => $body->username, "password" => $body->password);

        $options = self::getJsonOptions(json_encode($data), "PUT");
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            self::$response->set("success", "Data successfully updated in service", $bigArr );
        } else {
            self::$response->set("service_failure", "Service failed to update data", array() );
        }
        self::$response->toJSON();
    }//productsUpdateUserStore

    public static function productsGetProduct($productId) {
        if(!self::init(false, false)){
            return;
        }
        $url = "https://api.iamdata.co:443/v1/products/" .$productId. "?full_resp=true&".self::$iamdataKeys;

        $context = stream_context_create(self::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            self::$response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            self::$response->set("service_failure", "Service failed to return data", array() );
        }
        self::$response->toJSON();
    }//productsGetProduct

    public static function productsGetProductLocalAPI($productId) {
        if(!self::init(false,false)){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/products/" .$productId. "?full_resp=true&".self::$iamdataKeys;

        $context = stream_context_create(self::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            self::$response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            self::$response->set("service_failure", "Service failed to return data", array() );
        }
        return self::$response;
    }//productsGetProductLocalAPI
}
?>