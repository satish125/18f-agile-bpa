<?php

function openFDARecentRecalls($type, $days, $limit) {
    $response = new restResponse;
    
    $start = date("Ymd", strtotime("-".$days." days"));
    $end = date("Ymd");
    
    try {
        $db = getConnection();
        
        //get openFDA api key
        $sql = "SELECT api_key FROM api_key WHERE service_name='OPEN_FDA'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $api_data = $stmt->fetchObject();
        
        if ($api_data == null) {
            $response->set("service_failure","openFDA api keys are not configured", "");
            return;
        }  
        
        $url = "https://api.fda.gov/".$type."/enforcement.json?api_key=" .$api_data->api_key. "&search=report_date:[" .$start. "+TO+" .$end. "]&limit=".$limit; //
        
        $options = array(
                "http" => array(
                "header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
                "method"  => "GET",
            ),
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $bigArr = json_decode($result, true, 20);
        
        $response->set("success","Data successfully fetched from service", $bigArr["results"] );
    } catch(Exception $e) {
        $response->set("system_failure", "System error occurred, unable to return data", "");
    } finally {
        $response->toJSON();
    }
    
}

/**
 * match a purchase pulled in from Information Machine to FDA recall data
 * POST body - the purchase
 */
    
function openFDAProductMatch($type, $days) {
    $response = new restResponse;
    
    $start = date("Ymd", strtotime("-".$days." days"));
    $end = date("Ymd");
    
    $session_id = session_id();
    $limit = 100;
    
    try{
        $db = getConnection();        
        
        //get the request body
        $request = Slim::getInstance()->request();
        $body = json_decode($request->getBody());
        
        //retrieve request body attributes
        if ($body->source === "iamdata") {
            $productId = $body->id;
            $productName = $body->name;
            $productUpc = $body->upc;
        } else {
            $response->set("source_not_supported","Currently support is only for iamdata product information", "");
            return;
        }
        
        // Build array of search terms
        $productNamePieces = explode(" ", $productName);
        $productUpcPieces = explode(" ", $productUpc);
        
        // Filter out keywords that we don't want to search upon
        $productNamePieces = array_filter($productNamePieces, function($obj){
            if(preg_match('/the|of|all/i', $obj) === true) return false;
            return true;
        });        
        
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
        
        //get openFDA api key
        $sql = "SELECT api_key FROM api_key WHERE service_name='OPEN_FDA'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $api_data = $stmt->fetchObject();
        
        if ($api_data == null) {
            $response->set("service_failure","openFDA api keys are not configured", "");
            return;
        }
        
        $search_params="search=(";
        
        $first=true;
        
        // Build Searches for product name
        foreach ($productNamePieces as &$value) {
            if (!$first) {
                $search_params .= "+"; 
            } else {
                $first=false;
            }
            $search_params .= "product_description:" .$value. "+code_info:" .$value;
        }
        
        // Build Searches for product upc
        foreach ($productUpcPieces as &$value) {
            if (!$first) {
                $search_params .= "+"; 
            } else {
                $first=false;
            }            
            $search_params .= "product_description:" .$value. "+code_info:" .$value;
        }       
        
        // Add search dates
        $search_params .= ")+AND+report_date:[" .$start. "+TO+" .$end. "]";
        
        // Add limit
        $search_params .= "&limit=".$limit;
        

        $url = "https://api.fda.gov/".$type."/enforcement.json?" .$search_params. "&api_key=" .$api_data->api_key;
             
        $options = array(
                "http" => array(
                "header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
                "method"  => "GET",
            ),
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $bigArr = json_decode($result, true, 20);
        
        $response->set("success","Data successfully fetched from service", $bigArr );

    } catch(Exception $e) {
        $response->set("system_failure", $e->getMessage(), "");
    } finally {
        $db = null;
        $response->toJSON();
    }
}

?>