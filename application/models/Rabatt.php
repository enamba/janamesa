<?php

/**
 * @author mlaug
 */
class Yourdelivery_Model_Rabatt extends Default_Model_Base {

    const RELATIVE = 0;
    const ABSOLUTE = 1;
    const FIDELITY = 'fidelity';
    const REFERRAL = 'REFERRAL';

    /**
     * Type 0: a regular rabatt code without any verification
     */
    const TYPE_REGULAR = 0;

    /**
     * Type 1: the verification process is used to determine a new customer
     * and create discount which is sent to the customer
     */
    const TYPE_LANDING_PAGE = 1;

    /**
     * Type 2: there existing multiple verification codes, which can be used
     * once to generate a valid discount code
     */
    const TYPE_VERIFICATION_MANY = 2;

    /**
     * Type 3: there exists a single verification code, which can be used without
     * any limit until the rabatt offer expires
     */
    const TYPE_VERIFICATION_SINGLE = 3;

    /**
     * Type 4: the codes can be used without the verification process, but only
     * once per paypal and once ebanking, each per discount action. No Landingpage will be generate here.
     */
    const TYPE_VERIFCATION_ACTION = 4;

    /**
     * Type 5: the codes can be used without the verification process, but only
     * once per paypal/ebanking and discount action of type 5. No Landingpage will be generate here.
     */
    const TYPE_VERIFCATION_ONCE_PER_THIS_TYPE = 5;

    /**
     * Type 6: single code, but with New Customer Validation for specified Action
     */
    const TYPE_VERIFCATION_SINGLE_ACTION = 6;

    /**
     * Type 7: single code, but with New Customer Validation
     */
    const TYPE_VERIFCATION_SINGLE_ALL = 7;

