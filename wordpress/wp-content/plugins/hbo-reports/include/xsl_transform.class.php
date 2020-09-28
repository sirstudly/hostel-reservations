<?php

/**
 * Booking Resources Management controller.
 */
abstract class XslTransform {

    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    abstract function getXslFilename();
    
    /**
     * Returns the XML used during XSL transform.
     */
    abstract function toXml();

    /**
     * Transforms this class to HTML using the given stylesheet.
     */
    function toHtml() {
        // create a DOM document and load the XSL stylesheet
        $xsl = new DomDocument;
        $xsl->load($this->getXslFilename());
        
        // import the XSL styelsheet into the XSLT process
        $xp = new XsltProcessor();
        $xp->importStylesheet($xsl);
        
        // create a DOM document and load the XML datat
        $xml_doc = new DomDocument;
        $xml_doc->loadXML($this->toXml());
        
        // transform the XML into HTML using the XSL file
        if ($html = $xp->transformToXML($xml_doc)) {
            return $html;
        } else {
            //trigger_error('XSL transformation failed.', E_USER_ERROR);
            error_log( "XSL transformation failed: " . $this->toXml() );
            return 'XSL transformation failed.';
        } // if 
        return 'XSL transformation failed.';
    }

    /**
     * Make a Cloudbeds (POST) API call to the given URL.
     *
     * @param String $url target
     * @param Array $data HTTP request parameters
     * @throws RuntimeException
     */
    function doCloudbedsPOST($url, $data) {
        try {
            $start_ms = time();
            $make_call = $this->callAPI('POST', $url, $this->getHeaders(), $data);
            error_log("Cloudbeds request took " . (time() - $start_ms) . "ms.");
            $response = json_decode($make_call, true);
        }
        catch (Exception $ex) {
            error_log($ex->getMessage());
            throw new RuntimeException('Error accessing Cloudbeds. Please try again later.');
        }

        if( $response['success'] != 'true' ) {
            error_log('Unexpected error calling ' . $url);
            error_log('request: ' . json_encode($data));
            error_log('response: ' . $make_call);
            if (FALSE == empty($response['message']) && strpos($response['message'], 'you are not using the latest version') !== FALSE) {
                throw new RuntimeException("Cloudbeds version sync error. Please try again later.");
            }
            else if (FALSE == empty($response['message'])) {
                throw new RuntimeException($response['message']);
            }
            throw new RuntimeException('Error attempting operation.');
        }
        return $response;
    }

    function callAPI($method, $url, $headers, $data = NULL) {
        error_log('callAPI');
        error_log("method: $method");
        error_log("url: $url");
        error_log("headers: " . json_encode($headers));
        error_log("data: " . var_export($data, TRUE));
        $curl = curl_init($url);

        switch ($method){
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_ENCODING, "");

        // EXECUTE:
        $result = curl_exec($curl);
        if (curl_error($curl)) {
            $error_msg = "Connection Failure: " . curl_error($curl);
        }

        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        error_log('callAPI HTTP status ' . $http_status);
        curl_close($curl);

        if (isset($error_msg)) {
            throw new RuntimeException($error_msg);
        }
        return $result;
    }

    private function getHeaders()
    {
        return array(
            "Accept: application/json, text/javascript, */*; q=0.01",
            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
            "Referer: https://hotels.cloudbeds.com/connect/" . get_option('hbo_cloudbeds_property_id'),
            "Accept-Language: en-GB,en-US;q=0.9,en;q=0.8",
            "Accept-Encoding: gzip, deflate, br",
            "X-Requested-With: XMLHttpRequest",
            "X-Used-Method: common.ajax",
            "Cache-Control: max-age=0",
            "Origin: https://hotels.cloudbeds.com",
            "User-Agent: " . get_option('hbo_cloudbeds_useragent'),
            "Cookie: " . get_option('hbo_cloudbeds_cookies'),
        );
    }

}

?>