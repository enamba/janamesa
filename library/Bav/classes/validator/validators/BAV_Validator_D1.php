<?php
BAV_Autoloader::add('BAV_Validator_00.php');
BAV_Autoloader::add('../BAV_Validator.php');
BAV_Autoloader::add('../../bank/BAV_Bank.php');


/**
 * Copyright (C) 2008  Markus Malkusch <bav@malkusch.de>
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
 * @copyright Copyright (C) 2008 Markus Malkusch
 */
class BAV_Validator_D1 extends BAV_Validator {


    protected
    /**
     * @var String
     */
    $transformedAccount = '',
    /**
     * @var BAV_Validator_00
     */
    $validator;
    
    const TRANSFORMATION = 428259;


    public function __construct(BAV_Bank $bank) {
        parent::__construct($bank);
        
        $this->validator = new BAV_Validator_00($bank);
        $this->validator->setNormalizedSize(10 + strlen(self::TRANSFORMATION));
    }
    
    
    protected function validate() {
        $this->transformedAccount = self::TRANSFORMATION.$this->account;
    }
    
    
    /**
     * @return bool
     */
    protected function getResult() {
        return ! in_array($this->account{0}, array(0, 3, 9))
            && $this->validator->isValid($this->transformedAccount);
    }
    

}


?>