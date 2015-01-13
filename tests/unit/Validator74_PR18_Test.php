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
class Validator74_PR18_Test extends \PHPUnit_Framework_TestCase
{

    /**
     * Tests for Validator74 which produces accumulators which end from 1 to 14.
     * 
     * A halfdecade seems to be understanded as 5, 15, 25, ….
     * 
     * @param string $account  The account id.
     * @param bool   $expected The expected validation result.
     * 
     * @dataProvider provideTestAllHalfDecades
     * @link https://www.mail-archive.com/aqbanking-devel@lists.sourceforge.net/msg01292.html
     */
    public function testAllHalfDecades($account, $expected)
    {
        $backend = $this->getMock("malkusch\bav\FileDataBackend");
        $bank = $this->getMock(
                "malkusch\bav\Bank", array(), array($backend, 1, 74));
        
        $validator = new Validator74($bank);
        $this->assertEquals($expected, $validator->isValid($account));
    }
    
    /**
     * Returns test cases for testAllHalfDecades().
     * 
     * @return array Test cases.
     */
    public function provideTestAllHalfDecades()
    {
        return array(
            array("500004", true), //  5 - 1  = 4
            array("100003", true), //  5 - 2  = 3
            array("100102", true), //  5 - 3  = 2
            array("200001", true), //  5 - 4  = 1
            array("111000", true), // 15 - 5  = 10 (0)
            array("200209", true), // 15 - 6  = 9
            array("200308", true), // 15 - 7  = 8
            array("200407", true), // 15 - 8  = 7
            array("200506", true), // 15 - 9  = 6
            array("200605", true), // 15 - 10 = 5
            array("190004", true), // 15 - 11 = 4
            array("200803", true), // 15 - 12 = 3
            array("200902", true), // 15 - 13 = 2
            array("144001", true), // 15 - 14 = 1
        );
    }
}
