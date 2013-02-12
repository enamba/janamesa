<?php
/**
 * Google Geocoding API
 * @author vpriem
 * @since 10.08.2010
 */
class Default_Api_Google_Geocoding {

    /**
     * @var object
     */
    private $_response;
    /**
     * @var string
     */
    private $_state;

    /**
     * Ask google
     * @author vpriem
     * @since 10.08.2010
     * @param string $address
     * @param boolean $sensor
     * @return boolean
     */
    public function ask($address = null, $lat = 0, $lng = 0, $sensor = false) {

        // ask db first
        $dbTable = new Yourdelivery_Model_DbTable_Geocoding();
        $row = $dbTable->findByHash($hash = md5($address . $lat . $lng));
        if ($row) {
            $this->_response = json_decode($row->response);
        }
        else {
            $row = $dbTable->createRow(array(
                'hash' => $hash,
            ));
        }

        // ask now google
        if ($row->hasExpired()) {

            if ($address !== null) {
                $data = file_get_contents("http://maps.google.de/maps/api/geocode/json?address=" . urlencode($address) . "&sensor=" . ($sensor ? "true" : "false"));
            }
            elseif ($lat != 0 && $lng != 0) {
                $data = file_get_contents("http://maps.google.de/maps/api/geocode/json?latlng=" . urlencode($lat) . "," . urlencode($lng) . "&sensor=" . ($sensor ? "true" : "false"));
            }
            else {
                return false;
            }

            $this->_response = json_decode($data);

            // save into db
            try {
                $row->setFromArray(array(
                    'response' => $data,
                    'status' => $this->getStatus(),
                    'address' => $address === null ? $this->getAddress() : $address,
                    'lat' => $lat == 0 ? $this->getLat() : $lat,
                    'lng' => $lng == 0 ? $this->getLng() : $lng,
                    'type' => $this->getType(),
                    'updated' => date("Y-m-d H:i:s"), // it must be
                ))->save();
            }
            catch (Exception $e) {}
            $this->_state = "REQUESTED";
            
        }
        else {
            $this->_state = "CACHED";
        }
        
        if ($this->getStatus() == "OK") {
            return true;
        }
        
        return false;
    }

    /**
     * Get state
     * @author vpriem
     * @since 12.08.2010
     * @return boolean
     */
    public function isCached() {

        return $this->_state == "CACHED";
    }

    /**
     * Get state
     * @author vpriem
     * @since 12.08.2010
     * @return boolean
     */
    public function isRequested() {

        return $this->_state == "REQUESTED";
    }

    /**
     * Get response
     * @author vpriem
     * @since 10.08.2010
     * @return object
     */
    public function getResponse() {

        return $this->_response;
    }

    /**
     * Get status
     * @author vpriem
     * @since 10.08.2010
     * @return string
     */
    public function getStatus() {

        // OK, ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED, INVALID_REQUEST
        return $this->_response->status;
    }

    /**
     * Get results
     * @author vpriem
     * @since 10.08.2010
     * @return array
     */
    public function getResults() {

        return $this->_response->results;
    }

    /**
     * Get formatted address
     * @author vpriem
     * @since 10.08.2010
     * @return string
     */
    public function getAddress($i = 0) {
return print_r($this->_response,1);
        if (count($this->_response->results)) {
            return $this->_response->results[$i]->formatted_address;
        }
        return null;
    }
    
    /**
     * @author mlaug
     * @since 01.02.2011
     * @param interger $i
     * @return string
     */
    public function getPlz($i = 0){
        if (count($this->_response->results)) {
            foreach($this->_response->results[$i]->address_components as $key => $component){
                if (in_array("postal_code",$component->types) ){
                    return $component->long_name;
                }
            }
        }
        return null;
    }
    
    /**
     * @author namba
     * @since 14.01.2013
     * @param interger $i
     * @return string
     */
    public function getDistrict($i = 0){
        if (count($this->_response->results)) {
            foreach($this->_response->results[$i]->address_components as $key => $component){
                if (in_array("sublocality",$component->types) ){
                    return $component->long_name;
                }
            }
        }
        return null;
    }

    /**
     * Get lat lng
     * @author vpriem
     * @since 10.08.2010
     * @return object
     */
    public function getLatLng($i = 0) {

        if (count($this->_response->results)) {
            return $this->_response->results[$i]->geometry->location;
        }
        return null;
    }

    /**
     * Get lat
     * @author vpriem
     * @since 10.08.2010
     * @return string
     */
    public function getLat($i = 0) {

        if (count($this->_response->results)) {
            return $this->_response->results[$i]->geometry->location->lat;
        }
        return null;
    }

    /**
     * Get lng
     * @author vpriem
     * @since 10.08.2010
     * @return string
     */
    public function getLng($i = 0) {

        if (count($this->_response->results)) {
            return $this->_response->results[$i]->geometry->location->lng;
        }
        return null;
    }

    /**
     * Get location type
     * @author vpriem
     * @since 10.08.2010
     * @return string
     */
    public function getType($i = 0) {

        // ROOFTOP, RANGE_INTERPOLATED, GEOMETRIC_CENTER, APPROXIMATE
        if (count($this->_response->results)) {
            return $this->_response->results[$i]->geometry->location_type;
        }
        return null;
    }

    /**
     * Get distance between two coordinates
     * @author mlaug
     * @since 01.08.2010
     * @return int
     */
    public static function distance($lon1, $lat1, $lon2, $lat2) {

        $quad1 = pow(($lat1 - $lat2) * 111.1338401, 2);
        $quad2 = pow(cos($lat1 - $lat2) * ($lon1 - $lon2) * 110.1338401, 2);
        return sqrt($quad1 + $quad2);
    }

}
