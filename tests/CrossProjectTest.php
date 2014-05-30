<?php

namespace malkusch\bav;

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
 * @large
 */
class CrossProjectTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var int
     */
    #private $lastAccount = 9999999999,
    private $lastAccount = 99999;

    /**
     * @var Array
     */
    private $testedValidators = array();

    /**
     * @var Array
     */
    private $failedBankDependentValidators = array();

    /**
     * @var TestAPI[]
     */
    private static $testAPIs = array();

    public static function setUpBeforeClass()
    {
        self::$testAPIs = array(new BAVTestAPI());

        try {
            self::$testAPIs[] = new KontocheckTestAPI();

        } catch (TestAPIUnavailableException $e) {
            trigger_error("KontocheckTestAPI unavailable", E_USER_WARNING);

        }

        try {
            self::$testAPIs[] = new KtoblzcheckTestAPI();

        } catch (TestAPIUnavailableException $e) {
            trigger_error("KtoblzcheckTestAPI unavailable", E_USER_WARNING);

        }
    }

    /**
     * @return Bank[][]
     */
    public function provideBanks()
    {
        $banks   = array();
        $backend = new PDODataBackend(new \PDO('mysql:host=localhost;dbname=test', 'test'));
        foreach ($backend->getAllBanks() as $bank) {
            $banks[] = array($bank);

        }
        
        // $banks = array_slice($banks, 0, 60);
    
        return $banks;
    }

    /**
     * @dataProvider provideBanks
     */
    public function testCrossProjects(Bank $bank)
    {
        try {
            $isSkip = $bank->getValidator() instanceof Validator_BankDependent
                    ? array_key_exists($bank->getValidationType(), $this->failedBankDependentValidators)
                    : array_key_exists($bank->getValidationType(), $this->testedValidators);

            if ($isSkip) {
                return;

            }

            for ($account = $this->lastAccount; $account >= 0; $account--) {
                for ($pad = strlen($account); $pad <= strlen($this->lastAccount); $pad++) {
                    $paddedAccount = str_pad($account, $pad, "0", STR_PAD_LEFT);
                    $this->assertSameResult($bank, $paddedAccount);

                }
            }

        } catch (BankNotFoundTestAPIException $e) {
            return;

        } catch (Exception $e) {
            if ($bank instanceof Validator_BankDependent) {
                $this->failedBankDependentValidators[$bank->getValidationType()] = true;

            }
            throw $e;

        }
        $this->testedValidators[$bank->getValidationType()] = true;
    }

    private function assertSameResult(Bank $bank, $account)
    {
        $results = array();
        $resultValues = array();
        foreach (self::$testAPIs as $key => $testAPI) {
            if ($testAPI->ignoreTestCase($bank, $account)) {
                continue;

            }
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
     * @param Bank $bank
     * @param String $account
     * @param array $results
     * @return String
     */
    private function getErrorMessage(Bank $bank, $account, Array $results)
    {
        $resultTranslation = array(
            TestAPIResult::VALID   => "valid",
            TestAPIResult::INVALID => "invalid",
            TestAPIResult::ERROR   => "error"
        );

        $message = "{$bank->getBankID()}/{$bank->getValidationType()}\t"
                 . str_pad($account, strlen($this->lastAccount)) . "\t";

        foreach ($results as $result) {
            $message .= "{$result->getTestAPI()->getName()}: "
                     .  str_pad($resultTranslation[$result->getResult()], 8);
            if ($result instanceof TestAPIErrorResult) {
                $message .= " {$result->getMessage()}";

            }
            $message .= "\t";

        }

        return $message;
    }
}
