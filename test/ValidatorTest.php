<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__)."/../classes/autoloader/BAV_Autoloader.php";
BAV_Autoloader::add('../classes/dataBackend/BAV_DataBackend_PDO.php');
BAV_Autoloader::add('../classes/dataBackend/BAV_DataBackend_File.php');
BAV_Autoloader::add('../classes/dataBackend/exception/BAV_DataBackendException_BankNotFound.php');
BAV_Autoloader::add('../classes/class/BAV_ClassFile.php');
BAV_Autoloader::add('../classes/validator/exception/BAV_ValidatorException_NotExists.php');
BAV_Autoloader::add('../classes/bank/BAV_Bank.php');
BAV_Autoloader::add('../classes/bank/exception/BAV_ValidatorException_NotExists.php');


/**
 * check all validators in order to find errors
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
 */


class ValidatorTest extends PHPUnit_Framework_TestCase {


    private static
    /**
     * @var BAV_DataBackend
     */
    $dataBackend,
    /**
     * @var array This array contains all names of implemented algorithms as keys.
     */
    $implementedBanks = array(),
    /**
     * @var array all known banks
     */
    $knownBanks = array();


    /**
     * @throws BAV_FileParserException_IO
     * @throws BAV_FileParserException_FileNotExists
     */
    protected function setUp() {
    	if (! empty(self::$dataBackend)) {
    		return;
    		
    	}
        #self::$dataBackend = new BAV_DataBackend_PDO(new PDO('mysql:host=localhost;dbname=test', 'test'));
        self::$dataBackend = new BAV_DataBackend_File();


        foreach (self::$dataBackend->getAllBanks() as $bank) {
            self::$knownBanks[$bank->getValidationType()] = $bank;

        }
    }
    
    
    /**
     * @return Array
     */
    public function provideImplementedValidators() {
    	$implementedValidators = array();
    	$files = BAV_ClassFile::getClassFiles(dirname(__FILE__).'/../classes/validator/validators/');
    	foreach ($files as $class) {
    	   if (! preg_match('~^BAV_Validator_([A-Z0-9]{2})$~', $class->getName(), $matchType)) {
                continue;

            }
    		$implementedValidators[] = array($matchType[1]);
    		
    	}
    	return $implementedValidators;
    }


    /**
     * This Test runs all validators in order to find parse Errors
     * and fills {@link $implementedBanks}.
     *
     * @param String $validatorType
     * @throws BAV_ClassFileException_IO
     * @throws BAV_ClassFileException_MissingClass
     * @dataProvider provideImplementedValidators
     */
    public function testFindParseErrors($implementedValidatorType) {
        $bank = array_key_exists($implementedValidatorType, self::$knownBanks)
              ? $bank = self::$knownBanks[$implementedValidatorType]
              : new BAV_Bank(self::$dataBackend, 0, $implementedValidatorType);

        self::$implementedBanks[$implementedValidatorType] = $bank;
        /**
         * testing 10 random bank accounts
         */
        for ($i = 0; $i < 10; $i++) {
            $bank->isValid(mt_rand(0, 9999999999));
    
        }
    }
    
    
    /**
     * @return Array
     */
    public function provideTestAccounts() {
        $verifyArray = parse_ini_file(dirname(__FILE__).'/../data/verify.ini', true);
        if (! $verifyArray) {
            throw new RuntimeException("couldn't parse verify.ini.");
            
        }
        return array_merge(
            $this->getTestAccounts($verifyArray['valid'],   true),
            $this->getTestAccounts($verifyArray['invalid'], false)
        );
    }
    
    
    /**
     * @return Array
     */
    private function getTestAccounts(Array $testAccounts, $expectedValidation) {
    	$accounts = array();
        foreach ($testAccounts as $typeOrBankID => $tests) {
            $accounts[] = array(
                $typeOrBankID,
                preg_split(':\D+:', $tests),
                $expectedValidation
            );
            
        }
        return $accounts;
    }
    
    
    /**
     * @dataProvider provideTestAccounts
     */
    public function testAccount($typeOrBankID, Array $accountIDs, $expectedValidation) {
        if (strlen($typeOrBankID) <= 2) {
            $typeOrBankID = (strlen($typeOrBankID) < 2 ? '0' : '').$typeOrBankID;
            $this->assertArrayHasKey($typeOrBankID, self::$implementedBanks);
            $bank = self::$implementedBanks[$typeOrBankID];

        } else {
        	try {
                $bank = self::$dataBackend->getBank($typeOrBankID);
                    
            } catch (BAV_DataBackendException_BankNotFound $e) {
                switch ($e->getBankID()) {
                    
                    case '80053762': case '80053772': case '80053782':
                        $bank = new BAV_Bank(self::$dataBackend, $e->getBankID(), 'B6');
                        break;
                        
                    case '13051172':
                        $bank = new BAV_Bank(self::$dataBackend, $e->getBankID(), '52');
                        break;
                            
                    case '16052072':
                        $bank = new BAV_Bank(self::$dataBackend, $e->getBankID(), '53');
                        break;
                    
                    default: throw $e;
                        
                }
            }
        }
        
        foreach ($accountIDs as $accountID) {
        	$this->assertTrue($bank->isValid($accountID) === $expectedValidation);

        }
    }


}


?>