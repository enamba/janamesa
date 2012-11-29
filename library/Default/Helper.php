<?php

class Default_Helper {

    /**
     * get location based on given ip
     * @param string $ip
     * @return string
     */
    public static function locateIp($ip = null) {
        if (is_null($ip)) {
            return '';
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://ipinfodb.com/ip_query.php?ip=' . $ip . "&output=xml");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $ipdata = simplexml_load_string(curl_exec($ch));
        if (!empty($ipdata->City)) {
            return $ipdata->City;
        } else {
            return '';
        }
    }

    public static function companyUsedCode(Yourdelivery_Model_Company $company, Yourdelivery_Model_Rabatt_Code $code) {
        $db = Zend_Registry::get('dbAdapter');
        $sql = sprintf("select * from orders o inner join order_company_group oc on o.id=oc.orderId where o.rabattCodeId=%d and oc.companyId=%d", $code->getId(), $company->getId());
        $result = $db->fetchAll($sql);
        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Generates a random String with given length
     * @author alex
     * @param integer $length
     * @return string
     */
    public static function generateRandomString($length = 8, $chars = null) {
        if (is_null($chars)) {
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ12346790";
        }

        $chars = str_shuffle($chars);

        $genString = null;
        mt_srand((double) microtime() * 1000000);
        for ($i = 0; $i < $length; $i++) {
            $genString .= $chars{mt_rand(0, strlen($chars) - 1)};
        }

        $genString = trim($genString);

        return $genString;
    }

    /**
     * Create table grid, additionally applies defaults as passed within requests
     * @see http://ticket/browse/YD-2539
     *
     * @author Matthias Laug <laug@lieferando.de>, Marek Hejduk <m.hejduk@pyszne.pl>
     *
     * @param int $id
     * @param array $defaults
     * @param Zend_Controller_Request_Abstract
     * @return Bvb_Grid_data
     */
    public static function getTableGrid($id = 'grid', $defaults = array(), $request = null) {
        // A wrapper for non-empty defaults
        if (!empty($defaults) && $request instanceof Zend_Controller_Request_Abstract) {
            // Defaults must be applied BEFORE grid creation, otherwise they will be ignored
            foreach ($defaults as $key => $value) {
                $gridKey = $key . $id;
                if (is_null($request->getParam($gridKey))) {
                    $request->setParam($gridKey, $value);
                }
            }
        }

        $db = Zend_Registry::get('dbAdapter');
        $config = Zend_Registry::get('configuration');
        $grid = Bvb_Grid::factory('Table', $config, $id);
        $grid->setPagination(10);
        $grid->setExport(array('csv'));
        $grid->setOptions(array(
            'template' => array(
                'table' => array(
                    'cssClass' => array(
                        'table' => 'user-tab yd-grid-input'
                    )
                )
            ),
            'deploy' => array(
                'table' => array(
                    'imagesUrl' => '/media/images/yd-backend/grid/'
                )
            )
        ));
        $grid->setView(new Zend_View());
        return $grid;
    }

    /**
     * return salted sha1 encrypted string
     * @author mlaug
     * @param string $string
     * @return string
     */
    public static function encrypt($string) {
        return sha1($string . 'fdsljkjgdqtu32t87fgh');
    }

    /**
     * @author mlaug
     * @todo: implement
     * @param string $number
     * @return boolean
     */
    public static function fax_validate($number) {
        if(empty($number)) {
            return false;
        }
        
        return true;
    }

    /**
     * alter file to have relative path information
     * @author mlaug
     * @param string $file
     * @return string
     */
    public static function makeRelative($file) {

        $rel = null;

        if (strstr($file, 'media')) {
            $parts = explode('media', $file);
            $rel = '/media' . $parts[1];
        }

        if (strstr($file, 'storage')) {
            $parts = explode('storage', $file);
            $rel = '/storage' . $parts[1];
        }

        return $rel;
    }

    /**
     * create random count based on current time,
     * so this counter always returns a higher number than before
     * @author mlaug
     * @return int
     */
    public static function randomCounter() {
        $number = intval(date("H", time())) * 60 * 60 + intval(date("i", time())) * 60 + intval(date('s', time()));
        $random = $number % 100000;
        return intval($random);
    }

    /**
     * Validate email based on regular expression
     * @author mlaug
     * @param string  $email
     * @param boolean $restrictBlacklisted
     * @return boolean
     */
    public static function email_validate($email, $restrictBlacklisted = false) {
        $form = new Default_Forms_Base();
        $form->initEmail(true, true, false, $restrictBlacklisted);
        
        return $form->isValid(array('email' => $email));
    }

}
