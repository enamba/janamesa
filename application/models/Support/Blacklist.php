<?php

/**
 * Description of Blacklist
 *
 * @author mlaug
 */
class Yourdelivery_Model_Support_Blacklist extends Default_Model_Base {

    /**
     * @var Yourdelivery_Model_Support_Blacklist_Values[]
     */
    protected $_values = array();

    /**
     * @var array
     */
    protected static $_list = array();

    /**
     * Types
     * @var string
     */

    const TYPE_PAYPAL_EMAIL = 'paypal_email';
    const TYPE_PAYPAL_PAYERID = 'payerid';
    const TYPE_EMAIL = 'email';
    const TYPE_EMAIL_MINUTEMAILER = 'minutemailer';
    const TYPE_KEYWORD_IP = 'ip';
    const TYPE_KEYWORD_IP_NEWCUSTOMER_DISCOUNT = 'ip_newcustomer_discount';
    const TYPE_KEYWORD_UUID = 'uuid';
    const TYPE_KEYWORD_TEL = 'tel';
    const TYPE_KEYWORD_ADDRESS = 'address';
    const TYPE_KEYWORD_CUSTOMER = 'customer';
    const TYPE_KEYWORD_COMPANY = 'company';

    /**
     * Matchings
     * @var string
     */
    const MATCHING_EXACT = 'exact';
    const MATCHING_PARTIAL = 'partial';

