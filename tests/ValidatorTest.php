<?php

namespace malkusch\bav;

require_once __DIR__ . "/../autoloader/autoloader.php";

/**
 * check all validators in order to find errors
 *
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
class ValidatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var DataBackend
     */
    private static $dataBackend;

    /**
     * @var array This array contains all names of implemented algorithms as keys.
     */
    private static $implementedBanks = array();

    /**
     * @var array all known banks
     */
    private static $knownBanks = array();

    /**
     * @throws FileParserIOException
     * @throws FileParserNotExistsException
     */
    protected function setUp()
    {
        if (! empty(self::$dataBackend)) {
            return;

        }
        $container = new FileDataBackendContainer();

        #self::$dataBackend = new PDODataBackend(new \PDO('mysql:host=localhost;dbname=test', 'test'));
        self::$dataBackend = $container->getDataBackend();


        foreach (self::$dataBackend->getAllBanks() as $bank) {
            self::$knownBanks[$bank->getValidationType()] = $bank;

        }
    }

    /**
     * @return Array
     */
    public function provideBanks()
    {
        $this->setUp();

        $banks = array();
        $files = ClassFile::getClassFiles(__DIR__.'/../classes/validator/validators/');
        foreach ($files as $class) {
            if (! preg_match('~^Validator([A-Z0-9]{2})$~', $class->getName(), $matchType)) {
                continue;

            }
            $validatorType = $matchType[1];
            $bank = array_key_exists($validatorType, self::$knownBanks)
                  ? self::$knownBanks[$validatorType]
                  : new Bank(self::$dataBackend, 12345678, $validatorType);

            $banks[] = array($bank);
            self::$implementedBanks[$validatorType] = $bank;

        }
        return $banks;
    }

    /**
     * @return Array
     */
    public function provideAccountsAndBanksInAllLengths()
    {
        $providedAccountsAndBanks = array();
        foreach ($this->provideBanks() as $bank) {
            $bank = $bank[0];
            for ($length = 0; $length <= 10; $length++) {
                $providedAccountsAndBanks[] = array($bank, str_repeat(1, $length));

            }
        }
        return $providedAccountsAndBanks;
    }

    /**
     * This Test runs all validators in order to find parse Errors
     * and fills {@link $implementedBanks}.
     *
     * @param String $validatorType
     * @throws ClassFileIOException
     * @throws MissingClassException
     * @dataProvider provideBanks
     */
    public function testFindParseErrors(Bank $bank)
    {
        /**
         * testing 10 random bank accounts
         */
        for ($i = 0; $i < 10; $i++) {
            $bank->isValid(mt_rand(0, 9999999999));

        }
    }

    /**
     * 0 - 0000000000 should always be invalid
     *
     * @param String $validatorType
     * @throws ClassFileIOException
     * @throws MissingClassException
     * @dataProvider provideBanks
     */
    public function testNullIsInvalid(Bank $bank)
    {
        for ($length = 0; $length <= 10; $length++) {
            $account = str_pad("0", $length, "0", STR_PAD_LEFT);
            $this->assertFalse(
                $bank->isValid($account),
                "{$bank->getBankID()}/{$bank->getValidationType()} $account should be invalid."
            );

        }
    }

    /**
     * Short accounts should not raise exception.
     *
     * @param int $account
     * @throws ClassFileIOException
     * @throws MissingClassException
     * @dataProvider provideAccountsAndBanksInAllLengths
     */
    public function testAccountLength(Bank $bank, $account)
    {
        $bank->isValid($account);
    }

    /**
     * @return Array
     */
    public function provideTestAccounts()
    {
        $verifyArray = parse_ini_file(__DIR__.'/../data/verify.ini', true);
        if (! $verifyArray) {
            throw new RuntimeException("couldn't parse verify.ini.");

        }
        return array_merge(
            $this->getTestAccounts($verifyArray['valid'], true),
            $this->getTestAccounts($verifyArray['invalid'], false)
        );
    }

    /**
     * @return Array
     */
    private function getTestAccounts(Array $testAccounts, $expectedValidation)
    {
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
    public function testAccount($typeOrBankID, Array $accountIDs, $expectedValidation)
    {
        if (strlen($typeOrBankID) <= 2) {
            $typeOrBankID = (strlen($typeOrBankID) < 2 ? '0' : '').$typeOrBankID;
            $this->assertArrayHasKey($typeOrBankID, self::$implementedBanks);
            $bank = self::$implementedBanks[$typeOrBankID];

            $this->assertEquals($typeOrBankID, $bank->getValidationType());

        } else {
            try {
                $bank = self::$dataBackend->getBank($typeOrBankID);

            } catch (BankNotFoundException $e) {
                switch ($e->getBankID()) {

                    case '13051052':
                    case '13051172':
                    case '81053132':
                        $bank = new Bank(self::$dataBackend, $e->getBankID(), '52');
                        break;

                    case '16052072':
                    case '85055142':
                        $bank = new Bank(self::$dataBackend, $e->getBankID(), '53');
                        break;

                    case '80053762':
                    case '80053772':
                    case '80053782':
                        $bank = new Bank(self::$dataBackend, $e->getBankID(), 'B6');
                        break;

                    case '81053272':
                    case '86055462':
                        $bank = new Bank(self::$dataBackend, $e->getBankID(), 'C0');
                        break;

                    default:
                        throw $e;

                }
            }
        }

        foreach ($accountIDs as $accountID) {
            $this->assertEquals(
                $expectedValidation,
                $bank->isValid($accountID),
                "$accountID validates wrongly."
            );

        }
    }
}
