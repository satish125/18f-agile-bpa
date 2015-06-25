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
        
        $url = "https://api.fda.gov/".$type."/enforcement.json?api_key=" .$apiData->api_key. "&search=recall_initiation_date:[" .$start. "+TO+" .$end. "]&limit=".$limit;
        
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

function wordListParser($text, $wordListMap) {
     
    // Build array of search terms for product name, filter out common words and special characters
    $wordList = array();        
    
    foreach (explode(" ", $text) as &$value) {
        if ( ! in_array($value, $wordListMap) ) {
            $safeString=preg_replace('/[^A-Za-z0-9\-]/', '', $value);
            if (! in_array($safeString, $wordList)) {                
                array_push($wordList, $safeString);
            }
        }
    }    
    return $wordList;
} 
 
function openFDAProductMatch($type, $days) {
    $response = new restResponse;
    
    $start = date("Ymd", strtotime("-".$days." days"));
    $end = date("Ymd");

    $limit = 100;
    
    // Word exclusion list that will not be searched upon or scored upon
    $words = "about,above,across,after,against,around,at,before,behind,below,beneath,beside,besides,between,beyond,".
             "by,down,during,except,for,from,in,inside,into,like,near,,off,out,outside,over,since,through,throughout,".
             "till,toward,under,until,up,upon,with,without,according,to,because,addition,front,place,regard,".
             "spite,instead,on,account,the,and";
        
    $wordListMap = array_map('strtolower', explode(",", $words));
             
    try{
        $db = getConnection();
        
        // Initialize post request capture fields
        $productSource = "";
        $productId = "";
        $productName = "";
        $productUpc = "";
        
        //get the request body
        $request = Slim::getInstance()->request();
        $body = json_decode($request->getBody());    

        // Fail if product source is not found
        if (!property_exists($body, 'source')) {
			$response->set("missing_product_source","Product source is a required parameter", "");
			return;
        } else {
            $productSource = $body->source;
        }
        
        //retrieve request body attributes
        if ($productSource === "iamdata") {
            if (property_exists($body, 'id')) {
                $productId = $body->id;
            }
            if (property_exists($body, 'name')) {
                $productName = $body->name;
            }
            if (property_exists($body, 'upc')) {
                $productUpc = $body->upc;
            }
        } else {
            $response->set("source_not_supported","No support exists for the product source provided", "");
            return;
        }
        
        // Replace all hyphens with a space in the product name and convert to lower case
        $productName = str_replace('-', ' ', strtolower($productName));
        
        // Build array of search terms for product name, filter out common words and special characters
        $productNamePieces = wordListParser($productName, $wordListMap);
        
        // Remove all hyphens in the product upc and convert to lower case
        $productUpc = str_replace('-', '', strtolower($productUpc));

        // Build array of search terms for product name, filter out common words and special characters
        $productUpcPieces = wordListParser($productUpc, $wordListMap);
        
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
        
        // If the API call has returned an error then capture it and return the code/message to the caller
        if (array_key_exists('error',$bigArr)) {
            $response->set($bigArr['error']['code'], $bigArr['error']['message'], "" );
            return;
        }
        
        // Exit with an error if the service did not contain a results array
        if (!array_key_exists('results',$bigArr)) {
            $response->set("results_failure","The api did not contain any results", "");
            return;
        }
        
        // Iterate thru each search result
        foreach ($bigArr['results'] as $idx => $idxVal) {
           
            // Build array of terms for product name, filter out common words and special characters
            if ( !array_key_exists('product_description', $idxVal) ) {
                $resultProductNamePieces = array();   
            } else {
                $resultProductName =  str_replace('-', ' ', strtolower($idxVal['product_description']));
                $resultProductNamePieces = wordListParser($resultProductName, $wordListMap);
            }
            
            // Find matching terms for product upc
            $matchingProductNamePieces = array_intersect ($productNamePieces, $resultProductNamePieces);
            

            // Build array of terms for product ups, filter out common words and special characters
            if (!array_key_exists('code_info', $idxVal)) {
                $resultProductUpcPieces = array();   
            } else {
                $resultProductUpc =  str_replace('-', ' ', strtolower($idxVal['code_info']));   
                $resultProductUpcPieces = wordListParser($resultProductUpc, $wordListMap);
            }
            
            // Find matching terms
            $matchingProductUpcPieces = array_intersect ($productUpcPieces, $resultProductUpcPieces);
            
            // Calculating matching score
            $matchingScore = ( count($matchingProductNamePieces) / count($resultProductNamePieces) ) + 
                             ( count($matchingProductUpcPieces) / count($resultProductUpcPieces) );
                          
            $bigArr['results'][$idx]['matching_score']=round($matchingScore,2);
            
        }
     
        //var_dump ($bigArr);
        $response->set("success","Data successfully fetched from service", $bigArr['results'] );

    } catch(Exception $e) {
        $response->set("system_failure", $e->getMessage(), "");
    } finally {
        $db = null;
        $response->toJSON();
    }
}

?>