    /**
     * Behaviours
     * @var string
     */
    const BEHAVIOUR_FAKE = 'fake';
    const BEHAVIOUR_FAKE_STORNO = 'fake_storno';
    const BEHAVIOUR_BLACKLIST = 'blacklist';
    const BEHAVIOUR_WHITELIST = 'whitelist';

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 14.06.2012
     * @param int $id
     * @param null|Default_Model_Base $current
     */
    public function __construct($id = null, $current = null) {

        parent::__construct($id, $current);

        if ($id !== null) {
            $this->_loadValues();
        }
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.06.2012
     * @param string  $prefix
     * @return array
     */
    public static function getTypes($prefix = null) {

        $paypalTypes = array(
            self::TYPE_PAYPAL_EMAIL => __b("Paypal Email"),
            self::TYPE_PAYPAL_PAYERID => __b("Paypal PayerId"),
        );

        $emailTypes = array(
            self::TYPE_EMAIL => __b("eMail"),
            self::TYPE_EMAIL_MINUTEMAILER => __b("Minutemailer"),
        );

        $keywordTypes = array(
            self::TYPE_KEYWORD_IP => __b("Ip Adresse"),
            self::TYPE_KEYWORD_IP_NEWCUSTOMER_DISCOUNT => __b('Ip Addressen bei Neukunden Gutscheinen'),
            self::TYPE_KEYWORD_UUID => __b('UUID'),
            self::TYPE_KEYWORD_TEL => __b("Telefonnummer"),
            self::TYPE_KEYWORD_ADDRESS => __b("Adresse"),
            self::TYPE_KEYWORD_CUSTOMER => __b("Kunde"),
            self::TYPE_KEYWORD_COMPANY => __b("Firma"),
        );

        if ($prefix !== null) {
            switch (strtoupper($prefix)) {
                case 'PAYPAL':
                    return $paypalTypes;

                case 'EMAIL':
                case 'MAIL':
                    return $emailTypes;

                case 'KEYWORD':
                case 'KEYWORDS':
                    return $keywordTypes;

                default:
                    return array();
            }
        }

        return array_merge($paypalTypes, $emailTypes, $keywordTypes);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.06.2012
     * @return array
     */
    public static function getMatchings() {

        return array(
            self::MATCHING_EXACT => __b("Genauen Eintrag"),
            self::MATCHING_PARTIAL => __b("Enthält Eintrag"),
        );
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 14.06.2012
     * @return array
     */
    public static function getBehaviours() {

        return array(
            self::BEHAVIOUR_FAKE => __b("Fake"),
            self::BEHAVIOUR_BLACKLIST => __b('Blacklist'),
            self::BEHAVIOUR_WHITELIST => __b('Whitelist'),
        );
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012
     * @param string $behaviour
     * @return int
     */
    public static function getBehaviourToOrderState($behaviour) {

        switch ($behaviour) {
            case self::BEHAVIOUR_BLACKLIST:
                return Yourdelivery_Model_Order::FAKE_STORNO;

            default:
            case self::BEHAVIOUR_FAKE:
                return Yourdelivery_Model_Order::FAKE;
        }
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 18.06.2012
     * @param string $matching
     * @param string $str1
     * @param string $str2
     * @return boolean
     */
    public static function isMatching($matching, $str1, $str2) {

        switch ($matching) {
            // compare str1 to str2
            case self::MATCHING_EXACT:
                return strcasecmp($str1, $str2) == 0;

            // search str1 in str2
            case self::MATCHING_PARTIAL:
                return strpos($str2, $str1) !== false;
        }

        return false;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>, Vincent Priem <priem@lieferando.de>
     * @since 15.06.2012
     * @param array $types
     * @return array
     */
    public static function getList(array $types) {

        $t = array();
        foreach ($types as $type) {
            $t = array_merge($t, self::getTypes($type));
        }
        $t = array_keys($t);
        $key = implode("-", $t);

        if (array_key_exists($key, self::$_list)) {
            return self::$_list[$key];
        }

        $dbTable = new Yourdelivery_Model_DbTable_Blacklist_Values();
        return self::$_list[$key] = $dbTable->getList($t);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 19.06.2012
     */
    public static function flushList() {

        self::$_list = array();
    }

    /**
     * create a new key => value pair
     *
     * @author Matthias Laug <laug@lieferando.de>, Vincent Priem <priem@lieferando.de>
     * @since 12.06.2012
     * @return Yourdelivery_Model_Support_Blacklist_Values
     */
    public function addValue($type, $value, $matching = null, $behaviour = null) {

        self::flushList();

        //
        if ($type == self::TYPE_EMAIL_MINUTEMAILER) {
            $parts = explode('@', $value);
            if (count($parts) > 0) {
                $value = $parts[1];
            }
        }

        // set default matching
        if ($matching === null) {
            $matching = self::MATCHING_EXACT;
        }

        // set default behaviour
        if ($behaviour === null) {
            $behaviour = self::BEHAVIOUR_FAKE;

            if ($type === self::TYPE_KEYWORD_IP) {
                $behaviour = self::BEHAVIOUR_BLACKLIST;
            } elseif ($type === self::TYPE_KEYWORD_UUID) {
                $behaviour = self::BEHAVIOUR_BLACKLIST;
            }
        }

        $v = new Yourdelivery_Model_Support_Blacklist_Values();
        $v->setType($type);
        $v->setValue($value);
        $v->setMatching($matching);
        $v->setBehaviour($behaviour);

        $this->_values[$type] = $v;
        return $v;
    }

    /**
     * get a current value array
     *
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.06.2012
     * @return Yourdelivery_Model_Support_Blacklist_Values|null
     */
    public function getValue($key) {

        if (array_key_exists($key, $this->_values)) {
            return $this->_values[$key];
        }

        return null;
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 15.06.2012
     * @return Yourdelivery_Model_Support_Blacklist_Values[]
     */
    public function getValues() {

        return $this->_values;
    }

    /**
     * tales care of none unique entries and removes parent
     * element, if no unique element is created at all
     *
     * @author Matthias Laug <laug@lieferando.de>, Vincent Priem <priem@lieferando.de>
     * @since 12.06.2012
     */
    public function save() {

        $db = $this->getTable()->getDefaultAdapter();
        $db->beginTransaction();
        
        $id = (integer) parent::save();
        if (!$id) {
            throw new Yourdelivery_Exception_Database_Inconsistency('Could not create parent row for blacklist element');
        }

        $createdRows = 0;
        foreach ($this->_values as $v) {
            $v->setBlacklistId($this->getId());
            try {
                $v->save();
                $createdRows++;
            }
            // duplicated entry
            catch (Exception $e) {
                if (!strstr($e->getMessage(), 'Duplicate entry')) {
                    throw $e;
                }

                // assume values exists cause its a dupliacte entry
                $v = new Yourdelivery_Model_Support_Blacklist_Values(null, $v->getType(), $v->getValue());
                if ($v->restore()) {
                    $v->setBlacklistId($this->getId()); // update the parent blacklist
                    $v->save();
                    $createdRows++;
                }
            }
        }

        // if no entry has been created due to uniques, we rollback and remove that transaction as well
        if ($createdRows) {
            $db->commit();
            return $id;
        }

        $db->rollback();
        return false;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 14.06.2012
     * @param type $behaviour
     */
    public function setBehaviour($behaviour = self::BEHAVIOUR_FAKE) {

        foreach ($this->_values as $v) {
            $v->setBehaviour($behaviour);
        }
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>, Vincent Priem <priem@lieferando.de>
     * @since 14.06.2012
     */
    protected function _loadValues() {

        $rows = $this->getTable()
                ->getCurrent()
                ->findDependentRowset("Yourdelivery_Model_DbTable_Blacklist_Values");

        foreach ($rows as $row) {
            $v = new Yourdelivery_Model_Support_Blacklist_Values($row->id);
            $this->_values[$row->type] = $v;
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.06.2012
     * @return Yourdelivery_Model_DbTable_Blacklist
     */
    public function getTable() {

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Blacklist();
        }

        return $this->_table;
    }

}
