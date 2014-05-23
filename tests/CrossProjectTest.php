<?php


require_once __DIR__ . "/../autoloader/autoloader.php";


/**
 * Copyright (C) 2009  Markus Malkusch <markus@malkusch.de>
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
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2009 Markus Malkusch
 */


class CrossProjectTest extends PHPUnit_Framework_TestCase
{
	
	
	private
	/**
	 * @var int
	 */
	#$lastAccount = 9999999999,
	$lastAccount = 99999,
	/**
	 * @var Array
	 */
	$testedValidators = array(),
	/**
     * @var Array
     */
    $failedBankDependentValidators = array(),
	/**
	 * @var Array
	 */
	$testAPIs = array();
	
	
	/**
	 * @return Array
	 */
	protected function setUp() {
        $ktoblzcheckPath = __DIR__ . "/../tmp/ktoblzcheck/ktoblzcheck-1.21/src";
	   
		$this->testAPIs = array(
            new BAV_TestAPI_BAV(),
            new BAV_TestAPI_Kontocheck('/etc/blz.lut2', 2),
            new BAV_TestAPI_Ktoblzcheck(
                "$ktoblzcheckPath/bankdata/bankdata.txt",
                "$ktoblzcheckPath/bin/ktoblzcheck"
            )
        );
	}
	
	
	/**
	 * @return Array
	 */
	public function provideBanks() {
		$banks   = array();
		$backend = new BAV_DataBackend_PDO(new PDO('mysql:host=localhost;dbname=test', 'test'));
		foreach ($backend->getAllBanks() as $bank) {
			$banks[] = array($bank);
			
		}
		return $banks;
	}
	
	
	/**
	 * @dataProvider provideBanks
	 */
	public function testCrossProjects(BAV_Bank $bank) {
        try {
			$isSkip = $bank->getValidator() instanceof BAV_Validator_BankDependent
			        ? array_key_exists($bank->getValidationType(), $this->failedBankDependentValidators)
			        : array_key_exists($bank->getValidationType(), $this->testedValidators);
			        
			if ($isSkip) {
	            return;
					     
	        }

		    for ($account = $this->lastAccount; $account >= 0; $account--) {
	            for($pad = strlen($account); $pad <= strlen($this->lastAccount); $pad++) {
	                $paddedAccount = str_pad($account, $pad, "0", STR_PAD_LEFT);
			    	$this->assertSameResult($bank, $paddedAccount);
			    	
	            }
		    }
			
        } catch (BAV_TestAPIException_Validation_BankNotFound $e) {
        	return;
        	
        } catch (Exception $e) {
            if ($bank instanceof BAV_Validator_BankDependent) {
            	$this->failedBankDependentValidators[$bank->getValidationType()] = true;
            	
            }
            throw $e;	   	
	   	
        }
        $this->testedValidators[$bank->getValidationType()] = true;
	}
	
	
	private function assertSameResult(BAV_Bank $bank, $account) {
		$results = array();
		$resultValues = array();
		foreach ($this->testAPIs as $key => $testAPI) {
			$result          = $testAPI->getResult($bank, $account);
			$results[]       = $result;
			$resultValues[]  = $result->getResult();
			
		}
		
		$this->assertEquals(
            1,
            count(array_unique($resultValues)),
            $this->getErrorMessage($bank, $account, $results)
        );
	}
	
	
	/**
	 * @param BAV_Bank $bank
	 * @param String $account
	 * @param array $results
	 * @return String
	 */
	private function getErrorMessage(BAV_Bank $bank, $account, Array $results) {
		$resultTranslation = array(
            BAV_TestAPIResult::VALID   => "valid",
            BAV_TestAPIResult::INVALID => "invalid",
            BAV_TestAPIResult::ERROR   => "error"
        );
        
        $message = "{$bank->getBankID()}/{$bank->getValidationType()}\t"
                 . str_pad($account, strlen($this->lastAccount)) . "\t";
             
        foreach ($results as $result) {
            $message .= "{$result->getTestAPI()->getName()}: "
                     .  str_pad($resultTranslation[$result->getResult()], 8);
            if ($result instanceof BAV_TestAPIResult_Error) {
                $message .= " {$result->getMessage()}";
                
            }
            $message .= "\t";
            
        }
        
        return $message;
	}
	
	
}


?>