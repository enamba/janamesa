<?php
BAV_Autoloader::add('../../bank/BAV_Bank.php');
BAV_Autoloader::add('../BAV_Validator_Chain.php');
BAV_Autoloader::add('BAV_Validator_06.php');
BAV_Autoloader::add('BAV_Validator_90c.php');
BAV_Autoloader::add('BAV_Validator_90d.php');
BAV_Autoloader::add('BAV_Validator_90e.php');


/**
 * Implements 90
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
 */


class BAV_Validator_90 extends BAV_Validator_Chain {


    private
    /**
     * @var BAV_Validator
     */
    $modeF,
    /**
     * @var array
     */
    $defaultValidators = array();


    public function __construct(BAV_Bank $bank) {
        parent::__construct($bank);

        
        $this->defaultValidators[] = new BAV_Validator_06($bank);
        $this->defaultValidators[0]->setWeights(array(2, 3, 4, 5, 6, 7));
        $this->defaultValidators[0]->setEnd(3);
        
        $this->defaultValidators[] = new BAV_Validator_06($bank);
        $this->defaultValidators[0]->setWeights(array(2, 3, 4, 5, 6));
        $this->defaultValidators[0]->setEnd(4);
        
        $this->defaultValidators[] = new BAV_Validator_90c($bank);
        $this->defaultValidators[] = new BAV_Validator_90d($bank);
        $this->defaultValidators[] = new BAV_Validator_90e($bank);
        
        $this->modeF = new BAV_Validator_06($bank);
        $this->modeF->setWeights(array(2, 3, 4, 5, 6, 7, 8));
        $this->modeF->setEnd(2);
    }
    
    
    /**
     */
    protected function init($account) {
        parent::init($account);
        
        $this->validators = $this->account{2} == 9
                          ? array($this->modeF)
                          : $this->defaultValidators;
    }


}


?>