<?php
class ServiceTemplate {
	protected static $db;
	protected static $sessionId;
    protected static $response;
    protected static $sessionData;
    protected static $userData;
    protected static $userId;
    protected static $iamdataKeys;
    protected static $iamdata;

    protected static $getRequestOptions= array(
        "http" => array(
            "header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
            "method"  => "GET"
        ),
    );
    protected static $deleteRequestOptions = array(
        "http" => array(
            "header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
            "method"  => "DELETE"
        ),
    );

    protected static function init($doCheckUserExists=true, $doCheckHasSession=true){
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
        $sql = "SELECT user_id, email, zip FROM user WHERE user_id=:user_id";
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

    protected static function getJsonOptions($jsonData, $method="POST"){
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

    protected static function checkParamsExist($body, $params){
        foreach($params as $param){
            if (!property_exists($body, $param)) {
                static::$response->set("invalid_parameter","username parameter was not found", array());
            }
        }
        return true;
    }
}
?>