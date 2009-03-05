#!/usr/bin/php
<?php
error_reporting(E_ALL);

require_once dirname(__FILE__)."/../classes/autoloader/BAV_Autoloader.php";
BAV_Autoloader::add('../classes/BAV.php');
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
 * @package scripts
 * @subpackage test
 * @author Markus Malkusch <bav@malkusch.de>
 * @copyright Copyright (C) 2006 Markus Malkusch
 */


class BAV_CheckValidators extends BAV {


    private
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
    public function __construct() {
        echo "\n\n";
        #$this->dataBackend = new BAV_DataBackend_PDO(new PDO('mysql:host=localhost;dbname=test', 'test'));
        $this->dataBackend = new BAV_DataBackend_File();


        /**
         * get all banks for known types
         */
        foreach ($this->dataBackend->getAllBanks() as $bank) {
            # echo $bank->getValidationType(), '=>', $bank->getBankID(), "\n";
            $this->knownBanks[$bank->getValidationType()] = $bank;

        }
        /**
         * Run the first test and fill {@link $implementedBanks}
         */
        $this->test_findParseErrors();
        /**
         * The second test should find wrong imlpementations
         */
        $this->test_findWrongImplementations();

        echo "\n\n";
    }


    /**
     * This Test runs all validators in order to find parse Errors
     * and fills {@link $implementedBanks}.
     *
     * @throws BAV_ClassFileException_IO
     * @throws BAV_ClassFileException_MissingClass
     */
    public function test_findParseErrors() {
        echo "\n\n", 'running all Validators in order to find parse errors...', "\n\n";

        mt_srand(time());
        foreach (BAV_ClassFile::getClassFiles(dirname(__FILE__).'/../classes/validator/validators/') as $class) {
            if (! preg_match('~^BAV_Validator_([A-Z0-9]{2})$~', $class->getName(), $matchType)) {
                continue;

            }
            $type = $matchType[1];
            echo $type, ' ';
            $bank = array_key_exists($type, $this->knownBanks)
                  ? $bank = $this->knownBanks[$type]
                  : new BAV_Bank($this->dataBackend, 0, $type);

            $this->implementedBanks[$type] = $bank;
            /**
             * testing 10 random bank accounts
             */
            try {
                for ($i = 0; $i < 10; $i++) {
                    $bank->isValid(mt_rand(0, 9999999999));
    
                }
                
            } catch (BAV_ValidatorException_NotExists $e) {
                throw new LogicException("The validator must exist!");
            
            }

            /**
             * search for patterns in the Validator
             */
            /*$validator = file_get_contents(BAV_CLASS_PATH.'validator/validators/'.$matchType[0]);
            if (! $validator) {
                continue;
            }

            if (preg_match('~[^:]verfahren_~', $validator)) {
                echo $matchType[0].' calls a verfahren_*()', "\n";
            }*/

        }

    }



    /**
     * The second test should find wrong implementations
     *
     * @throws BAV_DataBackendException_BankNotFound
     * @throws BAV_DataBackendException
     * @throws BAV_ValidatorException_NotExists
     */
    public function test_findWrongImplementations() {
        echo "\n\n", 'trying some test accounts to find wrong or missing implementations...', "\n\n";

        $verifyArray = parse_ini_file(dirname(__FILE__).'/../data/verify.ini', true);
        if (! $verifyArray) {
            throw new RuntimeException("couldn't parse verify.ini.");
            
        }
        $validTests   = $verifyArray['valid'];
        $invalidTests = $verifyArray['invalid'];

        echo 'verify valid accounts...', "\n\n";
        $notValidResult = $this->testNumbers($validTests, true);
        echo "\n\n", 'verify invalid accounts...', "\n\n";
        $notInvalidResult = $this->testNumbers($invalidTests, false);
        
        
        echo "\n\n", 'not valid accounts: ', "\n\n";
        $this->printUnexpectedResults($notValidResult);
        echo "\n\n", 'not invalid accounts: ', "\n\n";
        $this->printUnexpectedResults($notInvalidResult);
    }



    /**
     * Tests numbers if they validate as expected
     *
     * @throws BAV_DataBackendException_BankNotFound
     * @throws BAV_DataBackendException
     * @throws BAV_ValidatorException_NotExists
     * @param int $expectedValidationCode The expected validation code
     * @return array unexpected {@link BAV_ValidatorResult} objects
     */
    private function testNumbers(Array $numberArray, $expectedValidation) {
        $unexpectedResults = array();
        foreach($numberArray as $typeOrBankID => $accountList) {
            $accountIDs = preg_split(':\D+:', $accountList);
            if (strlen($typeOrBankID) <= 2) {
                $typeOrBankID = (strlen($typeOrBankID) < 2 ? '0' : '').$typeOrBankID;
                if (! isset($this->implementedBanks[$typeOrBankID])) {
                    throw new LogicException("missing bank for '$typeOrBankID'.");
                
                }
                $bank = $this->implementedBanks[$typeOrBankID];

            } else {
                try {
                    $bank = $this->dataBackend->getBank($typeOrBankID);
                    
                } catch (BAV_DataBackendException_BankNotFound $e) {
                    switch ($e->getBankID()) {
                    
                        case '80053762': case '80053772': case '80053782':
                            $bank = new BAV_Bank($this->dataBackend, $e->getBankID(), 'B6');
                            break;
                        
                        case '13051172':
                            $bank = new BAV_Bank($this->dataBackend, $e->getBankID(), '52');
                            break;
                            
                        case '16052072':
                            $bank = new BAV_Bank($this->dataBackend, $e->getBankID(), '53');
                            break;
                    
                        default: throw $e;
                        
                    }
                
                }

            }
            echo $bank->getValidationType();
            foreach ($accountIDs as $accountID) {
                echo '.';
                if ($bank->isValid($accountID) !== $expectedValidation) {
                    $unexpectedResults[] = array($bank, $accountID);

                }

            }

        }
        return $unexpectedResults;

    }

    /**
     * prints the unexpected Accounts
     *
     * @param array $results
     */
    private function printUnexpectedResults($results) {
        foreach ($results as $result) {
            $account = $result[1];
            $bank    = $result[0];
            echo '      Type: ', $bank->getValidationType(),    "\n";
            echo '   Bank ID: ', $bank->getBankID(),            "\n";
            echo 'Account ID: ', $account,                      "\n\n";

        }
    }



}
new BAV_CheckValidators();


?>