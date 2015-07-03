<?php
class ProductService extends ServiceTemplate{
    
    public static function productsGetUser() {
        if(!parent::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users/".parent::$userId."?".parent::$iamdataKeys;

        $context = stream_context_create(parent::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            parent::$response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            parent::$response->set("service_failure", "Service failed to return data", array() );
        }

        parent::$response->toJSON();
    }//productsGetUser

    public static function productsDeleteUser() {
        if(!parent::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users?id=" .parent::$userId. "&".parent::$iamdataKeys;

        $context = stream_context_create(parent::$deleteRequestOptions);

        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            if (!property_exists($bigArr, 'result')) {
                if (!property_exists($bigArr, 'message')) {
                    parent::$response->set("service_failure","Service failed to return data", array());
                    return;
                } else {
                    parent::$response->set("service_failure", $bigArr->message, array());
                    return;
                }
            } else {
                parent::$response->set("success", "User ID has been deleted", array() );
            }
        } else {
            parent::$response->set("service_failure", "Service failed to delete data", array() );
        }
        parent::$response->toJSON();
    }//productsDeleteUser

    public static function productsAddUser() {
        if(!parent::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users?".parent::$iamdataKeys;

        $data = array("email" => parent::$userData->email, "zip" => parent::$userData->zip, "user_id" => parent::$userId);

        $options = parent::getJsonOptions(json_encode($data));
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            parent::$response->set("success", "Data successfully added in service", $bigArr );
        } else {
            parent::$response->set("service_failure", "Service failed to add data", array() );
        }
        parent::$response->toJSON();
    }//productsAddUser

    public static function productsAddUserLocalAPI() {
        if(!parent::init()){
            return parent::$response;
        }

        $url = "https://api.iamdata.co:443/v1/users?".parent::$iamdataKeys;

        $data = array("email" => parent::$userData->email, "zip" => parent::$userData->zip, "user_id" => parent::$userId);

        $options = parent::getJsonOptions(json_encode($data));
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            parent::$response->set("success", "Data successfully added in service", $bigArr );
        } else {
            parent::$response->set("service_failure", "Service failed to add data", array() );
        }
        return parent::$response;
    }//productsAddUserLocalAPI

    public static function productsGetUserPurchases($daylimit="30", $page="1"){
        if(!parent::init()){
            return;
        }

        $pageSize = 50;
        $pageNumber = trim($page);
        $days = trim($daylimit);
        $purchaseDateFrom = date("Ymd", strtotime("-".$days." days"));

        //build the URL
        $url = "https://api.iamdata.co:443/v1/users/" .parent::$userId. "/purchases?full_resp=true&purchase_date_from=".$purchaseDateFrom."&page=" .$pageNumber. "&per_page=" .$pageSize. "&".parent::$iamdataKeys;

        $context = stream_context_create(parent::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            parent::$response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            parent::$response->set("service_failure", "Service failed to return data", array() );
        }
        parent::$response->toJSON();
    }//productsGetUserPurchases

    public static function productsGetStores() {
        if(!parent::init(false)){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/stores/?".parent::$iamdataKeys;

        $context = stream_context_create(parent::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            $results = $bigArr["result"];

            //filter for objects with canScrape true
            $results = array_filter($results, function($obj){
                return $obj["can_scrape"] != 0;
            });

            parent::$response->set("success", "Data successfully fetched from service", $results );
        } else {
            parent::$response->set("service_failure", "Service failed to return data", array() );
        }
        parent::$response->toJSON();
    }//productsGetStores

    public static function productsGetUserStores($page="1") {
        $pageSize = 50;
        $pageNumber = trim($page);

        if(!parent::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users/" .parent::$userId. "/stores?page=" .$pageNumber. "&per_page=" .$pageSize. "&".parent::$iamdataKeys;

        $context = stream_context_create(parent::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            parent::$response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            parent::$response->set("service_failure", "Service failed to return data", array() );
        }

        parent::$response->toJSON();
    }//productsGetUserStores

    public static function productsGetUserStore($userStoreId) {
        if(!parent::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users/" .parent::$userId. "/stores/" .$userStoreId. "?".parent::$iamdataKeys;

        $context = stream_context_create(parent::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            $response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            $response->set("service_failure", "Service failed to return data", array() );
        }

        parent::$response->toJSON();
    }//productsGetUserStore

    

    public static function productsAddUserStore() {
        $request = Slim::getInstance()->request();
        $body = json_decode($request->getBody());

        parent::checkParamsExist($body, ['store_id', 'username', 'password']);

        if(!parent::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users/" .parent::$userId. "/stores?".parent::$iamdataKeys;

        $data = array("store_id" => $body->store_id, "username" => $body->username, "password" => $body->password);

        $options = parent::getJsonOptions(json_encode($data));
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            parent::$response->set("success", "Data successfully added to service", $bigArr );
        } else {
            parent::$response->set("service_failure", "Service failed to add data", array() );
        }
        parent::$response->toJSON();
    }//productsAddUserStore

    public static function productsDeleteUserStore($userStoreId) {
        if(!parent::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users/" .parent::$userId. "/stores/" .$userStoreId. "?".parent::$iamdataKeys;

        $context = stream_context_create(parent::$deleteRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            parent::$response->set("success", "Data successfully deleted from service", $bigArr );
        } else {
            parent::$response->set("service_failure", "Service failed to delete data", array() );
        }
        parent::$response->toJSON();
    }//productsDeleteUserStore

    public static function productsUpdateUserStore() {
        $request = Slim::getInstance()->request();
        $body = json_decode($request->getBody());

        parent::checkParamsExist($body, ['user_store_id', 'username', 'password']);

        if(!parent::init()){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/users/" .parent::$userId. "/stores/" .$body->user_store_id. "/?".parent::$iamdataKeys;

        $data = array("username" => $body->username, "password" => $body->password);

        $options = parent::getJsonOptions(json_encode($data), "PUT");
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            parent::$response->set("success", "Data successfully updated in service", $bigArr );
        } else {
            parent::$response->set("service_failure", "Service failed to update data", array() );
        }
        parent::$response->toJSON();
    }//productsUpdateUserStore

    public static function productsGetProduct($productId) {
        if(!parent::init(false, false)){
            return;
        }
        $url = "https://api.iamdata.co:443/v1/products/" .$productId. "?full_resp=true&".parent::$iamdataKeys;

        $context = stream_context_create(parent::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            parent::$response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            parent::$response->set("service_failure", "Service failed to return data", array() );
        }
        parent::$response->toJSON();
    }//productsGetProduct

    public static function productsGetProductLocalAPI($productId) {
        if(!parent::init(false,false)){
            return;
        }

        $url = "https://api.iamdata.co:443/v1/products/" .$productId. "?full_resp=true&".parent::$iamdataKeys;

        $context = stream_context_create(parent::$getRequestOptions);
        $result = file_get_contents($url, false, $context);

        if ($result !== false) {
            $bigArr = json_decode($result, true, 20);
            parent::$response->set("success", "Data successfully fetched from service", $bigArr );
        } else {
            parent::$response->set("service_failure", "Service failed to return data", array() );
        }
        return parent::$response;
    }//productsGetProductLocalAPI
}
?>