<?php

class ProductService extends restService {
    protected $dbService;
    protected $userData;
    protected $productApiData;
    protected $productApiUserId;
    protected $productApiKeys;

    function __construct() {
        // Establish Database Service
        $this->dbService = new dbService();
        
        $this->userData = $this->dbService->getUserBySessionId();             
        $this->productApiData = $this->dbService->getProductApiKey();

        // Only populate when db service returns success
        if ($this->userData->code == dbService::SUCCESS_CODE && $this->productApiData->code == dbService::SUCCESS_CODE) {
            $this->productApiUserId = $this->productApiData->client_id ."_". $this->userData->user_id;
            $this->productApiKeys = "client_id=" .$this->productApiData->client_id. "&client_secret=" .$this->productApiData->client_secret;
        }
    }
    
    function __destruct() {
        // Close database service
        $this->dbService = null;
    }
    
    protected function init() {
        if ($this->userData->code !== dbService::SUCCESS_CODE) {
            $this->setResponse("not_logged_on", "You are not currently logged into the system", array());
            return false;
        }
        
        if ($this->productApiData->code !== dbService::SUCCESS_CODE) {
            $this->setResponse(self::SYSTEM_FAILURE_CODE, "Product api keys are not configured", array());
            return false;
        }
        
        return true;
    }
    
    public function productsGetUser() {
        try {
            if(!$this->init()){
                return;
            }
        
            $url = "https://api.iamdata.co:443/v1/users/".$this->productApiUserId."?".$this->productApiKeys;

            $context = stream_context_create($this->getRequestOptions());
            $result = file_get_contents($url, false, $context);

            if ($result !== false) {
                $bigArr = json_decode($result, true, 20);
                $this->setResponse(self::SUCCESS_CODE, "Data successfully fetched from service", $bigArr );
            } else {
                $this->setResponse(self::SERVICE_FAILURE_CODE, "Service failed to return data", array() );
            }
        } catch(Exception $e) {
            $this->setResponse(self::SYSTEM_FAILURE_CODE, "System error occurred, unable to return data ".$e->getMessage(), array());
        } finally {
            $this->outputResponse();
        }

    }//productsGetUser

    public function productsDeleteUser() {
        try {
            if(!$this->init()){
                return;
            }
        
            $url = "https://api.iamdata.co:443/v1/users?id=" .$this->productApiUserId. "&".$this->productApiKeys;

            $context = stream_context_create($this->deleteRequestOptions());

            $result = file_get_contents($url, false, $context);

            if ($result !== false) {
                $bigArr = json_decode($result, true, 20);
                if (!property_exists($bigArr, 'result')) {
                    if (!property_exists($bigArr, 'message')) {
                        $this->setResponse(self::SERVICE_FAILURE_CODE,"Service failed to return data", array());
                        return;
                    } else {
                        $this->setResponse(self::SERVICE_FAILURE_CODE, $bigArr->message, array());
                        return;
                    }
                } else {
                    $this->setResponse(self::SUCCESS_CODE, "User ID has been deleted", array() );
                }
            } else {
                $this->setResponse(self::SERVICE_FAILURE_CODE, "Service failed to delete user", array() );
            }
        } catch(Exception $e) {
            $this->setResponse(self::SYSTEM_FAILURE_CODE, "System error occurred, unable to delete user", array());
        } finally {
            $this->outputResponse();
        }
    }//productsDeleteUser

