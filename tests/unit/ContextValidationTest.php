<?php

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";

/**
 * Tests ContextValidation
 *
 * @license WTFPL
 * @see ContextValidation
 * @author Markus Malkusch <markus@malkusch.de>
 */
class ContextValidationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ContextValidation
     */
    private $validation;
    
    protected function setUp()
    {
        $bav = new BAV();
        $this->validation = new ContextValidation($bav->getDataBackend());
    }

    /**
     * Tests switching the bank context
     * 
     * @see ContextValidation::isValidBank()
     */
    public function testContextSwitch()
    {
        $this->validation->isValidBank("0");
        $this->assertTrue($this->validation->isValidAccount("0"));
        
        $this->validation->isValidBank("10000000");
        $this->assertFalse($this->validation->isValidAccount("0"));
        
        $this->validation->isValidBank("0");
        $this->assertTrue($this->validation->isValidAccount("0"));
    }
    
    /**
     * Tests isValidBank()
     * 
     * @see ContextValidation::isValidBank()
     */
    public function testValidBank()
    {
        $this->assertTrue($this->validation->isValidBank("10000000"));
        $this->assertFalse($this->validation->isValidBank("0"));
    }
    
    /**
     * Tests isValidAccount()
     * 
     * @see ContextValidation::isValidAccount()
     */
    public function testValidAccount()
    {
        // set context
        $this->validation->isValidBank("10000000");
        
        $this->assertTrue($this->validation->isValidAccount("12345"));
        $this->assertFalse($this->validation->isValidAccount("0"));
    }
    
    /**
     * Tests filter validation
     * 
     * @see ContextValidation::getValidAccountFilterCallback()
     * @see ContextValidation::getValidBankFilterCallback()
     */
    public function testFilterValidation()
    {
        // Valid bank context
        $this->assertTrue(
            filter_var(
                "10000000",
                FILTER_CALLBACK,
                $this->validation->getValidBankFilterCallback()
            )
        );
        $this->assertTrue(
            filter_var(
                "12345",
                FILTER_CALLBACK,
                $this->validation->getValidAccountFilterCallback()
            )
        );
        $this->assertFalse(
            filter_var(
                "0",
                FILTER_CALLBACK,
                $this->validation->getValidAccountFilterCallback()
            )
        );
        
        // Invalid bank context, account always valid
        $this->assertFalse(
            filter_var(
                "12345",
                FILTER_CALLBACK,
                $this->validation->getValidBankFilterCallback()
            )
        );
        $this->assertTrue(
            filter_var(
                "0",
                FILTER_CALLBACK,
                $this->validation->getValidAccountFilterCallback()
            )
        );
    }
    
    /**
     * Test an invalid context
     * 
     * @see ContextValidation::getValidAccountFilterCallback()
     * @expectedException malkusch\bav\InvalidContextException
     */
    public function testFilterInvalidContext()
    {
        filter_var(
            "0",
            FILTER_CALLBACK,
            $this->validation->getValidAccountFilterCallback()
        );
    }
    
    /**
     * Test an invalid context
     * 
     * @see ContextValidation::isValidAccount()
     * @expectedException malkusch\bav\InvalidContextException
     */
    public function testInvalidContext()
    {
        $this->validation->isValidAccount("12345");
    }
}
