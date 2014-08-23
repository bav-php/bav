<?php

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";

/**
 * Tests UpdatePlan
 *
 * This dates uses a mock for time(). Therefore this test must run before
 * any unmocked call to time().
 *
 * @see TimeMock
 * @license WTFPL
 * @author Markus Malkusch <markus@malkusch.de>
 */
class UpdatePlanTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Provide test cases for testIsOutdated()
     * 
     * @see testIsOutdated()
     */
    public function provideTestIsOutdated()
    {
        return array(
            array("2012-11-01", "2013-01-31", false),
            array("2012-10-31", "2013-01-31", true),
            array("2013-02-01", "2013-04-01", false),
            array("2013-01-31", "2013-04-01", true),
            array("2013-02-01", "2013-02-01", false),
            array("2013-01-31", "2013-02-01", true),
            array("2013-02-01", "2013-02-31", false),
            array("2013-01-31", "2013-02-31", true),
            array("2013-02-01", "2013-03-01", false),
            array("2013-01-31", "2013-03-01", true),
            array("2013-02-01", "2013-03-31", false),
            array("2013-01-31", "2013-03-31", true),
        );
    }

    /**
     * Tests isOutdated()
     * 
     * @dataProvider provideTestIsOutdated
     * @param string $mtime file time
     * @param string $time current time
     * @param bool $expectedIsOutdated
     * @see UpdatePlan::isOutdated()
     */
    public function testIsOutdated($mtime, $time, $expectedIsOutdated)
    {
        $fileUtil = new FileUtil();
        $file = tempnam($fileUtil->getTempDirectory(), 'bavtest');
        touch($file, strtotime($mtime));
        $backend = new FileDataBackend($file);

        TimeMock::setTime(strtotime($time));

        $updatePlan = new AutomaticUpdatePlan();
        $this->assertEquals($expectedIsOutdated, $updatePlan->isOutdated($backend));

        TimeMock::disable();
    }

    /**
     * @expectedException \PHPUnit_Framework_Error_Warning
     */
    public function testLogUpdatePlan()
    {
        $updatePlan = new LogUpdatePlan();
        $updatePlan->perform(new FileDataBackend());
    }

    /**
     * @expectedException \PHPUnit_Framework_Error_Notice
     * @medium
     */
    public function testAutomaticUpdatePlanNotice()
    {
        $fileUtil = new FileUtil();
        $file = tempnam($fileUtil->getTempDirectory(), 'bavtest');
        $updatePlan = new AutomaticUpdatePlan();
        $updatePlan->perform(new FileDataBackend($file));
    }

    /**
     * @expectedException \PHPUnit_Framework_Error_Notice
     * @medium
     */
    public function testAutomaticUpdatePlan()
    {
        $fileUtil = new FileUtil();
        $file = tempnam($fileUtil->getTempDirectory(), 'bavtest');
        touch($file, strtotime("-1 year"));
        $backend = new FileDataBackend($file);

        $updatePlan = new AutomaticUpdatePlan();
        $this->assertTrue($updatePlan->isOutdated($backend));

        $updatePlan->perform($backend);
        $this->assertFalse($updatePlan->isOutdated($backend));
    }
}
