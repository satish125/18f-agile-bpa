<?php

function openFDARecentRecalls($type, $days, $limit) {
    $response = new restResponse;
    
    $start = date("Ymd", strtotime("-".$days." days"));
    $end = date("Ymd");
    
    $key = "dkjmH4qrI5pMYoj8hN0SCR8mhESAPGg8XxBH169b";
    $url = "https://api.fda.gov/".$type."/enforcement.json?api_key=" .$key. "&search=report_date:[" .$start. "+TO+" .$end. "]&limit=".$limit; //
    
    try {
        $data = array("key1" => "value1", "key2" => "value2");
        $options = array(
                "http" => array(
                "header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
                "method"  => "GET",
                "content" => http_build_query($data),
            ),
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $bigArr = json_decode($result,true,20);
        $res = $bigArr["results"];
        $json = json_encode($res);
        $json1 = json_decode($json);
        
        $response->set("success","Data successfully fetched from service", $json1 );
    } catch(PDOException $e) {
        $response->set("system_failure","System error occurred, unable fetch data", "");
    } finally {
        $response->toJSON();
    }
    
}

?>