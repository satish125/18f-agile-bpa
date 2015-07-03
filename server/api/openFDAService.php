<?php

class OpenFDAService extends ServiceTemplate{
    /**
     * Pull recent enforcement data from open FDA interface.
     */

    public static function openFDARecentRecalls($type, $days, $limit) {
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
                $response->set("service_failure","openFDA api keys are not configured", array());
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
            $response->set("system_failure", "System error occurred, unable to return data", array());
        } finally {
            $response->toJSON();
        }

    }

    /**
     * Word parser that separates a text string containing the product name into an array.  All 
     * special characters and words in the exclusion word list are removed from the array.
     */

    private static function productNameParser($text, $exclusionWords) {

        // Build array of search terms for product name, filter out common words and special characters
        $wordList = array();
        
        // Remove Special Characters, retain a-z, A-Z and spaces
        $text = preg_replace('/[^a-zA-Z ]+/', ' ', $text);
        
        // Remove extra spaces
        $text = preg_replace('!\s{2,}!', ' ', $text);
        
        // Exclude all words that are in the exclusion list
        $wordList = array_diff(explode(" ", $text), $exclusionWords);
        
        // filter out words that are less than three characters in length
        $wordList = array_filter($wordList, function($obj){
            if (strlen($obj) < 3) {
                return false;
            } else {
                return true;
            }
        });
        
        // Return Unique list of words   
        return array_unique($wordList);
    }

    /**
     * Word parser that separates a text string containing the product upc into an array.  All 
     * special characters and words in the exclusion word list are removed from the array.
     */

    private static function productUpcParser($text, $exclusionWords) {

        // Build array of search terms for product name, filter out common words and special characters
        $wordList = array();
        
        // Remove Special Characters, retain 0-9 and spaces
        $text = preg_replace('/[^a-zA-Z0-9 ]+/', ' ', $text);
        
        // Remove extra spaces
        $text = preg_replace('!\s{2,}!', ' ', $text);
        
        // Exclude all words that are in the exclusion list
        $wordList = array_diff(explode(" ", $text), $exclusionWords);
        
        // filter out words that are less than three characters in length
        $wordList = array_filter($wordList, function($obj){
            if (strlen($obj) < 3) {
                return false;
            } else {
                return true;
            }
        });
        
        // Return Unique list of words   
        return array_unique($wordList);
    }

    /**
     * match a purchase pulled in from Information Machine to FDA recall data
     * POST body - the purchase
     */

    public static function openFDAProductMatch($type, $days, $minMatchingScore, $minQualityScore) {
        $response = new restResponse;

        $start = date("Ymd", strtotime("-".$days." days"));
        $end = date("Ymd");

        $limit = 100;
        $maxPieceCompare = 10;

        // Word exclusion list that will not be searched upon or scored upon
        $words = "about,above,across,after,against,around,at,before,behind,below,beneath,beside,besides,between,beyond,".
                 "by,down,during,except,for,from,in,inside,into,like,near,off,out,outside,over,since,through,throughout,".
                 "till,toward,under,until,up,upon,with,without,according,to,because,addition,front,place,regard,".
                 "spite,instead,on,account,the,and,aboard,along,amid,among,as,behind,but,concerning,considering,despite,".
                 "excepting,excluding,following,minus,of,on,onto,opposite,past,per,plus,regarding,round,save,than,then,".
                 "towards,underneath,unlike,versus,via,within,oz,net,wt,inc,rd,city,no,is,it,fl,ml,us,size,lb,or,all,".
                 "count,ct,pk,all,feel,about,wegmans,food,you,feel,good,about,pack,world,finest,product,combination";
        
        $exclusionWords = array_map('strtolower', explode(",", $words));

        try{
            $db = getConnection();

            // Initialize post request capture fields
            $productSource = "";
            $productId = "";
            $productName = "";
            $productUpc = "";

            //get the request body
            $request = \Slim\Slim::getInstance()->request();
            $body = json_decode($request->getBody());

            // Initialize payload
            $payload = array();
            $payload["purchase"] = $body;

            // Fail if product source is not found
            if (!property_exists($body, 'source')) {
                $response->set("missing_product_source","Product source is a required parameter", array());
                return;
            } else {
                $productSource = $body->source;
            }

            //retrieve request body attributes
            if ($productSource === "iamdata") {
                if (property_exists($body, 'name')) {
                    $productName = $body->name;
                }
                if (property_exists($body, 'upc')) {
                    $productUpc = $body->upc;
                }
                if (property_exists($body, 'product')) {
                    if (property_exists($body->product, 'id')) {
                        $productId = $body->product->id;
                    }
                }
            } else {
                $response->set("source_not_supported","No support exists for the product source provided", array());
                return;
            }

            // Replace all hyphens with a space in the product name and convert to lower case
            $productName = str_replace('-', ' ', strtolower($productName));

            // Build array of search terms for product name, filter out common words and special characters
            $productNamePieces = static::productNameParser($productName, $exclusionWords);  

            // Remove all hyphens in the product upc and convert to lower case
            $productUpc = str_replace('-', '', strtolower($productUpc));

            // Build array of search terms for product name, filter out common words and special characters
            $productUpcPieces = static::productUpcParser($productUpc, $exclusionWords);

            //get openFDA api key
            $sql = "SELECT api_key FROM api_key WHERE service_name='OPEN_FDA'";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $apiData = $stmt->fetchObject();

            if ($apiData == null) {
                $response->set("service_failure","openFDA api keys are not configured", array());
                return;
            }

            $searchParams="(";

            $first=true;

            // Build Searches for product name
            foreach ($productNamePieces as &$value) {
                if (!$first) {
                    $searchParams .= "+";
                } else {
                    $first=false;
                }
                if ($value !== NULL && $value !== "") {
                    $searchParams .= "product_description:" .$value;
                }
            }

            // Build Searches for product upc
            foreach ($productUpcPieces as &$value) {
                if (!$first) {
                    $searchParams .= "+";
                } else {
                    $first=false;
                }
                if ($value !== NULL && $value !== "") {
                    $searchParams .= "product_description:" .$value. "+code_info:" .$value;
                }
            }

            // Add search dates
            $searchParams .= ")+AND+report_date:[" .$start. "+TO+" .$end. "]";

            // Build the URL
            $url = "https://api.fda.gov/".$type."/enforcement.json?search=" .$searchParams. "&limit=100&api_key=" .$apiData->api_key;

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
                $response->set($bigArr['error']['code'], $bigArr['error']['message'], array() );
                return;
            }

            // Exit with an error if the service did not contain a results array
            if (!array_key_exists('results',$bigArr)) {
                $response->set("results_failure","The api did not contain any results", array());
                return;
            }

            $foundAMatch = false;

            // Iterate thru each search result
            foreach ($bigArr['results'] as $idx => &$idxVal) {
                // Build array of terms for product name, filter out common words and special characters
                if ( !array_key_exists('product_description', $idxVal) ) {
                    $resultProductNamePieces = array();
                } else {
                    $resultProductName =  str_replace('-', ' ', strtolower($idxVal['product_description']));
                    $resultProductNamePieces = static::productNameParser($resultProductName, $exclusionWords);
                }
           
                // Find matching terms for product upc
                $matchingProductNamePieces = array_intersect ($resultProductNamePieces, $productNamePieces);

                // Build array of terms for product ups, filter out common words and special characters
                if (!array_key_exists('code_info', $idxVal)) {
                    $resultProductUpcPieces = array();
                } else {
                    $resultProductUpc =  str_replace('-', ' ', strtolower($idxVal['code_info']));
                    $resultProductUpcPieces = static::productUpcParser($resultProductUpc, $exclusionWords);               
                }

                // Find matching terms          
                $matchingProductUpcPieces = array_intersect ($resultProductUpcPieces, $productUpcPieces);

                // Calculating matching score
                $nameWeight = count($matchingProductNamePieces)*.5;
                $upcWeight = 1000;
                
                // Initialize Matching Score
                $matchingScore = 0;
                
                // Calculate the Product Name matching Score
                if (count($productNamePieces) > 0) {
                    $matchingScore += ( count($matchingProductNamePieces) / count($productNamePieces) *  $nameWeight);
                }

                // Calculate the Product Upc matching Score
                if (count($productUpcPieces) > 0) {
                    $matchingScore += ( count($matchingProductUpcPieces) / count($productUpcPieces) * $upcWeight );;
                }

                // Initialize Quality Score
                $qualityScore = 0;
                
                // Determine size of quality match for product names
                if ( count($resultProductNamePieces) > $maxPieceCompare) {
                    $cntResultProductNamePieces = $maxPieceCompare; 
                } else {
                    $cntResultProductNamePieces = count($resultProductNamePieces);
                }
                
                // Calculate Quality Score if product name pieces were found
                if ($cntResultProductNamePieces > 0 ) {
                    // Generate associative array to find product names
                    $flippedResultProductNamePieces = array_flip($resultProductNamePieces);                
                    
                    foreach ($productNamePieces as $idx1 => &$value) {
                        if ( array_key_exists($value, $flippedResultProductNamePieces) && $idx1 <= $maxPieceCompare && $flippedResultProductNamePieces[$value] <= $maxPieceCompare ) {
                            $qualityScore += ( $cntResultProductNamePieces - $flippedResultProductNamePieces[$value] ) * (1 / $cntResultProductNamePieces);
                        }
                    }
                }
                
                // Determine size of quality match on Product UPCs
                if ( count($resultProductUpcPieces) > $maxPieceCompare) {
                    $cntResultProductUpcPieces = $maxPieceCompare;
                } else {
                    $cntResultProductUpcPieces = count($resultProductUpcPieces);
                }   
                
                // Calculate Quality Score if product upc pieces were found
                if ($cntResultProductUpcPieces > 0 ) {
                    // Generate associative array to find product upc codes
                    $flippedResultProductUpcPieces = array_flip($resultProductUpcPieces);
                    
                    // Iterate thru all product upc pieces and calculate score based upon position of match
                    foreach ($productUpcPieces as $idx1 => &$value) {             
                        if ( array_key_exists($value, $flippedResultProductUpcPieces) && $idx1 <= $maxPieceCompare && $flippedResultProductUpcPieces[$value] <= $maxPieceCompare ) {
                            $qualityScore += ( $cntResultProductUpcPieces - $flippedResultProductUpcPieces[$value] ) * (1 / $cntResultProductUpcPieces);
                        }
                    }
                }
            
                // Remove array entry if minimum matching score has not been met
                if ($matchingScore >= $minMatchingScore && $qualityScore >= $minQualityScore) {
                    // Adding matching score to output and flag result as a match
                    $bigArr['results'][$idx]['matching_score']=round($matchingScore,2);
                    $foundAMatch = true;      
                } else {              
                    unset($bigArr['results'][$idx]);       
                }
            }

            // Retrieve product information
            $productAmazonLink = null;
            $productManufacturer = null;
            $productLargeImage = null;
            $productSmallImage = null;
            $productDescription = null;
            $productBrand = null;
            $productCategory = null;

            if ($productId !== "" && $foundAMatch) {
                try {
                    $productQuery = productsGetProductLocalAPI($productId);

                    if ($productQuery->code === "success") {
                        $productAmazonLink = $productQuery->payload['result']['amazon_link'];
                        $productManufacturer = $productQuery->payload['result']['manufacturer'];
                        $productLargeImage = $productQuery->payload['result']['large_image'];
                        $productSmallImage = $productQuery->payload['result']['small_image'];
                        $productDescription = $productQuery->payload['result']['description'];
                        $productBrand = $productQuery->payload['result']['brand'];
                        $productCategory = $productQuery->payload['result']['category'];
                    }
                } catch(Exception $e) {
                    // Nothing
                }
            }

            // Add results to the payload
            $payload["results"] = $bigArr['results'];

            // Inject product attributes into purchase element
            $payload["purchase"]->amazon_link=$productAmazonLink;
            $payload["purchase"]->manufacturer=$productManufacturer;
            $payload["purchase"]->large_image=$productLargeImage;
            $payload["purchase"]->small_image=$productSmallImage;
            $payload["purchase"]->description=$productDescription;
            $payload["purchase"]->brand=$productBrand;
            $payload["purchase"]->category=$productCategory;

            $response->set("success","Data successfully fetched from service", $payload );        
        } catch(Exception $e) {
            $response->set("system_failure", $e->getMessage(), array());
        } finally {
            $db = null;
            $response->toJSON();
        }
    }

}
?>