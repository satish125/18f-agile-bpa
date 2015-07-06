<?php

class RestService {

    // Status Codes
    const SUCCESS_CODE = "success";
    const SERVICE_FAILURE_CODE = "service_failure";
    const SYSTEM_FAILURE_CODE = "system_failure";
    const NO_DATA_FOUND_CODE = "no_data_found";

    // Messages
    const SUCCESS_MESSAGE = "Data successfully fetched from service";
    const SERVICE_FAILURE_MSG = "Service failed to return data";

    // Rest Responses
    public $code = "";
    public $msg = "";
    public $payload = "";

    // Request Options for HTTP GET
    protected function getRequestOptions() {
        return array(
            "http" => array(
                "header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
                "method"  => "GET"
            ),
        );
    }

    // Request Options for HTTP DELETE
    protected function deleteRequestOptions() {
        return array(
            "http" => array(
                "header"  => "Accept: application/json; Content-type: application/x-www-form-urlencoded\r\n",
                "method"  => "DELETE"
            ),
        );
    }

    // Request Options for HTTP POST/PUT
    protected function getJsonOptions($jsonData, $method="POST"){
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

    // Setter for Rest Response
    protected function setResponse($code, $msg, $payload){
        $this->code = $code;
        $this->msg = $msg;
        $this->payload = $payload;
    }

    // Getter for Rest Response
    protected function getResponse(){
        return (object) ['code' => $this->code, 'msg'=> $this->msg, 'payload'=> $this->payload];
    }

    // Output Rest Response in JSON format
    protected function outputResponse() {
        $responseObject = (object) ['code' => $this->code, 'msg'=> $this->msg, 'payload'=> $this->payload];

        echo json_encode($responseObject);
    }

    // Validate request body parameters
    protected function checkParamsExist($body, $params){
        $parameterMissing = false;

        foreach ($params as $parameterName => &$parameterErrorMsg) {
            if (!property_exists($body, $parameterName)) {
                if ($parameterErrorMsg !== null) {
                    setResponse("invalid_parameter", $parameterErrorMsg, array());
                } else {
                    setResponse("invalid_parameter", $parameterName." parameter is missing", array());
                }
                $parameterMissing = true;
            }
        }
        return !$parameterMissing;
    }
}
?>