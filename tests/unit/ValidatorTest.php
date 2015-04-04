<?php

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";

/**
 * check all validators in order to find errors
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @license WTFPL
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

        #self::$dataBackend = new PDODataBackend(PDOFactory::makePDO());
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
        $files = ClassFile::getClassFiles(__DIR__.'/../../classes/validator/validators/');
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
        $verifyArray = json_decode(file_get_contents(__DIR__ . '/../data/accounts.json'));
        if (! is_array($verifyArray)) {
            throw new \RuntimeException("couldn't parse accounts.json.");

        }
        array_walk(
            $verifyArray,
            function (&$item) {
                $item = array($item);
            }
        );
        return $verifyArray;
    }
    
    /**
     * Validator::isValid() with an int should raise a warning.
     * 
     * @see Validator::isValid()
     * @dataProvider provideBanks
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testWarningForIsValidWithInt(Bank $bank)
    {
        $intAccount = 0020012357;
        $bank->isValid($intAccount);
    }

    /**
     * @dataProvider provideTestAccounts
     */
    public function testAccount(\stdClass $testCase)
    {
        if (! empty($testCase->blz)) {
            $bank = new Bank(self::$dataBackend, $testCase->blz, $testCase->validator);

        } else {
            $this->assertArrayHasKey($testCase->validator, self::$implementedBanks);
            $bank = self::$implementedBanks[$testCase->validator];

            $this->assertEquals($testCase->validator, $bank->getValidationType());

        }

        $test = $this;
        $checkAccounts = function ($account, $key, $isValid) use ($bank, $test, $testCase) {
            $test->assertEquals(
                $isValid,
                $bank->isValid($account),
                "Validator $testCase->validator validates wrongly for account $account."
            );
        };

        if (isset($testCase->valid)) {
            array_walk($testCase->valid, $checkAccounts, true);

        }
        if (isset($testCase->invalid)) {
            array_walk($testCase->invalid, $checkAccounts, false);

        }
    }
}
