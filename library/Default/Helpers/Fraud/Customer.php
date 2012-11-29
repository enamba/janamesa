<?php

/**
 * Detector of blacklisted data dedicated for a customer
 *
 * @author Marek Hejduk
 * @since 4.07.2012
 */
class Default_Helpers_Fraud_Customer {
    /**
     * Loaded blacklist data
     * @var array
     */
    protected static $_blacklist;

    /**
     * Scan all values appearing in customer data, returns blacklist key of first blacklisted value (if found)
     *
     * @author Marek Hejduk
     * @since 5.07.2012
     *
     * @param array $values
     * @return string|null
     */
    public static function multidetect($values) {
        if (empty($values)) {
            // nothing to be checked
            return null;
        }

        // Beware: all passed values may be invalid
        $city = self::_getVal('city', $values);
        if (!strlen($city)) {
            $cityId = self::_getVal('cityId', $values);
            $cityRecord = (is_numeric($cityId))
                ? Yourdelivery_Model_City::getById($cityId)
                : null;
            if (!is_null($cityRecord)) {
                $city = $cityRecord->city;
            }
        }
        $email = self::_getVal('email', $values);
        $emailChunks = explode('@', $email);
        $emailDomain = self::_getVal(1, $emailChunks);

        // composing a complete list of values to be checked values
        $needles = array(
            Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_ADDRESS => sprintf(
                '%s %s %s %s',
                self::_getVal('street', $values),
                self::_getVal('hausnr', $values),
                self::_getVal('plz', $values),
                $city
            ),
            Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_COMPANY => self::_getVal('companyName', $values),
            Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_CUSTOMER => sprintf(
                '%s %s',
                self::_getVal('prename', $values),
                self::_getVal('name', $values)
            ),
            Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_IP => Default_Helpers_Web::getClientIp(),
            Yourdelivery_Model_Support_Blacklist::TYPE_KEYWORD_TEL => self::_getVal('tel', $values),
            Yourdelivery_Model_Support_Blacklist::TYPE_EMAIL => $email,
            Yourdelivery_Model_Support_Blacklist::TYPE_EMAIL_MINUTEMAILER => $emailDomain,
        );

        // do checking
        return self::_getDetectedType($needles);
    }

    /**
     * Says whether value of selected type exists on a blacklist (true) or not (false)
     *
     * @author Marek Hejduk
     * @since 5.07.2012
     *
     * @param string $needle
     * @param string $type
     * @return bool
     */
    public static function detect($needle, $type) {
        return (strlen($needle) > 0)
            ? (bool)self::_getDetectedType(array($type => $needle))
            : false;
    }

    /**
     * Returns array's value with empty string fallback for non-existing keys
     *
     * @author Marek Hejduk
     * @since 5.07.2012
     *
     * @param mixed $key
     * @param array $values
     * @return mixed
     */
    protected static function _getVal($key, $values) {
        return (array_key_exists($key, $values))? $values[$key]: '';
    }

    /**
     * Does the blacklist checking, returns the key of first blacklisted value (if found)
     * 
     * @author Marek Hejduk
     * @since 5.07.2012
     *
     * @param array $needles
     * @param array $types
     * @return string|null
     */
    protected static function _getDetectedType($needles, $types = null) {
        // pre-loading blacklist data
        if (!isset(self::$_blacklist)) {
            self::$_blacklist = Yourdelivery_Model_Support_Blacklist::getList(array('email', 'keyword'));
        }
        // checking blacklist - entry by entry
        foreach (self::$_blacklist as $entry) {
            if ($entry->isDeprecated()) {
                $entry->delete();
                continue ;
            } elseif (!empty($types) && !in_array($entry->type, $types)) {
                continue ;
            }

            $needle = self::_getVal($entry->type, $needles);
            if (strlen($needle) > 0 && Yourdelivery_Model_Support_Blacklist::isMatching($entry->matching, $entry->value, $needle)) {
                // needle value exists on blacklist - we've detected it!
                Zend_Registry::get('logger')->warn(sprintf(
                    "BLACKLIST: customer's data: %s (type: %s) rejected",
                    $needle, $entry->type
                ));

                return $entry->type;
            }
        }

        // all checked values seem to be clear
        return null;
    }
}
