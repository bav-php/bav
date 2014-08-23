<?php

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";

/**
 * Tests the values between different projects.
 * 
 * This test takes a very long time (days!). You should split it on several
 * machines. You can therefore set the environment variables NODE_NUMBER, which
 * starts with 0 and NODE_COUNT. Two cores would run with:
 * 
 * $ NODE_NUMBER=0 NODE_COUNT=2 phpunit CrossProjectTest.php
 * $ NODE_NUMBER=1 NODE_COUNT=2 phpunit CrossProjectTest.php
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @license WTFPL
 * @large
 */
class CrossProjectTest extends \PHPUnit_Framework_TestCase
{

    /**
     * name of environment variable for setting this node number.
     */
    const ENV_NODE_NUMBER = "NODE_NUMBER";
    
    /**
     * name of environment variable for setting the node count.
     */
    const ENV_NODE_COUNT = "NODE_COUNT";

    /**
     * @var int
     */
    #private $lastAccount = 9999999999,
    private $lastAccount = 99999;

    /**
     * @var int
     */
    private $accountPadSize = 10;

    /**
     * @var Array
     */
    private $testedValidators = array();

    /**
     * @var Array
     */
    private $failedBankDependentValidators = array();
    
    /**
     * @var int
     */
    private static $nodeNumber = 0;
    
    /**
     * @var int
     */
    private static $nodeCount = 1;

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
            //Too slow and version 1.45 has many false positives
            //self::$testAPIs[] = new KtoblzcheckTestAPI();

        } catch (TestAPIUnavailableException $e) {
            trigger_error("KtoblzcheckTestAPI unavailable", E_USER_WARNING);

        }
    }

    /**
     * @return Bank[][]
     */
    public function provideBanks()
    {
        $nodeNumber = getenv(self::ENV_NODE_NUMBER);
        if ($nodeNumber) {
            self::$nodeNumber = (int) $nodeNumber;

        }
        $nodeCount = getenv(self::ENV_NODE_COUNT);
        if ($nodeCount) {
            self::$nodeCount = (int) $nodeCount;

        }
        self::assertGreaterThan(self::$nodeNumber, self::$nodeCount);

        $banks   = array();
        $backend = new PDODataBackend(PDOFactory::makePDO());
        $i = 0;
        foreach ($backend->getAllBanks() as $bank) {
            // only pick banks for this node.
            if ($i % self::$nodeCount == self::$nodeNumber) {
                $banks[] = array($bank);

            }
            $i++;

        }
    
        return $banks;
    }

    /**
     * @dataProvider provideBanks
     */
    public function testCrossProjects(Bank $bank)
    {
        try {
            // Skip failed bank dependend validators or valid independend validators.
            $isSkip = $bank->getValidator() instanceof Validator_BankDependent
                    ? array_key_exists($bank->getValidationType(), $this->failedBankDependentValidators)
                    : array_key_exists($bank->getValidationType(), $this->testedValidators);

            if ($isSkip) {
                return;

            }

            // Generate accounts from $this->lastAccount until 0.
            for ($account = $this->lastAccount; $account >= 0; $account--) {

                // Generate accounts with padded zeros up to $this->accountPadSize
                for ($pad = strlen($account); $pad <= $this->accountPadSize; $pad++) {
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

        $message = "bank: {$bank->getBankID()}  method: {$bank->getValidationType()}  account: $account";

        foreach ($results as $result) {
            $message .= "  {$result->getTestAPI()->getName()}: ". $resultTranslation[$result->getResult()];
            if ($result instanceof TestAPIErrorResult) {
                $message .= " {$result->getMessage()}";

            }
        }

        return $message;
    }
}
