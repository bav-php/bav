<?php

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";

/**
 * Tests BICUtil.
 *
 * @license WTFPL
 * @see BICUtil
 * @author Markus Malkusch <markus@malkusch.de>
 */
class BICUtilTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test cases for testNormalize
     * 
     * @see testNormalize()
     */
    public function provideTestNormalize()
    {
        return array(
            array("VZVDDED1", "VZVDDED1XXX"),
            array("VZVDDED1ABC", "VZVDDED1ABC"),
        );
    }

    /**
     * Tests BICUtil:normalize()
     * 
     * @see BICUtil::normalize()
     * @dataProvider provideTestNormalize()
     */
    public function testNormalize($bic, $expected)
    {
        $this->assertEquals($expected, BICUtil::normalize($bic));
    }
}
