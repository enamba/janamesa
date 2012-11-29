#!/usr/bin/php
<?php
error_reporting(E_ALL);

require_once dirname(__FILE__)."/../classes/autoloader/BAV_Autoloader.php";
BAV_Autoloader::add('../classes/BAV.php');
BAV_Autoloader::add('../classes/bank/BAV_Bank.php');
BAV_Autoloader::add('../classes/validator/BAV_Validator_BankDependent.php');
BAV_Autoloader::add('../classes/verify/testAPI/BAV_TestAPIResult_Error.php');
BAV_Autoloader::add('../classes/verify/testAPI/BAV_TestAPI_BAV.php');
BAV_Autoloader::add('../classes/verify/testAPI/BAV_TestAPI_Kontocheck.php');
BAV_Autoloader::add('../classes/verify/testAPI/BAV_TestAPI_Ktoblzcheck.php');
BAV_Autoloader::add('../classes/verify/testAPI/exception/BAV_TestAPIException_Validation_BankNotFound.php');
BAV_Autoloader::add('../classes/dataBackend/BAV_DataBackend_File.php');
BAV_Autoloader::add('../classes/dataBackend/BAV_DataBackend_PDO.php');


/**
 * Copyright (C) 2009  Markus Malkusch <bav@malkusch.de>
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
 * @package scripts
 * @subpackage test
 * @author Markus Malkusch <bav@malkusch.de>
 * @copyright Copyright (C) 2009 Markus Malkusch
 */


class BAV_CheckAgainstTestAPIs extends BAV {
	
	
	const VALID            = 1;
	const INVALID          = 2;
	const BANK_NOT_FOUND   = 3;
	const ERROR            = 4;

	
	private
	/**
	 * @var int
	 */
	#$firstAccount = 9999999999,
	$firstAccount = 999,
	/**
	 * @var int
	 */
	$lastAccount = 1,
	/**
	 * @var Array
	 */
	$testedValidators = array(),
	/**
	 * @var Array
	 */
	$differences = array(),
	/**
	 * @var Array
	 */
	$testAPIs = array();
	
	
	public function __construct() {
        $ktoblzcheckPath = dirname(__FILE__) . "/../tmp/ktoblzcheck/ktoblzcheck-1.21/src";
	
		$this->testAPIs[] = new BAV_TestAPI_BAV();
		$this->testAPIs[] = new BAV_TestAPI_Kontocheck('/etc/blz.lut', 2);
		$this->testAPIs[] = new BAV_TestAPI_Ktoblzcheck(
            "$ktoblzcheckPath/bankdata/bankdata.txt",
		    "$ktoblzcheckPath/bin/ktoblzcheck"
        );
		
		
		#$backend = new BAV_DataBackend_File();
		$backend = new BAV_DataBackend_PDO(new PDO('mysql:host=localhost;dbname=test', 'test'));
		
		
		if (! empty($GLOBALS['argv'][1])) {
            $nodeNumber = $GLOBALS['argv'][1];
            $nodeCount  = @$GLOBALS['argv'][2];
            
            if ($nodeNumber * $nodeCount == 0 || min($nodeNumber, $nodeCount) < 0) {
            	trigger_error(
                    'Expect two numeric arguments > 0: $nodeNumber $nodeCount',
            	    E_USER_ERROR
            	);
            	
            }
            
            if ($nodeNumber > $nodeCount) {
            	trigger_error(
            	    'Expect first argument ($nodeNumber) <= second argument ($nodeCount)',
            	    E_USER_ERROR
            	);
            	
            }
			
		} else {
			$nodeNumber = 1;
            $nodeCount  = 1;
			
		}
		
		$increment = $this->lastAccount > $this->firstAccount ? 1 : -1;
        $padLength = strlen(max($this->lastAccount, $this->firstAccount));
        
		$count            = ceil(abs($this->lastAccount - $this->firstAccount) / $nodeCount);
		$firstAccount     = $this->firstAccount + $increment * ($nodeNumber - 1) * ($count + 1);
		
		if ($nodeCount == $nodeNumber) {
			$afterLastAccount = $this->lastAccount + $increment;
			
		} else {
            $afterLastAccount = $firstAccount + $increment * ($count + 1);	
			
		}
		
		foreach ($backend->getAllBanks() as $bank) {
			try {
				if ( array_key_exists($bank->getValidationType(), $this->testedValidators)
				     && ! $bank->getValidator() instanceof BAV_Validator_BankDependent) {
				     	
				     continue;
				     
			    }
			    
			    for ($account = $firstAccount; $account != $afterLastAccount; $account += $increment) {
			    	for($pad = strlen($account); $pad <= $padLength; $pad++) {
	                	$paddedAccount = str_pad($account, $pad, "0", STR_PAD_LEFT);
				    	$differences = count($this->differences);
				    	$this->testAccount($bank, $paddedAccount);
				    	if (count($this->differences) > $differences) {
				    		break 2;
				    		
				    	}
	                }
			    }
				
				$this->testedValidators[$bank->getValidationType()] = true;
				
			} catch (BAV_TestAPIException_Validation_BankNotFound $e) {
				continue;
				
			}
		}
	}
	
	
	private function testAccount(BAV_Bank $bank, $account) {
		$results = array();
		$resultValues = array();
		foreach ($this->testAPIs as $key => $testAPI) {
			$result          = $testAPI->getResult($bank, $account);
			$results[]       = $result;
			$resultValues[]  = $result->getResult();
			
		}
		
		if (count(array_unique($resultValues)) == 1) {
			return;
			
		}
		
		
		$resultTranslation = array(
            BAV_TestAPIResult::VALID   => "valid",
            BAV_TestAPIResult::INVALID => "invalid",
            BAV_TestAPIResult::ERROR   => "error"
		);
		
		echo "{$bank->getBankID()}/{$bank->getValidationType()}\t",
		     str_pad($account, strlen($this->lastAccount)),     "\t";
		     
		foreach ($results as $result) {
			echo "{$result->getTestAPI()->getName()}: ",
			     str_pad($resultTranslation[$result->getResult()], 8);
			if ($result instanceof BAV_TestAPIResult_Error) {
				echo " {$result->getMessage()}";
				
			}
			echo "\t";
			
		}
		echo "\n";
		
		$this->differences[] = array($bank, $account, $results);
	}
	
	
}
new BAV_CheckAgainstTestAPIs();


?>