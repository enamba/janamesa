<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__)."/../classes/autoloader/BAV_Autoloader.php";
BAV_Autoloader::add('../classes/verify/BAV_VerifyImport.php');
BAV_Autoloader::add('../classes/dataBackend/BAV_DataBackend_File.php');
BAV_Autoloader::add('../classes/dataBackend/exception/BAV_DataBackendException_BankNotFound.php');


/**
 * A test for BAV_VerifyImport.
 *
 *
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
 * @package test
 * @author Markus Malkusch <bav@malkusch.de>
 * @copyright Copyright (C) 2009 Markus Malkusch
 * @see BAV_VerifyImport
 */


class VerifyImportTest extends PHPUnit_Framework_TestCase {


    private
    /**
     * @var array
     */
    $validationMap = array(),
    /**
     * @var array
     */
    $verifyArray = array(),
    /**
     * @var BAV_DataBackend_File
     */
    $databack;
    
    
    protected function setUp() {
        $this->databack    = new BAV_DataBackend_File();
        $this->verifyArray = parse_ini_file(dirname(__FILE__).'/../data/verify.ini', true);
        
        $this->assertType(
            PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY,
            $this->verifyArray,
            "Could not parse verify.ini"
        );
        
        foreach ($this->databack->getAllBanks() as $bank) {
            $this->validationMap[$bank->getValidationType()] = $bank;
        
        }
    }
    
    
    public function testFileImport() {
        $importer = new BAV_VerifyImport($this->databack);
        $importer->importVerifyFile();
        $this->assertImporter($importer);
    }
    
    
    public function testSequentialImport() {
        $importer       = new BAV_VerifyImport($this->databack);
        $notSupported   = array();
        foreach ($this->verifyArray as $expect => $array) {
            foreach ($array as $type => $accounts) {
                try {
                    $type     = (strlen($type) < 2 ? '0' : '').$type;
                    $accounts = preg_split('~\D+~', $accounts);
                    $bankID   = strlen($type) === 2 ? $this->getBank($type)->getBankID() : $type;
                    foreach ($accounts as $account) {
                        $importer->import($bankID, $account, $expect === 'valid' ? true : false);
    
                    }
                    
                } catch (BAV_DataBackendException_BankNotFound $e) {
                    $notSupported[] = $type;
                
                }
                
            }
            
        }
        $this->assertImporter($importer, $notSupported);
    }
    
    
    private function assertImporter(BAV_VerifyImport $importer, Array $notSupported = array()) {
        $file = tempnam('/tmp', 'BAV');
        $this->assertFileExists($file);
        
        $importer->save($file);
        $checkArray = parse_ini_file($file, true);
        unlink($file);
        
        $this->assertType(
            PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY,
            $checkArray,
            "Could not parse temporary file $file."
        );
        
        foreach ($this->verifyArray as $expect => $array) {
            foreach ($array as $type => $accounts) {
            	$actualAccounts = @$checkArray[$expect][$type];
            	unset($checkArray[$expect][$type]);
            	if (array_search($type, $notSupported) !== false) {
            		continue;
            		
            	}
            	$this->assertEquals(
            	    preg_replace('~\D~', '', $accounts),
            	    preg_replace('~\D~', '', $actualAccounts),
            	    "[$expect]$type is not equal!"
            	);
                
            }
            
        }
        
        $this->assertEquals(0, count($checkArray['valid']));
        $this->assertEquals(0, count($checkArray['invalid']));
    }
    
    
    /**
     * @param String $validationType
     * @return BAV_Bank
     */
    private function getBank($validationType) {
        if (! isset($this->validationMap[$validationType])) {
            throw new BAV_DataBackendException_BankNotFound($validationType);
        
        }
        return $this->validationMap[$validationType];
    }

}


?>