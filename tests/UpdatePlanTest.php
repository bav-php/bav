<?php

namespace malkusch\bav;

require_once __DIR__ . "/../autoloader/autoloader.php";

/**
 * Tests UpdatePlan
 *
 * @license GPL
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
        $file = tempnam(\BAV_DataBackend_File::getTempdir(), 'bavtest');
        touch($file, strtotime($mtime));
        $backend = new \BAV_DataBackend_File($file);

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
        $updatePlan->perform(new \BAV_DataBackend_File());
    }

    /**
     * @expectedException \PHPUnit_Framework_Error_Notice
     * @medium
     */
    public function testAutomaticUpdatePlanNotice()
    {
        $file = tempnam(\BAV_DataBackend_File::getTempdir(), 'bavtest');
        $updatePlan = new AutomaticUpdatePlan();
        $updatePlan->perform(new \BAV_DataBackend_File($file));
    }

    /**
     * @expectedException \PHPUnit_Framework_Error_Notice
     * @medium
     */
    public function testAutomaticUpdatePlan()
    {
        $file = tempnam(\BAV_DataBackend_File::getTempdir(), 'bavtest');
        touch($file, strtotime("-1 year"));
        $backend = new \BAV_DataBackend_File($file);

        $updatePlan = new AutomaticUpdatePlan();
        $this->assertTrue($updatePlan->isOutdated($backend));

        $updatePlan->perform($backend);
        $this->assertFalse($updatePlan->isOutdated($backend));
    }
}
