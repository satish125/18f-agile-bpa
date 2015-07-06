<?php

class UserService extends restService {
    protected $dbService;    
    protected $sessionId;
    
    function __construct() {
        // Establish Database Service
        $this->dbService = new dbService();        
        
        $this->sessionId = session_id();
    } 
    
    function __destruct() {
        // Clear Session
        $this->sessionId = null;
        
        // Close database service
        $this->dbService = null;
    }        
    
	public function userLogin() {

	    try {
			$request = \Slim\Slim::getInstance()->request();
			$body = json_decode($request->getBody());
            
            // Validate request body has email and password parameters
            if(! self::checkParamsExist($body, ['email' => 'Email address is required',
                                                'password' => 'Password is required'])) {
                return;
            }            
            
            // Retrieve User data by email address
            $userData = $this->dbService->doValidatePassword($body->email, $body->password); 

	        if ($userData->code !== dbService::SUCCESS_CODE) {
				$this->setResponse(dbService::INVALID_CREDENTIALS_CODE, "Email address and/or password was invalid", array());
			} else {
                try {
                    $doLogin = $this->dbService->doSessionLogin($userData->user_id);
                    
                    if ($doLogin->code !== dbService::SUCCESS_CODE) {
                        $this->setResponse(self::SYSTEM_FAILURE_CODE, "System error occurred, unable to login", array());
                    } else {
                        $this->setResponse(self::SUCCESS_CODE, "User was authenticated", array("SESSION_ID" => $this->sessionId) );   
                    }
                } catch(Exception $e) {
                    $this->setResponse(self::SYSTEM_FAILURE_CODE, "System error occurred, unable to login", array());
                }
	        }
	    } catch(Exception $e) {
			$this->setResponse(self::SYSTEM_FAILURE_CODE, "System error occurred, unable to login", array());
	    } finally {
            $this->outputResponse();
		}
	}

	public function userRegister() {
	    try {
			$request = \Slim\Slim::getInstance()->request();
			$body = json_decode($request->getBody());
            
            // Validate request body has email, zipcode and password parameters
            if(! self::checkParamsExist($body, ['email' => 'Email address is required',
                                                'zipcode' => 'Zipcode is required',
                                                'password' => 'Password is required'])) {
                return;
            }                   
	        
	        $email = strtoupper($body->email);
	        
	        // Check if the user already exists in the database
            $userData = $this->dbService->getUserByEmail($email);             
	        
            if ($userData->code == dbService::SUCCESS_CODE) {
				$this->setResponse(self::SYSTEM_FAILURE_CODE, "User with Email address already exists", array());
			} else {
	            
	            // Has Password
	            $passwordHash = createHashedPassword($body->password);
	            
	            // Register the user
                $registerUser = $this->dbService->registerUser($body->email, $body->zipcode, $passwordHash);
                
                if ($registerUser->code !== dbService::SUCCESS_CODE || $registerUser->user_id == null) {
                    $this->setResponse(self::SYSTEM_FAILURE_CODE, "User registration has failed to complete - " .$registerUser->code, array());
                    return;
                }

	            // Auto register user in products service (iamdata)
                $productService = new productService();
                
	            $productAddUser = $productService->productsAddUserLocalAPI();

                if ($productAddUser->code !== self::SUCCESS_CODE) {                
	                $this->setResponse($productAddUser->code, $productAddUser->msg, $productAddUser->payload);
	            } else {
                    $this->setResponse(self::SUCCESS_CODE, "User was registered", array());       
                }     
	        }
	    } catch(Exception $e) {
	        $this->setResponse(self::SYSTEM_FAILURE_CODE, "System error occurred, unable save user", array());
	    } finally {
	        try {
	            if ($this->code !== self::SUCCESS_CODE) {       
                    $deleteUser = $this->dbService->deleteUser($registerUser->user_id);                
	            }
	        } catch(Exception $e) {
	            // Do nothing, we tried to clean up the account, so just give up at this point
	        }
            $this->outputResponse();
		}

	}

	public function userGet() {
        try {
            $userData = $this->dbService->getUserBySessionId();
            
            if ($userData->code == dbService::SUCCESS_CODE) {  
                $this->setResponse(self::SUCCESS_CODE, "User is logged into the system", $userData); 
            } else {
                $this->setResponse(self::NO_DATA_FOUND_CODE, "User is not logged into the system", $userData);
            }
	    } catch(Exception $e) {
	        $this->setResponse(self::SYSTEM_FAILURE_CODE, "System error occurred, unable retrieve user", array());
	    } finally {
            $this->outputResponse();
        }
	}

	public function userLogout() {
	    try {
            $doLogout = $this->dbService->doSessionLogout();
	    } catch(Exception $e) {
			// Do nothing
	    } finally {
	        // Always respond with success
	        $this->setResponse(self::SUCCESS_CODE, "User logged out successfully", array());
	    	$this->outputResponse();
		}
	}
     
}

?>