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
        static::$response = new restResponse;
        static::$sessionId = session_id();

        try{
            static::$db = getConnection();
            if($doCheckHasSession){
                static::getSessionData();
            }
            static::getProductAPIKeys();
            if($doCheckUserExists){
                static::getUserData();
            }
        }
        catch(Exception $e) {
            static::$response->set("system_failure","System error occurred, unable to return data", array());
        } finally {
            static::$db = null;

            //if there is a code set, the init has failed
            if(property_exists(static::$response, '$code') && strlen(static::$response->$code) > 0){
                static::$response->toJson();
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
        $stmt = static::$db->prepare($sql);
        $stmt->bindParam("session_id", static::$sessionId);
        $stmt->execute();
        static::$sessionData = $stmt->fetchObject();

        if(static::$sessionData == null){
            static::$response->set("not_logged_on","You are not currently logged into the system", array());
        }
    }

    /**
     * may throw error
     */
    private static function getProductAPIKeys(){
        $sql = "SELECT client_id, client_secret FROM iamdata_properties";
        $stmt = static::$db->prepare($sql);
        $stmt->execute();
        static::$iamdata = $stmt->fetchObject();
        if (static::$iamdata == null) {
            static::$response->set("service_failure","product api keys are not configured", array());
            return;
        }
        static::$iamdataKeys = "client_id=" .static::$iamdata->client_id. "&client_secret=" .static::$iamdata->client_secret;
    }

    /**
     * may throw error
     */
    private static function getUserData(){
        $sql = "SELECT user_id FROM user WHERE user_id=:user_id";
        $stmt = static::$db->prepare($sql);
        $stmt->bindParam("user_id", static::$sessionData->user_id);
        $stmt->execute();
        static::$userData = $stmt->fetchObject();
        if (static::$userData == null) {
            static::$response->set("user_not_found","User was not found", array());
            return;
        }
        static::$userId = static::$iamdata->client_id ."_". static::$userData->user_id;
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
                static::$response->set("invalid_parameter","username parameter was not found", array());
            }
        }
        return true;
    }

    public static function productsGetUser() {
        if(!static::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users/".static::$userId."?".static::$iamdataKeys;

        $context = stream_context_create(static::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            static::$response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            static::$response->set("service_failure", "Service failed to return data", array() );
        }

        static::$response->toJSON();
    }//productsGetUser

    public static function productsDeleteUser() {
        if(!static::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users?id=" .static::$userId. "&".static::$iamdataKeys;

        $context = stream_context_create(static::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            if (!property_exists($bigArr, 'result')) {
                if (!property_exists($bigArr, 'message')) {
                    static::$response->set("service_failure","Service failed to return data", array());
                    return;
                } else {
                    static::$response->set("service_failure", $bigArr->message, array());
                    return;
                }
            } else {
                static::$response->set("success", "User ID has been deleted", array() );
            }
        } else {
            static::$response->set("service_failure", "Service failed to delete data", array() );
        }
        static::$response->toJSON();
    }//productsDeleteUser

    public static function productsAddUser() {
        if(!static::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users?".static::$iamdataKeys;

        $data = array("email" => $userData->email, "zip" => $userData->zip, "user_id" => $userId);

        $options = static::getJsonOptions(json_encode($data));
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            static::$response->set("success", "Data successfully added in service", $bigArr );
        } else {
            static::$response->set("service_failure", "Service failed to add data", array() );
        }
        static::$response->toJSON();
    }//productsAddUser

    public static function productsAddUserLocalAPI() {
        if(!static::init()){
            return static::$response;
        }

        $url = "https://api.iamdata.co:443/v1/users?".static::iamdataKeys;

        $data = array("email" => $userData->email, "zip" => $userData->zip, "user_id" => $userId);

        $options = static::getJsonOptions(json_encode($data));
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            static::$response->set("success", "Data successfully added in service", $bigArr );
        } else {
            static::$response->set("service_failure", "Service failed to add data", array() );
        }
        return static::$response;
    }//productsAddUserLocalAPI

    public static function productsGetUserPurchases($daylimit="30", $page="1"){
        if(!static::init()){
            return;
        }

        $pageSize = 50;
        $pageNumber = trim($page);
        $days = trim($daylimit);
        $purchaseDateFrom = date("Ymd", strtotime("-".$days." days"));

        //build the URL
        $url = "https://api.iamdata.co:443/v1/users/" .static::$userId. "/purchases?full_resp=true&purchase_date_from=".$purchaseDateFrom."&page=" .$pageNumber. "&per_page=" .$pageSize. "&".static::$iamdataKeys;

        $context = stream_context_create(static::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            static::$response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            static::$response->set("service_failure", "Service failed to return data", array() );
        }
        static::$response->toJSON();
    }//productsGetUserPurchases

    public static function productsGetStores() {
        if(!static::init(false)){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/stores/?".static::$iamdataKeys;

        $context = stream_context_create(static::$getRequestOptions);
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

            static::$response->set("success", "Data successfully fetched from service", $results );
        } else {
            static::$response->set("service_failure", "Service failed to return data", array() );
        }
        static::$response->toJSON();
    }//productsGetStores

    public static function productsGetUserStores($page="1") {
        $pageSize = 50;
        $pageNumber = trim($page);

        if(!static::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users/" .static::$userId. "/stores?page=" .$pageNumber. "&per_page=" .$pageSize. "&".static::$iamdataKeys;

        $context = stream_context_create(static::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            static::$response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            static::$response->set("service_failure", "Service failed to return data", array() );
        }

        static::$response->toJSON();
    }//productsGetUserStores

    public static function productsGetUserStore($userStoreId) {
        if(!static::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users/" .static::$userId. "/stores/" .$userStoreId. "?".static::$iamdataKeys;

        $context = stream_context_create(static::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        static::$response->toJSON();
    }//productsGetUserStore

    

    public static function productsAddUserStore() {
        $request = Slim::getInstance()->request();
        $body = json_decode($request->getBody());

        static::checkParamsExist($body, ['store_id', 'username', 'password']);

        if(!static::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users/" .static::$userId. "/stores?".static::$iamdataKeys;

        $data = array("store_id" => $body->store_id, "username" => $body->username, "password" => $body->password);

        $options = static::getJsonOptions(json_encode($data));
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            static::$response->set("success", "Data successfully added to service", $bigArr );
        } else {
            static::$response->set("service_failure", "Service failed to add data", array() );
        }
        static::$response->toJSON();
    }//productsAddUserStore

    public static function productsDeleteUserStore($userStoreId) {
        if(!static::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users/" .static::$userId. "/stores/" .$userStoreId. "?".static::$iamdataKeys;

        $context = stream_context_create(static::$deleteRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            static::$response->set("success", "Data successfully deleted from service", $bigArr );
        } else {
            static::$response->set("service_failure", "Service failed to delete data", array() );
        }
        static::$response->toJSON();
    }//productsDeleteUserStore

    public static function productsUpdateUserStore() {
        $request = Slim::getInstance()->request();
        $body = json_decode($request->getBody());

        static::checkParamsExist($body, ['user_store_id', 'username', 'password']);

        if(!static::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users/" .static::$userId. "/stores/" .$body->user_store_id. "/?".static::$iamdataKeys;

        $data = array("username" => $body->username, "password" => $body->password);

        $options = static::getJsonOptions(json_encode($data), "PUT");
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            static::$response->set("success", "Data successfully updated in service", $bigArr );
        } else {
            static::$response->set("service_failure", "Service failed to update data", array() );
        }
        static::$response->toJSON();
    }//productsUpdateUserStore

    public static function productsGetProduct($productId) {
        if(!static::init(false, false)){
            return;
        }
        $url = "https://api.iamdata.co:443/v1/products/" .$productId. "?full_resp=true&".static::$iamdataKeys;

        $context = stream_context_create(static::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            static::$response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            static::$response->set("service_failure", "Service failed to return data", array() );
        }
        static::$response->toJSON();
    }//productsGetProduct

    public static function productsGetProductLocalAPI($productId) {
        if(!static::init(false,false)){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/products/" .$productId. "?full_resp=true&".static::$iamdataKeys;

        $context = stream_context_create(static::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            static::$response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            static::$response->set("service_failure", "Service failed to return data", array() );
        }
        return static::$response;
    }//productsGetProductLocalAPI
}
?>