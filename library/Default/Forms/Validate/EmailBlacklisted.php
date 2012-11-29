<?php

/**
 * Description of EmailBlacklisted
 *
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 05.01.2012
 */
class Default_Forms_Validate_EmailBlacklisted extends Zend_Validate_Abstract {
    
    const BLACKLISTED = 'emailIsBlacklisted';

    /**
     * @var array 
     */
    protected $_messageTemplates = array(
        self::BLACKLISTED => 'could not verify email'
    );

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 05.01.2012
     * @param string $value
     * @return boolean
     */
    public function isValid($value) {
        /* @deprecated BLACKLIST */
        if (file_exists(BLACKLIST)) {
            $handle = @fopen(BLACKLIST, 'r');
            if ($handle !== false) {
                $value = strtolower(trim($value));
                while (!feof($handle)) {
                    $buffer = strtolower(trim(fgets($handle)));
                    if (strcmp($value, $buffer) == 0) {
                        fclose($handle);
                        $this->_error(self::BLACKLISTED);
                        return false;
                    }
                }
                fclose($handle);
            }
        }
        return true;
    }

}