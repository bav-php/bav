<?php

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";

/**
 * Test for the issue of PR18.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @license WTFPL
 * @link https://github.com/bav-php/bav/pull/18
 */
class Validator66_PR18_Test extends \PHPUnit_Framework_TestCase
{

    /**
     * Tests for Validator66 which produces the remainer 0 and 1.
     * 
     * @param string $account  The account id.
     * @param bool   $expected The expected validation result.
     * 
     * @dataProvider provideTestRemainer0and1
     * @link https://www.mail-archive.com/aqbanking-devel@lists.sourceforge.net/msg01292.html
     */
    public function testRemainer0and1($account, $expected)
    {
        $backend = $this->getMock("malkusch\bav\FileDataBackend");
        $bank = $this->getMock(
                "malkusch\bav\Bank", array(), array($backend, 1, 66));
        
        $validator = new Validator66($bank);
        $this->assertEquals($expected, $validator->isValid($account));
    }
    
    /**
     * Returns test cases for testRemainer0and1().
     * 
     * @return array Test cases.
     */
    public function provideTestRemainer0and1()
    {
        return array(
            array("0100001001", true), //  0
            array("0100201000", true), //  1
        );
    }
}
