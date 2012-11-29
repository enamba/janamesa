<?php
BAV_Autoloader::add('BAV_Validator.php');
BAV_Autoloader::add('../bank/BAV_Bank.php');


/**
 * This abstract class offers support for algorithmns which uses more algorithmns
 *
 * You have to add the algorithms to the $this->validators array.
 *
 * Copyright (C) 2006  Markus Malkusch <bav@malkusch.de>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *
 * @package classes
 * @subpackage validator
 * @author Markus Malkusch <bav@malkusch.de>
 * @copyright Copyright (C) 2006 Markus Malkusch
 */
abstract class BAV_Validator_Chain extends BAV_Validator {


    protected
    /**
     * @var Array a list of validators
     */
    $validators = array();


    /**
     * Iterates through the validators.
     *
     * @access protected
     * @param BAV_Account $account
     * @return bool
     */
    protected function getResult() {
        foreach ($this->validators as $validator) {
            if (! $this->continueValidation($validator)) {
                return false;
                
            }
            if ($this->useValidator($validator) && $validator->isValid($this->account)) {
                return true;
            
            }
        
        }
        return false;
    }
    /**
     * should not be used
     */
    final protected function validate() {
    }
    /**
     * After each successless iteration step this method will be called and
     * should return if the iteration should stop and the account is invalid.
     *
     * @return bool
     */
    protected function continueValidation(BAV_Validator $validator) {
        return true;
    }
    /**
     * Decide if you really want to use this validator
     *
     * @return bool
     */
    protected function useValidator(BAV_Validator $validator) {
        return true;
    }
    

}


?>