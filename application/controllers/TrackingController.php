<?php

/**
 * Description of TrackingController
 *
 * @author mlaug
 */
class TrackingController extends Default_Controller_Base {

    /**
     * inviteAction called with i/<customerId who has invited new Customer>
     * ( example yourdelivery.de/i/1231 )
     * this will set the inviteCustomerId in the session and redirect to index
     *
     * @author Felix Haferkorn <haferkorn@lieferando.de> haferkorn
     */
    public function iAction() {
        $i = $this->getRequest()->getParam('customerId', null);
        if (!is_null($i)) {
            $this->session->inviteCustomerId = $i;
            $this->_redirect('/');
            return;
        }
    }

    private function getKeywords() {
        $_refer = parse_url($_SERVER['HTTP_REFERER']);
        $host = $_refer['host'];
        $refer = $_refer['query'];
        if (empty($refer)) {
            $refer = $_refer['fragment'];
        }

        if (strstr($host, 'google')) {
            //do google stuff
            $match = preg_match('/&q=([a-zA-Z0-9+-]+)/', $refer, $output);
            $querystring = $output[0];
            $querystring = str_replace('&q=', '', $querystring);
            $keywords = str_replace('+', ',', $querystring);
            return $keywords;
        } elseif (strstr($host, 'yahoo')) {
            //do yahoo stuff
            $match = preg_match('/p=([a-zA-Z0-9+-]+)/', $refer, $output);
            $querystring = $output[0];
            $querystring = str_replace('p=', '', $querystring);
            $keywords = str_replace('+', ',', $querystring);
            return $keywords;
        } elseif (strstr($host, 'msn')) {
            //do msn stuff
            $match = preg_match('/q=([a-zA-Z0-9+-]+)/', $refer, $output);
            $querystring = $output[0];
            $querystring = str_replace('q=', '', $querystring);
            $keywords = str_replace('+', ',', $querystring);
            return $keywords;
        } else {
            //else, who cares
            return "UNKNOWN";
        }
    }

    
    /**
     * tracking emails by called images (pixeltracking)
     * in email there is a image (1x1px) that calls this action
     * the url for tracking is <HOST>/t/<base_64_encode(json)>
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 29.11.2010
     */
    public function trackcalledimagesAction() {
        
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $json = $this->getRequest()->getParam('code', null);
        if (is_null($json)) {
            $this->_redirect('/');
        }
        
        // get recieved values and add referer
        $recievedValues = json_decode(base64_decode($json));
        $referer = Default_Helpers_Web::getReferer();

        $email = str_replace(' ', '', $recievedValues->address);
        $campaign = $recievedValues->campaign;
        if( !is_null($email) && !is_null($campaign)){
        
            $row = Yourdelivery_Model_DbTable_Tracking_Pixel::findByEmailAndCampaign($email, $campaign);
            $table = new Yourdelivery_Model_DbTable_Tracking_Pixel();
            if( is_array($row) ){
                /**
                 * update entry, increase count
                 * so we can track, how often a customer read this mail
                 */
                $table->increaseCount($row['id']);

            }else{
                /**
                 * create new entry in tracking_pixel table
                 */
                $table->insert(array(
                    'time'      => date('Y-m-d H:i:s'),
                    'address'   => $email,
                    'campaign'  => $campaign
                ));
            }
        }

        // create image
        header ('Content-type: image/jpeg');
        $im = @imagecreatetruecolor(1,1);
        imagejpeg($im);
        imagedestroy($im);
    }

}

?>
