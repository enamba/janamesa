<?php

/**
 * Description of Heidelpay
 * @package payment
 * @subpackage heidelpay
 * @author mlaug
 */
class Yourdelivery_Payment_Heidelpay {

    /**
     * @author vpriem
     * @since 03.11.11
     * @param string $returnCode
     * @return boolean
     */
    public static function isFake($returnCode) {

        return in_array($returnCode, array(
            "800.200.159", // account or user is blacklisted (card stolen)
            "800.200.160", // account or user is blacklisted (card blocked)
            "800.200.165", // account or user is blacklisted (card lost)
            "800.200.202", // account or user is blacklisted (account closed)
            "800.200.208", // account or user is blacklisted (account blocked)
            "800.200.220", // account or user is blacklisted (fraudulent transaction)
            "800.300.101", // account or user is blacklisted
            "800.300.102", // country blacklisted
            "800.300.200", // email is blacklisted
            "800.300.301", // ip blacklisted
            "800.300.302", // ip is anonymous proxy
            "800.300.401", // bin blacklisted
            "800.300.500", // transaction temporary blacklisted (too many tries invalid CVV)
            "800.300.501", // transaction temporary blacklisted (too many tries invalid expire date)
        ));
    }    
    
}
