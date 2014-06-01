<?php

namespace malkusch\bav;

require_once __DIR__ . "/../autoloader/autoloader.php";

/**
 * Tests URIPicker.
 *
 * @license GPL
 * @author Markus Malkusch <markus@malkusch.de>
 */
class URIPickerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Provides URIPicker implementations.
     *
     * @return URIPicker[][]
     */
    public function provideURIPicker()
    {
        return array(
            array(new RegExpURIPicker()),
            array(new DOMURIPicker()),
            array(new FallbackURIPicker()),
        );
    }

    /**
     * All picker return the same result.
     *
     * @medium
     */
    public function testSameResults()
    {
        $lastResult = null;
        $html = file_get_contents(FileDataBackend::DOWNLOAD_URI);
        foreach ($this->provideURIPicker() as $picker) {
            $picker = $picker[0];
            $result = $picker->pickURI($html);
            if (is_null($lastResult)) {
                $lastResult = $result;
                continue;

            }

            $this->assertEquals($lastResult, $result);
        }
    }

    /**
     * Tests pickURI()
     *
     * @dataProvider provideURIPicker
     */
    public function testPickURI(URIPicker $picker)
    {
        $html = file_get_contents(__DIR__ . "/data/bankleitzahlen_download.html");
        $uri = $picker->pickURI($html);

        $this->assertEquals(
            "/Redaktion/DE/Downloads/Aufgaben/Unbarer_Zahlungsverkehr/Bankleitzahlen/2014_09_07/blz_2014_06_09_txt.txt?__blob=publicationFile",
            $uri
        );
    }

    /**
     * Tests pickURI()
     *
     * @dataProvider provideURIPicker
     * @expectedException malkusch\bav\URIPickerException
     */
    public function testFailPickURI(URIPicker $picker)
    {
        $html = "XXX";
        $uri = $picker->pickURI($html);
    }

    /**
     * All pickers are available on this platform.
     *
     * @dataProvider provideURIPicker
     */
    public function testIsAvailable(URIPicker $picker)
    {
        $this->assertTrue($picker->isAvailable());
    }
}
