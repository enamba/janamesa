<?php

/**
 * Description of ApiCall
 *
 * @author Daniel Hahn <hahn@lieferando.de>
 */
class Goolive_ApiCall {

    protected $_user = "XXXX";

    protected $_password = "XXX";

    protected $_url = "http://www.goolive.de/common/api/goocard_validation.php";

    protected $_result = null;

    protected $_resultCodes = array(1 => 'Kartennummer ist gültig',
        -1 => "Fehlender Parameter 'cardnumber'",
        -2 => "Fehlender Parameter 'auth'",
        -3 => "Ungültige Zugangsdaten",
        -4 => "Kartennummer ist ungültig"
    );

    /**
     * Generates the auth param for the request
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 27.07.2012
     * @return string
     */
    protected function _generatePassword() {
        $block = intval(gmmktime() / 60) * 60;
        return md5($this->_password . 'goolive-api' . $block);
    }

    /**
     * Validates a code.
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 27.07.2012
     * @param string $code the code to validate
     * @return boolean
     */
    public function validate($code) {

        $url = $this->_url . "?auth=" . md5($this->_user) . $this->_generatePassword() . "&cardnumber=" . urlencode($code);
        $this->_result = @file_get_contents($url);

        if ($this->_result !== false) {
            return strpos($this->_result, '1') !== false;
        }

        return false;
    }

    /**
     * Gets the result code for the current result
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 27.07.2012
     * @return string
     */
    public function getResultCode() {

        if ($this->_result != null) {
            return $this->_resultCodes[$this->_result];
        }
        return "Genereller Fehler";
    }

}