    public function productsAddUser() {
        try {
            if(!$this->init()){
                return;
            }

            $url = "https://api.iamdata.co:443/v1/users?".$this->productApiKeys;

            $data = array("email" => $this->userData->email, "zip" => $this->userData->zip, "user_id" =>$this->productApiUserId);

            $options = $this->getJsonOptions(json_encode($data));
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);

            if ($result !== false) {
                $bigArr = json_decode($result, true, 20);
                $this->setResponse(self::SUCCESS_CODE, "User successfully added in service", $bigArr );
            } else {
                $this->setResponse(self::SERVICE_FAILURE_CODE, "Service failed to add user", array() );
            }
        } catch(Exception $e) {
            $this->setResponse(self::SYSTEM_FAILURE_CODE, "System error occurred, unable to add user", array());
        } finally {
            $this->outputResponse();
        }
    }//productsAddUser

    public function productsAddUserLocalAPI() {
        try {        
            if(!$this->init()){
                return;
            }

            $url = "https://api.iamdata.co:443/v1/users?".$this->productApiKeys;

            $data = array("email" => $this->userData->email, "zip" => $this->userData->zip, "user_id" => $this->productApiUserId);

            $options = $this->getJsonOptions(json_encode($data));
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);

            if ($result !== false) {
                $bigArr = json_decode($result, true, 20);
                $this->setResponse(self::SUCCESS_CODE, "User successfully added in service", $bigArr );
            } else {
                $this->setResponse(self::SERVICE_FAILURE_CODE, "Service failed to add user", array() );
            }
        } catch(Exception $e) {
            $this->setResponse(self::SYSTEM_FAILURE_CODE, "System error occurred, unable to add user", array());
        } finally {
            return $this->getResponse();
        }
    }//productsAddUserLocalAPI

    public function productsGetUserPurchases($daylimit="30", $page="1"){
        try {        
            if(!$this->init()){
                return;
            }

            $pageSize = 50;
            $pageNumber = trim($page);
            $days = trim($daylimit);
            $purchaseDateFrom = date("Ymd", strtotime("-".$days." days"));

            //build the URL
            $url = "https://api.iamdata.co:443/v1/users/" .$this->productApiUserId. "/purchases?full_resp=true&purchase_date_from=".$purchaseDateFrom."&page=" .$pageNumber. "&per_page=" .$pageSize. "&".$this->productApiKeys;

            $context = stream_context_create($this->getRequestOptions());
            $result = file_get_contents($url, false, $context);

            if ($result !== false) {
                $bigArr = json_decode($result, true, 20);
                $this->setResponse(self::SUCCESS_CODE, "Data successfully fetched from service", $bigArr );
            } else {
                $this->setResponse(self::SERVICE_FAILURE_CODE, "Service failed to return data", array() );
            }
        } catch(Exception $e) {
            $this->setResponse(self::SYSTEM_FAILURE_CODE, "System error occurred, unable to add user", array());
        } finally {
            $this->outputResponse();
        }
    }//productsGetUserPurchases

    public function productsGetStores() {
        try {        
            if(!$this->init()){
                return;
            }

            $url = "https://api.iamdata.co:443/v1/stores/?".$this->productApiKeys;

            $context = stream_context_create($this->getRequestOptions());
            $result = file_get_contents($url, false, $context);

            if ($result !== false) {
                $bigArr = json_decode($result, true, 20);
                $results = $bigArr["result"];

                //filter for objects with canScrape true
                $results = array_filter($results, function($obj){
                    return $obj["can_scrape"] != 0;
                });

                $this->setResponse(self::SUCCESS_CODE, "Data successfully fetched from service", $results );
            } else {
                $this->setResponse(self::SERVICE_FAILURE_CODE, "Service failed to return data", array() );
            }
        } catch(Exception $e) {
            $this->setResponse(self::SYSTEM_FAILURE_CODE, "System error occurred, unable to add user", array());
        } finally {
            $this->outputResponse();
        }
    }//productsGetStores

    public function productsGetUserStores($page="1") {
        try {
            if(!$this->init()){
                return;
            }
            $pageSize = 50;
            $pageNumber = trim($page);
            
            $url = "https://api.iamdata.co:443/v1/users/" .$this->productApiUserId. "/stores?page=" .$pageNumber. "&per_page=" .$pageSize. "&".$this->productApiKeys;

            $context = stream_context_create($this->getRequestOptions());
            $result = file_get_contents($url, false, $context);

            if ($result !== false) {
                $bigArr = json_decode($result, true, 20);
                $this->setResponse(self::SUCCESS_CODE, "Data successfully fetched from service", $bigArr );
            } else {
                $this->setResponse(self::SERVICE_FAILURE_CODE, "Service failed to return data", array() );
            }

        } catch(Exception $e) {
            $this->setResponse(self::SYSTEM_FAILURE_CODE, "System error occurred, unable to get user stores", array());
        } finally {
            $this->outputResponse();
        }
    }//productsGetUserStores

    public function productsGetUserStore($userStoreId) {
        try {
            if(!$this->init()){
                return;
            }
        
            $url = "https://api.iamdata.co:443/v1/users/" .$this->productApiUserId. "/stores/" .$userStoreId. "?".$this->productApiKeys;

            $context = stream_context_create($this->getRequestOptions());
            $result = file_get_contents($url, false, $context);

            if ($result !== false) {
                $bigArr = json_decode($result, true, 20);
                $this->setResponse(self::SUCCESS_CODE, "Data successfully fetched from service", $bigArr );
            } else {
                $this->setResponse(self::SERVICE_FAILURE_CODE, "Service failed to return data", array() );
            }
        } catch(Exception $e) {
            $this->setResponse(self::SYSTEM_FAILURE_CODE, "System error occurred, unable to get user store", array());
        } finally {
            $this->outputResponse();
        }
    }//productsGetUserStore


    public function productsAddUserStore() {
        try {
            if(!$this->init()){
                return;
            }
            $request = \Slim\Slim::getInstance()->request();
            $body = json_decode($request->getBody());

            if(!self::checkParamsExist($body, ['store_id' => null, 'username' => null, 'password' => null])) {
                return;
            }

            $url = "https://api.iamdata.co:443/v1/users/" .$this->productApiUserId. "/stores?".$this->productApiKeys;

            $data = array("store_id" => $body->store_id, "username" => $body->username, "password" => $body->password);

            $options = $this->getJsonOptions(json_encode($data));
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);

            if ($result !== false) {
                $bigArr = json_decode($result, true, 20);
                $this->setResponse(self::SUCCESS_CODE, "Data successfully added to service", $bigArr );
            } else {
                $this->setResponse(self::SERVICE_FAILURE_CODE, "Service failed to add user store", array() );
            }
        } catch(Exception $e) {
            $this->setResponse(self::SYSTEM_FAILURE_CODE, "System error occurred, unable to get add user store", array());
        } finally {
            $this->outputResponse();
        }
    }//productsAddUserStore

    public function productsDeleteUserStore($userStoreId) {
        try {
            if(!$this->init()){
                return;
            }
            $url = "https://api.iamdata.co:443/v1/users/" .$this->productApiUserId. "/stores/" .$userStoreId. "?".$this->productApiKeys;

            $context = stream_context_create($this->deleteRequestOptions());
            $result = file_get_contents($url, false, $context);

            if ($result !== false) {
                $bigArr = json_decode($result, true, 20);
                $this->setResponse(self::SUCCESS_CODE, "Data successfully deleted from service", $bigArr );
            } else {
                $this->setResponse(self::SERVICE_FAILURE_CODE, "Service failed to delete user store", array() );
            }
        } catch(Exception $e) {
            $this->setResponse(self::SYSTEM_FAILURE_CODE, "System error occurred, unable to delete user store", array());
        } finally {
            $this->outputResponse();
        }
    }//productsDeleteUserStore

    public function productsUpdateUserStore() {
        try {
            if(!$this->init()){
                return;
            }            
            
            $request = \Slim\Slim::getInstance()->request();
            $body = json_decode($request->getBody());

            if(!self::checkParamsExist($body, ['user_store_id' => null, 'username' => null, 'password' => null])){
                return;
            }

            $url = "https://api.iamdata.co:443/v1/users/" .$this->productApiUserId. "/stores/" .$body->user_store_id. "/?".$this->productApiKeys;

            $data = array("username" => $body->username, "password" => $body->password);

            $options = $this->getJsonOptions(json_encode($data), "PUT");
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);

            if ($result !== false) {
                $bigArr = json_decode($result, true, 20);
                $this->setResponse(self::SUCCESS_CODE, "Data successfully updated in service", $bigArr );
            } else {
                $this->setResponse(self::SERVICE_FAILURE_CODE, "Service failed to update user store", array() );
            }
        } catch(Exception $e) {
            $this->setResponse(self::SYSTEM_FAILURE_CODE, "System error occurred, unable to update user store", array());
        } finally {
            $this->outputResponse();
        }
    }//productsUpdateUserStore

    public function productsGetProduct($productId) {
        try {
            if(!$this->init()){
                return;
            }   
            $url = "https://api.iamdata.co:443/v1/products/" .$productId. "?full_resp=true&".$this->productApiKeys;

            $context = stream_context_create($this->getRequestOptions());
            $result = file_get_contents($url, false, $context);

            if ($result !== false) {
                $bigArr = json_decode($result, true, 20);
                $this->setResponse(self::SUCCESS_CODE, "Data successfully fetched from service", $bigArr );
            } else {
                $this->setResponse(self::SERVICE_FAILURE_CODE, "Service failed to get product", array() );
            }
        } catch(Exception $e) {
            $this->setResponse(self::SYSTEM_FAILURE_CODE, "System error occurred, unable to get product", array());
        } finally {
            $this->outputResponse();
        }            
    }//productsGetProduct

    public function productsGetProductLocalAPI($productId) {
        try {
            if(!$this->init()){
                return;
            } 

            $url = "https://api.iamdata.co:443/v1/products/" .$productId. "?full_resp=true&".$this->productApiKeys;

            $context = stream_context_create($this->getRequestOptions());
            $result = file_get_contents($url, false, $context);

            if ($result !== false) {
                $bigArr = json_decode($result, true, 20);
                $this->setResponse(self::SUCCESS_CODE, "Data successfully fetched from service", $bigArr['result'] );
            } else {
                $this->setResponse(self::SERVICE_FAILURE_CODE, "Service failed to get product", array() );
            }
        } catch(Exception $e) {
            $this->setResponse(self::SYSTEM_FAILURE_CODE, "System error occurred, unable to get product", array());
        } finally {
            return $this->getResponse();
        }
    }//productsGetProductLocalAPI
}
?>