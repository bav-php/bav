<?php

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";

/**
 * Tests the facade BAV.
 *
 * @license WTFPL
 * @author Markus Malkusch <markus@malkusch.de>
 * @see BAV
 */
class BAVFacadeTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * Tests BAV::getBank();
     *
     * @see BAV::getBank();
     */
    public function testGetBank()
    {
        $bav = new BAV();
        $bank = $bav->getBank("73362500");
        $this->assertNotNull($bank);
    }

    /**
     * Tests BAV::getBank();
     *
     * @expectedException malkusch\bav\BankNotFoundException
     * @see BAV::getBank();
     */
    public function testFailGetBank()
    {
        $bav = new BAV();
        $bav->getBank("12345678");
    }
    
    /**
     * Tests BAV::getAgencies();
     *
     * @expectedException malkusch\bav\BankNotFoundException
     * @see BAV::getAgencies();
     */
    public function testFailGetAgencies()
    {
        $bav = new BAV();
        $bav->getAgencies("12345678");
    }

    /**
     * Test cases for testGetAgencies()
     *
     * @return array
     * @see testGetAgencies()
     */
    public function provideTestGetAgencies()
    {
        return array(
            array("73362500", 0),
            array("10070000", 2),
            array("10020890", 5),
        );
    }

    /**
     * Tests BAV::getAgencies();
     *
     * @dataProvider provideTestGetAgencies
     * @see BAV::getAgencies();
     */
    public function testGetAgencies($bankID, $count)
    {
        $bav = new BAV();
        $agencies = $bav->getAgencies($bankID);
        $this->assertEquals($count, count($agencies));
    }

    /**
     * Tests BAV::getMainAgency();
     *
     * @see BAV::getMainAgency();
     */
    public function testGetMainAgency()
    {
        $bav = new BAV();
        $agency = $bav->getMainAgency("73362500");
        $this->assertNotNull($agency);
    }

    /**
     * Tests BAV::getMainAgency();
     *
     * @expectedException malkusch\bav\BankNotFoundException
     * @see BAV::getMainAgency();
     */
    public function testFailGetMainAgency()
    {
        $bav = new BAV();
        $bav->getMainAgency("12345678");
    }

    /**
     * Test cases for testIsValidBank()
     * 
     * @see testBankExists()
     * @return array
     */
    public function provideTestIsValidBank()
    {
        return array(
            array("73362500", true),
            array("12345678", false),
        );
    }

    /**
     * Tests BAV::isValidBank();
     *
     * @dataProvider provideTestIsValidBank
     * @see BAV::isValidBank();
     */
    public function testIsValidBank($bankID, $expected)
    {
        $bav = new BAV();
        $this->assertEquals($expected, $bav->isValidBank($bankID));
    }

    /**
     * Test cases for testIsValidBankAccount()
     * 
     * @see testIsValidBankAccount()
     * @return array
     */
    public function provideTestIsValidBankAccount()
    {
        return array(
            array("73362500", "0110030005", false),
            array("73362500", "0010030005", true),
        );
    }

    /**
     * Tests BAV::isValidBankAccount();
     *
     * @dataProvider provideTestIsValidBankAccount
     * @see BAV::isValidBankAccount();
     */
    public function testIsValidBankAccount($bankID, $account, $expected)
    {
        $bav = new BAV();
        $this->assertEquals($expected, $bav->isValidBankAccount($bankID, $account));
    }

    /**
     * Test cases for testIsValidBIC()
     * 
     * @see testIsValidBIC()
     * @return array
     */
    public function provideTestIsValidBIC()
    {
        return array(
            array("VZVDDED1XXX", true),
            array("VZVDDED1", true),
            array("VZVDDED1~~~", false),
        );
    }

    /**
     * Tests BAV::isValidBankAccount();
     *
     * @dataProvider provideTestIsValidBIC
     * @see BAV::isValidBIC();
     */
    public function testIsValidBIC($bic, $expected)
    {
        $bav = new BAV();
        $this->assertEquals($expected, $bav->isValidBIC($bic));
    }

    /**
     * Test cases for testGetBICAgencies()
     * 
     * @see testGetBICAgencies()
     * @return array
     */
    public function provideTestGetBICAgencies()
    {
        return array(
            array("VZVDDED1XXX", array("52944")),
            array("VZVDDED1", array("52944")),
            array("VZVDDED1~~~", array()),
        );
    }

    /**
     * Tests BAV::getBICAgencies();
     *
     * @dataProvider provideTestGetBICAgencies
     * @see BAV::getBICAgencies();
     */
    public function testGetBICAgencies($bic, $expected)
    {
        $bav = new BAV();
        $agencies = $bav->getBICAgencies($bic);
        $getID = function (Agency $agency) {
            return $agency->getID();
        };
        $agenciesIds = array_map($getID, $agencies);
        
        sort($expected);
        sort($agenciesIds);
        $this->assertEquals($expected, $agenciesIds);
    }
    
    /**
     * Tests filter validation
     * 
     * @see BAV::getValidAccountFilterCallback()
     * @see BAV::getValidBankFilterCallback()
     */
    public function testFilterValidation()
    {
        $bav = new BAV();
        
        $this->assertFalse(
            filter_var(
                "0",
                FILTER_CALLBACK,
                $bav->getValidBankFilterCallback()
            )
        );
        
        $this->assertTrue(
            filter_var(
                "10000000",
                FILTER_CALLBACK,
                $bav->getValidBankFilterCallback()
            )
        );
        
        $this->assertTrue(
            filter_var(
                "12345",
                FILTER_CALLBACK,
                $bav->getValidAccountFilterCallback()
            )
        );
        
        $this->assertFalse(
            filter_var(
                "0",
                FILTER_CALLBACK,
                $bav->getValidAccountFilterCallback()
            )
        );
    }
    
    /**
     * Tests BAV::isValidAccount();
     *
     * @dataProvider provideTestIsValidBankAccount
     * @see BAV::isValidAccount();
     */
    public function testValidAccount($bankID, $account, $expected)
    {
        $bav = new BAV();
        $bav->isValidBank($bankID);
        
        $this->assertEquals($expected, $bav->isValidAccount($account));
    }
}