    protected $_restaurants;
    protected $_citys;

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 19.01.2012
     * @return boolean
     */
    public function isNewCustomerDiscount() {

        $config = Zend_Registry::get('configuration');
        
        if($config->ordering->discount->newcustomercheck) {            
             return $this->getType() > 0;
        }
        return false;
       
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @return array
     */
    public static function getRabattRoutes() {

        $return = array();
        $fields = Yourdelivery_Model_DbTable_Rabatt::getDistinctReferer();
        foreach ($fields as $field) {
            // Filter out null and slashes
            $field['referer'] = str_replace('/', "", $field['referer']);
            if (!empty($field['referer'])) {
                $return[] = $field['referer'];
            }
        }
        return $return;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @param string $referer
     * @return Yourdelivery_Model_Rabatt
     */
    public static function getByReferer($referer) {
        if (is_null($referer)) {
            return null;
        }
        $result = Yourdelivery_Model_DbTable_Rabatt::findByReferer($referer);
        if ($result && $result['id'] !== NULL) {
            try {
                return new Yourdelivery_Model_Rabatt($result['id']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Retrieves a single rabatt object by hash
     * 
     * @param string $hash
     * @return null|\Yourdelivery_Model_Rabatt
     * 
     * @author André Ponert <ponert@lieferando.de>
     * @since 10.08.2012
     */
    public static function getByHash($hash) {
        $result = Yourdelivery_Model_DbTable_Rabatt::findByHash($hash);
        if ($result && $result['id'] !== NULL) {
            try {
                return new Yourdelivery_Model_Rabatt($result['id']);
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Get all restaurants with lieferando11 discount
     * @author alex
     * @since 25.11.2011
     * @return array
     */
    static function getLieferando11Restaurants() {
        return array(
            14708, 15467, 12990, 12067, 12136, 12426, 12346, 12458, 12504, 12913, 12963, 13020, 13021, 13448, 13750,
            13974, 14120, 14168, 14414, 14486, 14581, 14604, 14645, 14655, 14169, 12044, 14276, 14647, 14764,
            15026, 14763, 14764, 14761, 12598, 13115, 14380, 13269, 14566, 14808, 14842, 12700, 15236, 15245, 13442,
            14174, 14053, 15263, 12579, 13021, 12494, 14633, 14486, 14647, 14886, 14457, 12085, 15080, 15089, 15156,
            14175, 14212, 13750, 12601, 13683, 13888, 12498, 14631, 13439, 15249, 15465, 14557, 16033, 14855, 12161,
            14414, 14189, 14749, 14746, 16100, 16025, 14599, 16024, 16132, 14738, 17191, 16969, 14167, 12305, 16518,
            17201, 16933, 14814, 15197, 12337, 15160, 14413, 16513, 15389, 12340, 12924, 16216, 17750, 17187, 17571,
            11996, 16784, 12216, 12358, 15083, 15339, 17862, 13877, 17968, 18328, 16648, 18037, 14680, 17355, 15484,
            18372, 16889, 15804, 18335, 15001, 16336, 13383, 17730, 19101, 17141, 18000, 18515, 15237, 14166, 17603,
            12606, 16531, 19427, 15316, 19813, 19446, 18539, 16375, 20242, 15762, 15413
        );
    }

    /**
     * create a specific type of rabatt object
     * @author mlaug
     * @since 17.08.2011
     * @param string $type
     * @return Yourdelivery_Model_Rabatt_Code
     */
    public static function factory($type, $params) {
        switch ($type) {
            case self::FIDELITY:
                extract($params);
                $discount = new Yourdelivery_Model_Rabatt();
                $values = array();
                $values['name'] = $fullname . ' (Eingelöste Treuepunkte) ' . date('d.m.Y. H:i');
                $values['start'] = date('Y-m-d H:i:s', time());
                $values['end'] = date('Y-m-d H:i:s', strtotime('+2days'));
                $values['rrepeat'] = 0;
                $values['number'] = 1;
                $values['status'] = 1;
                $values['kind'] = 1;
                $values['info'] = '(Eingelöste Treuepunkte)';
                $values['fidelity'] = 1;
                $values['rabatt'] = $cost;
                $values['onlyPrivate'] = 1;
                $values['noCash'] = 1;

                $discountId = $discount->getId();
                $discount->setData($values);
                $discount->save();

                // create RabattCode
                $code = $discount->generateCode();
                $rabattCodeObj = new Yourdelivery_Model_Rabatt_Code($code);
                return $rabattCodeObj;
                break;
                
            case self::REFERRAL:
                extract($params);
                $discount = new Yourdelivery_Model_Rabatt();
                $values = array();
                if ($referral){
                    $values['name'] = $fullname . " ($referral) " . date('d.m.Y. H:i');
                } else {
                    $values['name'] = $fullname . ' (USER REFERRAL) ' . date('d.m.Y. H:i');
                }
                $values['start'] = date('Y-m-d H:i:s', time());
                $values['end'] = date('Y-m-d H:i:s', strtotime('+2days'));
                $values['rrepeat'] = 0;
                $values['number'] = 1;
                $values['status'] = 1;
                $values['kind'] = 0;
                $values['info'] = '(USER REFERRAL)';
                $values['fidelity'] = 1;
                $values['rabatt'] = $percent;
                $values['onlyPrivate'] = 1;
                $values['noCash'] = 1;

                $discountId = $discount->getId();
                $discount->setData($values);
                $discount->save();

                // create RabattCode
                $code = $discount->generateCode();
                $rabattCodeObj = new Yourdelivery_Model_Rabatt_Code($code);
                return $code;
                break;
        }
        return null;
    }

    /**
     * @author mlaug
     * @since 25.08.2010
     * @return array
     */
    public static function getPermanent() {
        $db = Zend_Registry::get('dbAdapter');
        $sql = "select * from rabatt where status=1 and rrepeat=1";
        $rows = $db->fetchAll($sql);
        //get them all
        $all = new SplObjectStorage();
        foreach ($rows as $row) {
            try {
                $all->attach(new Yourdelivery_Model_Rabatt($row['id']));
            } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                continue;
            }
        }
        return $all;
    }

    /**
     * get related table
     * @author mlaug
     * @return Yourdelivery_Model_DbTable_Rabatt
     */
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_Rabatt();
        }
        return $this->_table;
    }

    /**
     * Check if Discount is Active
     * @author Daniel Hahn <hahn@lieferando.de>
     * @return boolean
     */
    public function isActive() {

        if ($this->getStatus() == 0) {
            return false;
        }


        $startTime = $this->getStart();
        $endTime = $this->getEnd();

        if (time() < $startTime || (time() > $endTime && $endTime > 0)) {
            return false;
        }

        return true;
    }

    /**
     * get all used codes
     * @author mlaug
     * @return mixed
     */
    public function getUsedCodes() {
        return $this->getTable()->getUsed();
    }

    /**
     * get all codes with onlyCustomer flag set to 1
     * @author mlaug
     * @return mixed
     */
    public function getOnlyCustomerCodes() {
        return $this->getTable()->getOnlyCustomerCodes();
    }

    /**
     * Retrieves a random code for this discount
     *
     * @return string rabatt code
     * 
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 09.08.2012
     */
    public function getRandomCode() {
        $codes = $this->getCodes()->toArray();

        // We only want to return valid codes
        foreach ($codes as $code) {
            if ($code['used'] == 0 && $code['reserved'] != 1) {
                return $code['code'];
            }
        }
        return null;
    }

    /**
     * Generates a hash value for this rabatt instance
     *
     * @author Andre Ponert <ponert@lieferando.de>
     * @since 09.08.2012
     */
    public function makeHash() {
        return md5(SALT . $this->getId() . SALT);
    }

    /**
     * get all codes
     * @author mlaug
     * @return mixed
     * @modified Alex Vait - added $regcodes - get normal codes or registration codes
     */
    public function getCodes($regcodes = false) {
        return $this->getTable()->getCodes($regcodes);
    }

    /**
     * @author mlaug
     * @since 26.10.210
     * @return int
     */
    public function getStart() {
        return strtotime($this->_data['start']);
    }

    /**
     * @author mlaug
     * @since 26.10.210
     * @return int
     */
    public function getEnd() {
        return strtotime($this->_data['end']);
    }

    /**
     * @author mlaug
     * @since 26.10.210
     * @return int
     */
    public function setStart($start) {
        $this->_data['start'] = date('Y-m-d H:i:s', (integer) $start);
    }

    /**
     * @author mlaug
     * @since 26.10.210
     * @return int
     */
    public function setEnd($end) {
        $this->_data['end'] = date('Y-m-d H:i:s', (integer) $end);
    }

    /**
     * realy delete rabatt
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 09.08.2010
     */
    public function delete() {
        $this->getTable()->remove($this->getId());
        // delete depending codes
        $codesTable = new Yourdelivery_Model_DbTable_RabattCodes();
        $codesTable->removeCodes($this->getId());
    }

    /**
     * @author vpriem
     * @since 28.01.2011
     * @param string $code
     * @param int $maxlen
     * @return string|boolean
     */
    public function generateCode($code = null, $maxlen = 15, $charset = null, $verificationCode = false) {

        if (is_null($charset)) {
            $charset = "ACEFGHJKLMNPQRTUVWXYZ234679";
        }

        if ($verificationCode) {
            $dbTable = new Yourdelivery_Model_DbTable_RabattCodesVerification();
        } else {
            $dbTable = new Yourdelivery_Model_DbTable_RabattCodes();
        }

        $dbRow = $dbTable->createRow(array(
            'rabattId' => $this->getId()
                ));

        if ($code === null) {
            $deadLockPreventer = 0;
            while ($deadLockPreventer < 50) {
                $code = Default_Helper::generateRandomString($maxlen, $charset);
                $check = $dbTable->findByCode($code);
                if ($check === false) {
                    break;
                }

                $deadLockPreventer++;
            }

            if ($deadLockPreventer == 50) {
                return false;
            }
        } else {
            // code already exists
            $check = $dbTable->findByCode($code);
            if ($check !== false) {
                if ($check['rabattId'] == $this->getId()) {
                    return $code;
                }
                return false;
            }
        }

        if ($verificationCode) {
            $dbRow->registrationCode = $code;
        } else {
            $dbRow->code = $code;
        }

        $dbRow->save();
        return $code;
    }

    /**
     * create a set of codes
     * @author Alex Vait <vait@lieferando.de>
     * @since 24.01.2012
     * @param string $name
     * @return boolean
     */
    public function generateCodes($number = null, $universalCode = null) {
        if (is_null($number) && is_null($universalCode)) {
            return false;
        }

        if (($this->getType() == self::TYPE_VERIFICATION_SINGLE) && is_null($universalCode)) {
            return false;
        }

        if ($number == 0) {
            $number = 1;
        }

        switch ($this->getType()) {
            case self::TYPE_REGULAR:
            case self::TYPE_VERIFCATION_ACTION:
                // create normal discount codes
                for ($i = 0; $i < $number; $i++) {
                    $this->generateCode();
                }
                break;
            case self::TYPE_VERIFCATION_ONCE_PER_THIS_TYPE:
                // create normal discount codes
                for ($i = 0; $i < $number; $i++) {
                    $this->generateCode();
                }
                break;
            case self::TYPE_LANDING_PAGE:
                // new customer without verification
                // do nothing, discount code will be created on the fly
                break;
            case self::TYPE_VERIFICATION_MANY:
                // new customer with verification codes
                for ($i = 0; $i < $number; $i++) {
                    $this->generateCode(null, 15, null, true);
                }
                break;
            case self::TYPE_VERIFICATION_SINGLE:
                // create single fake code
                $codeVerification = new Yourdelivery_Model_Rabatt_CodesVerification();
                $codeVerification->setData(
                        array(
                            'rabattId' => $this->getId(),
                            'registrationCode' => $universalCode
                ));
                $codeVerification->save();
                break;
            //single code with New Customer Discount Verification
            case self::TYPE_VERIFCATION_SINGLE_ACTION:
            case self::TYPE_VERIFCATION_SINGLE_ALL:
                //generate Code, if exists with different Rabatt then return false
                if ($this->generateCode($universalCode) == false) {
                    return false;
                };
                break;
            default:
                break;
        }

        return true;
    }

    /**
     * Get all discount types
     *
     * @author Alex Vait <vait@lieferando.de>
     * @since 18.01.2012
     * @return array
     */
    public static function getDiscountTypes() {
        $types = array(
            0 => array('name' => __b('Typ 0 '), 'description' => __b('Neukunden & Bestandskunden, einfache Rabattaktion mit Code, keine Verifikation, keine Landingpage ')),
            1 => array('name' => __b('Typ 1'), 'description' => __b('Neukunden, ohne Code, mit Verifikation, mit Landingpage')),
            2 => array('name' => __b('Typ 2'), 'description' => __b('Neukunden, mit Code, mit Verifikation, mit Landingpage ')),
            3 => array('name' => __b('Typ 3'), 'description' => __b('Neukunden, mit universellem Code, mit Verifikation, mit Landingpage')),
            4 => array('name' => __b('Typ 4'), 'description' => __b('Neu- und Bestandskunden, einmaliger Code, ohne Verifikation, ohne Landingpage, nur 1 Gutschein pro Kunde pro Aktion')),
            5 => array('name' => __b('Typ 5'), 'description' => __b('Neukunden, eindeutiger Code, ohne Verifizierung, ohne Landingpage, nur 1 Gutschein pro Neukunde')),
            6 => array('name' => __b('Typ 6'), 'description' => __b('Neu- und Bestandskunden, universeller Code, ohne Verifikation, ohne Landingpage, nur 1 Gutschein pro Kunde pro Aktion')),
            7 => array('name' => __b('Typ 7'), 'description' => __b('Neukunden, universeller Code, ohne Verifikation, ohne Landingpage')),
        );

        return $types;
    }

    /**
     * get storage object of this discount
     * @author Alex Vait <vait@lieferando.de>
     * @since 19.01.2011
     * @return Default_File_Storage
     */
    public function getStorage($path = null) {

        if (is_null($this->_storage)) {
            $this->_storage = new Default_File_Storage();
        }

        $this->_storage->resetSubFolder();
        $this->_storage->setSubFolder('discounts/' . $this->getId());

        return $this->_storage;
    }

    /**
     * Sets a new image for this discount for landing page
     * @author Alex Vait <vait@lieferando.de>
     * @since 19.01.2012
     * @param string $name
     * @return boolean
     */
    public function setImg($name) {
        if (is_null($this->getId())) {
            return false;
        }

        $data = file_get_contents($name);
        // if file_get_contents failed $data is 'false'
        if ($data !== false) {
            $this->getStorage()->store('default.jpg', $data);

            //save image additionally in amazon s3
            $config = Zend_Registry::get('configuration');
            Default_Helpers_AmazonS3::putObject($config->domain->base, "discounts/" . $this->getId() . "/default.jpg", $name);

            if ($this->config->varnish->enabled) {
                $varnishPurger = new Yourdelivery_Api_Varnish_Purger();
                $varnishPurger->addUrl($this->getImg());
                $varnishPurger->executePurge();
            }
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 20.06.2012
     *
     * @return string
     */
    public function getImg() {
        $width = $this->config->timthumb->discount->normal->width;
        $height = $this->config->timthumb->discount->normal->height;
        $http = isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] == 443 ? 'https://' : 'http://';
        $url = sprintf('%s/%s/discounts/%s/default-%d-%d.jpg', $http . $this->config->domain->timthumb, $this->config->domain->base, $this->getId(), $width, $height);
        return $url;
    }

    /**
     * @author dhahn
     * @param type $rabattId
     * @modified afrank - 14-02-2012
     * @return string
     */
    public function getZipFile($rabattId = null) {

        $_numEntries = 500000;


        if (is_null($rabattId) || $rabattId <= 0) {
            $rabattId = $this->getId();
        }

        switch ($this->getType()) {
            case self::TYPE_REGULAR:
                $regcodes = false;
                $codeType = 'code';
                break;
            case self::TYPE_LANDING_PAGE :
                break;
            case self::TYPE_VERIFICATION_MANY :
                $regcodes = true;
                $codeType = 'registrationCode';
                break;
            case self::TYPE_VERIFICATION_SINGLE :
                break;
            default :
                $regcodes = false;
                $codeType = 'code';
        }

        $rabattModel = new Yourdelivery_Model_Rabatt($rabattId);
        $codes = $rabattModel->getCodes($regcodes);
        $codes_split = array_chunk($codes->toArray(), $_numEntries);

        $codeDir = "/tmp/rabattCodes-" . $rabattId;

        if (!is_dir($codeDir)) {
            mkdir($codeDir);
        }

        foreach ($codes_split as $key => $codeFile) {
            $filename = $codeDir . "/codes-" . $rabattId . "-" . $key . ".csv";
            $file = fopen($filename, "w");

            if ($file) {
                fputcsv($file, array('Code'));
                foreach ($codeFile as $list) {
                    fputcsv($file, array($list[$codeType]));
                }
                fclose($file);
            } else {
                throw new Yourdelivery_Exception_FileWrite("File " . $filename . " could not be created.@cronjob/download of discount-code.");
            }
        }

        $outfile = $codeDir . "/rabatt_codes-" . $rabattId . ".zip";

        system("/usr/bin/zip -j " . $outfile . ' ' . $codeDir . "/*.csv", $returnVal);

        if ($returnVal != 0) {
            throw new Yourdelivery_Exception_FileWrite("Zip File : " . $outfile . " could not be created.@cronjob/download of discount-code.");
        }

        return $outfile;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 07.05.2012
     * @param int $oldStatus
     * @param int $newStatus
     * @param Yourdelivery_Model_Order_Abstract $order
     */
    public function handleStorno($oldStatus, $newStatus, Yourdelivery_Model_Order_Abstract $order) {

        if (in_array($newStatus, array(-2, -6))) {

            $db = Zend_Registry::get('dbAdapter');
            $select = $db->select()->from('orders')->where('id != ?', $order->getId())->where('rabattCodeId = ?', $order->getDiscount()->getId());
            $result = $db->fetchAll($select);

            if (count($result) == 0) {
                $order->getDiscount()->setCodeUnused($order);
            }
        }
        if (in_array($oldStatus, array(-2, -6)) && $newStatus >= 0) {
            $order->getDiscount()->setCodeUsed();
        }
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 07.05.2012
     * @param int $oldStatus
     * @param int $newStatus
     * @param Yourdelivery_Model_Order_Abstract $order
     */
    public function handleAffiliprint($oldStatus, $newStatus, Yourdelivery_Model_Order_Abstract $order) {
        if (in_array($oldStatus, array(-5, -6)) && $newStatus >= 0) {
            // Affiliprint validation
            if (IS_PRODUCTION && $this->getId() == 31546) {
                require_once(APPLICATION_PATH . '/../library/Extern/Affiliprint/AffiliPrintConfig.php');
                require_once(APPLICATION_PATH . '/../library/Extern/Affiliprint/AffiliPrintCom.php');
                $talkToAffiliprint = new AffiliPrintCom();

                if (!$order->getDiscount()) {
                    $this->warn('Affiliprint and no Discount, something went wrong...');
                    return;
                }

                if ($talkToAffiliprint->remoteRedeemBonuscode($order->getDiscount()->getCode(), intToPrice($order->getAbsTotal()), false, "Bestellung Nr. " . $order->getNr())) {
                    $this->logger->debug('Succesfully validated Code: ' . $order->getDiscount()->getCode());
                } else {
                    $this->warn('Could not redeem Code: ' . $order->getDiscount()->getCode());
                }
            }
        }
    }

    /**
     * check if this discount code needs any landingpage
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 07.06.2012
     * @return type
     */
    public function needsLandingpage() {
        $landing = array(1, 2, 3);
        return in_array($this->getType(), $landing);
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 05.06.2012
     * @param array $restaurantIds
     * @return boolean
     */
    public function setRestaurants($restaurantIds) {

        $currentIds = array();

        $restaurants = $this->getRestaurants();
        //delete
        foreach ($restaurants as $restaurant) {
            if (!in_array($restaurant->getId(), $restaurantIds)) {
                Yourdelivery_Model_Rabatt_Restaurant::deleteByRabattAndRestaurantId($this->getId(), $restaurant->getId());
            } else {
                $currentIds[] = $restaurant->getId();
            }
        }

        //add
        foreach ($restaurantIds as $restaurantId) {
            if (!in_array($restaurantId, $currentIds)) {
                $rr = new Yourdelivery_Model_Rabatt_Restaurant();
                $rr->setRabattId($this->getId());
                $rr->setRestaurantId($restaurantId);
                $rr->save();
            }
        }

        $this->_restaurants = null;

        return true;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 05.06.2012
     * @return array
     */
    public function getRestaurants() {

        if (is_null($this->_restaurants)) {
            $tmp = array();
            $relations = $this->getTable()->getCurrent()->findDependentRowset("Yourdelivery_Model_DbTable_Rabatt_Restaurant");

            foreach ($relations as $relation) {
                $tmp[] = new Yourdelivery_Model_Servicetype_Restaurant($relation->restaurantId);
            }

            if (count($tmp) > 0) {
                $this->_restaurants = $tmp;
            }
        }
        return $this->_restaurants;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 05.06.2012
     * @param int $restaurantId
     * @return null|boolean
     */
    public function isDiscountRestaurant($restaurantId) {

        if (is_null($this->getRestaurants())) {
            return null;
        }

        foreach ($this->getRestaurants() as $restaurant) {
            if ($restaurant->getId() == $restaurantId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 06.06.2012
     * @param type $cityIds
     * @return boolean
     */
    public function setCitys($cityIds) {

        $currentIds = array();

        $citys = $this->getCitys();
        //delete

        if ($citys) {
            foreach ($citys as $city) {
                if (!in_array($city->getId(), $cityIds)) {
                    Yourdelivery_Model_Rabatt_City::deleteByRabattAndCityd($this->getId(), $city->getId());
                } else {
                    $currentIds[] = $city->getId();
                }
            }
        }


        //add
        if ($cityIds) {
            foreach ($cityIds as $cityId) {
                if (!in_array($cityId, $currentIds)) {
                    $rr = new Yourdelivery_Model_Rabatt_City();
                    $rr->setRabattId($this->getId());
                    $rr->setCityId($cityId);
                    $rr->save();
                }
            }
        }

        $this->_citys = null;

        return true;
    }

    /**
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 06.06.2012
     * @return type
     */
    public function getCitys() {
        if (is_null($this->_citys)) {
            $tmp = array();
            $relations = $this->getTable()->getCurrent()->findDependentRowset("Yourdelivery_Model_DbTable_Rabatt_City");

            foreach ($relations as $relation) {
                $tmp[] = new Yourdelivery_Model_City($relation->cityId);
            }

            if (count($tmp) > 0) {
                $this->_citys = $tmp;
            }
        }
        return $this->_citys;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 06.06.2012
     * @param type $cityId
     * @return null|boolean
     */
    public function isDiscountCity($cityId) {
        if (is_null($this->getCitys())) {
            return null;
        }

        foreach ($this->getCitys() as $city) {
            if ($city->getId() == $cityId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 16.08.2012
     * @param string $uuid
     * @return boolean
     */
    public function hasAlreadyBeenUsedForThatUuid($uuid) {

        if (!strlen($uuid)) {
            return false;
        }

        $db = $this->getTable()->getAdapterReadOnly();
        
        $select = $db->select()
                ->from(array('o' => 'orders'), array())
                ->join(array('rc' => 'rabatt_codes'), "o.rabattCodeId = rc.id", array('rabattId'))
                ->where('o.uuid = ?', $uuid)
                ->where('o.state > 0')
                ->where('rc.rabattId = ?', $this->getId())
                ->limit(1);
        
        return $db->fetchOne($select) ? true : false;
    }

}

