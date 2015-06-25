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
        $apiData = $stmt->fetchObject();
        
        if ($apiData == null) {
            $response->set("service_failure","openFDA api keys are not configured", "");
            return;
        }  
        
        $url = "https://api.fda.gov/".$type."/enforcement.json?api_key=" .$apiData->api_key. "&search=report_date:[" .$start. "+TO+" .$end. "]&limit=".$limit;
        
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

    $limit = 100;
    
    // Word exclusion list that will not be searched upon or scored upon
    $words = "about,above,across,after,against,around,at,before,behind,below,beneath,beside,besides,between,beyond,".
             "by,down,during,except,for,from,in,inside,into,like,near,,off,out,outside,over,since,through,throughout,".
             "till,toward,under,until,up,upon,with,without,according,to,because,addition,front,place,regard,".
             "spite,instead,on,account,the";

    $wordList = explode(",", $words);           
             
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
       
        // Build array of search terms for product name, filter out common words
        $productNamePieces = array();        
        
        foreach (explode(" ", $productName) as &$value) {
            if ( ! in_array(strtolower($value), array_map('strtolower', $wordList)) ) {
                array_push($productNamePieces, $value);
            }
        }
        
        // Build array of search terms for upc code, filter out common words
        $productUpcPieces = array();        
        
        foreach (explode(" ", $productName) as &$value) {
            if ( ! in_array(strtolower($value), array_map('strtolower', $wordList)) ) {
                array_push($productUpcPieces, $value);
            }
        }
        
        //get openFDA api key
        $sql = "SELECT api_key FROM api_key WHERE service_name='OPEN_FDA'";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $apiData = $stmt->fetchObject();
        
        if ($apiData == null) {
            $response->set("service_failure","openFDA api keys are not configured", "");
            return;
        }
        
        $searchParams="search=(";
        
        $first=true;
        
        // Build Searches for product name
        foreach ($productNamePieces as &$value) {
            if (!$first) {
                $searchParams .= "+"; 
            } else {
                $first=false;
            }
            $searchParams .= "product_description:" .$value. "+code_info:" .$value;
        }
        
        // Build Searches for product upc
        foreach ($productUpcPieces as &$value) {
            if (!$first) {
                $searchParams .= "+"; 
            } else {
                $first=false;
            }            
            $searchParams .= "product_description:" .$value. "+code_info:" .$value;
        }       
        
        // Add search dates
        $searchParams .= ")+AND+report_date:[" .$start. "+TO+" .$end. "]";
        
        // Add limit
        $searchParams .= "&limit=".$limit;
        
        // Build the URL
        $url = "https://api.fda.gov/".$type."/enforcement.json?" .$searchParams. "&api_key=" .$apiData->api_key;
        
        // HTTP options
        $options = array(
                "http" => array(
                "header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
                "method"  => "GET",
            ),
        );
        
        // Retrieve the content
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