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
     * Sends an API post request to Cloudbeds. Updates the version if it fails and re-submits it.
     * @param string $endpoint
     * @param array $data
     * @return array deserialized JSON response data
     */
    function cloudbeds_api_request($endpoint, $data) {

        error_log("Calling $endpoint");

        // set default parameters for this property
        $PROPERTY_ID = get_option('hbo_cloudbeds_property_id');
        $data['version'] = $this->get_cloudbeds_version_for_endpoint($endpoint);
        $data['property_id'] = $PROPERTY_ID;
        $data['group_id'] = $PROPERTY_ID;

        $start_ms = time();
        $make_call = $this->make_api_call('POST', $endpoint, $this->get_headers($PROPERTY_ID), $data);
        error_log("Cloudbeds request took " . (time() - $start_ms) . "ms.");
        $response = json_decode($make_call, true);

        if( $response['success'] != 'true' && isset($response['message'])) {
            if( strpos($response['message'], 'you are not using the latest version') !== false ) {
                error_log('API version has been updated; recording new version');
                $prev_version = $this->get_cloudbeds_version_for_endpoint($endpoint);
                $new_version = $response['version'];
                error_log("Updating version from $prev_version to $new_version for $endpoint");
                $this->set_cloudbeds_version_for_endpoint($endpoint, $new_version);

                // now redo the request
                $data['version'] = $new_version;
                $start_ms = time();
                $make_call = $this->make_api_call('POST', $endpoint,
                    $this->get_headers(get_option('hbo_cloudbeds_property_id')), $data);
                error_log("Cloudbeds request took " . (time() - $start_ms) . "ms.");
                $response = json_decode($make_call, true);
            }
        }
        return $response;
    }

    private function get_cloudbeds_version_option_name($endpoint) {
        return 'hbo_cloudbeds_version_' . substr($endpoint, strripos($endpoint, "/") + 1);
    }

    function get_cloudbeds_version_for_endpoint($endpoint)
    {
        $option_name = $this->get_cloudbeds_version_option_name($endpoint);
        $option_value = get_option($option_name);
        if (FALSE === empty($option_value)) {
            return $option_value;
        }
        else {
            $default_version = get_option('hbo_cloudbeds_version');
            error_log("variable $option_name not set, setting to default cloudbeds version $default_version");
            update_option($option_name, $default_version);
            return $default_version;
        }
    }

    private function set_cloudbeds_version_for_endpoint($endpoint, $version) {
        update_option($this->get_cloudbeds_version_option_name($endpoint), $version);
    }

    /**
     * Returns default headers for communicating with Cloudbeds.
     * @param $property_id string
     * @return array of headers
     */
    private function get_headers($property_id) {
        return array(
            "Accept: application/json, text/javascript, */*; q=0.01",
            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
            "Referer: https://hotels.cloudbeds.com/connect/" . $property_id,
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

    private function make_api_call($method, $url, $headers, $data = NULL) {
        error_log('make_api_call');
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