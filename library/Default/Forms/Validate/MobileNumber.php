<?php

/**
 * Form for validating mobile phone number
 *
 * @author Alex Vait <vait@lieferando.de>
 * @since 31.07.2012
 */
class Default_Forms_Validate_MobileNumber extends Zend_Validate_Abstract {
    
    const NOT_MOBILE = 'notMobileNumber';

    /**
     * @var array 
     */
    protected $_messageTemplates = array(
        self::NOT_MOBILE => 'not a mobile phone number'
    );

    
    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 31.07.2012
     * @param string $value
     * @return boolean
     */
    public function isValid($value) {
        if (!Default_Helpers_Phone::isMobile(Default_Helpers_Normalize::telephone($value))) {
             $this->_error(self::NOT_MOBILE);
             return false;
        }
        
        return true;
    }

}