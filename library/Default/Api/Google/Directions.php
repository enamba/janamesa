<?php
/**
 * Google Directions API
 * @author vpriem
 * @since 10.08.2010
 */
class Default_Api_Google_Directions{

    /**
     * @var object
     */
    private $_response;

    /**
     * Ask google
     * @author vpriem
     * @since 10.08.2010
     * @param string $address
     * @param string $destination
     * @param boolean $sensor
     * @return boolean
     */
    public function ask ($origin, $destination, $sensor = false) {

        $data = file_get_contents("http://maps.google.de/maps/api/directions/json?origin=" . urlencode($origin) . "&destination=" . urlencode($destination) . "&sensor=" . ($sensor ? "true" : "false"));
        $this->_response = json_decode($data);
        if ($this->getStatus() == "OK") {
            return true;
        }
        return false;

    }

    /**
     * Get response
     * @author vpriem
     * @since 10.08.2010
     * @return object
     */
    public function getResponse(){

        return $this->_response;

    }

    /**
     * Get status
     * @author vpriem
     * @since 10.08.2010
     * @return string
     */
    public function getStatus(){

        // OK, NOT_FOUND, ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED, INVALID_REQUEST, UNKNOWN_ERROR
        return $this->_response->status;

    }

    /**
     * Get routes
     * @author vpriem
     * @since 10.08.2010
     * @return array
     */
    public function getRoutes(){

        return $this->_response->routes;

    }

    /**
     * Get start location
     * @author vpriem
     * @since 10.08.2010
     * @return string
     */
    public function getStartLocation ($i = 0) {

        return $this->_response->routes[$i]->legs[0]->start_location;

    }

    /**
     * Get end location
     * @author vpriem
     * @since 10.08.2010
     * @return string
     */
    public function getEndLocation ($i = 0) {

        return $this->_response->routes[$i]->legs[0]->end_location;

    }

    /**
     * Get duration
     * @author vpriem
     * @since 10.08.2010
     * @return object
     */
    public function getDuration ($i = 0) {

        return $this->_response->routes[$i]->legs[0]->duration;

    }

    /**
     * Get duration value
     * @author vpriem
     * @since 10.08.2010
     * @return int
     */
    public function getDurationValue ($i = 0) {

        return $this->_response->routes[$i]->legs[0]->duration->value;

    }

    /**
     * Get duration text
     * @author vpriem
     * @since 10.08.2010
     * @return string
     */
    public function getDurationText ($i = 0) {

        return $this->_response->routes[$i]->legs[0]->duration->text;

    }

    /**
     * Get distance
     * @author vpriem
     * @since 10.08.2010
     * @return object
     */
    public function getDistance ($i = 0) {

        return $this->_response->routes[$i]->legs[0]->distance;

    }

    /**
     * Get distance value
     * @author vpriem
     * @since 10.08.2010
     * @return int
     */
    public function getDistanceValue ($i = 0) {

        return $this->_response->routes[$i]->legs[0]->distance->value;

    }

    /**
     * Get distance text
     * @author vpriem
     * @since 10.08.2010
     * @return string
     */
    public function getDistanceText ($i = 0) {

        return $this->_response->routes[$i]->legs[0]->distance->text;

    }

}
