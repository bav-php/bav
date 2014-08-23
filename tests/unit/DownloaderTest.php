<?php

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";

/**
 * Tests Downloader.
 *
 * @license WTFPL
 * @author Markus Malkusch <markus@malkusch.de>
 */
class DownloaderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Returns URIs and the expected md5 sum of their contents.
     *
     * @return String[][]
     */
    public function provideTestURIs()
    {
        return array(
            array("http://www.gnu.org/licenses/gpl-2.0.txt", "b234ee4d69f5fce4486a80fdaf4a4263")
        );
    }

    /**
     * Tests downloadContent();
     * 
     * @param string $uri URI
     * @param string $md5 expected MD5 sum
     * @dataProvider provideTestURIs
     * @see Downloader::downloadContent()
     */
    public function testDownloadContent($uri, $md5)
    {
        $downloader = new Downloader();
        $content = $downloader->downloadContent($uri);
        $this->assertEquals($md5, md5($content));
    }

    /**
     * Tests downloadFile();
     * 
     * @param string $uri URI
     * @param string $md5 expected MD5 sum
     * @dataProvider provideTestURIs
     * @see Downloader::downloadFile()
     */
    public function testDownloadFile($uri, $md5)
    {
        $downloader = new Downloader();
        $file = $downloader->downloadFile($uri);
        $this->assertEquals($md5, md5_file($file));
        unlink($file);
    }

    /**
     * Tests a sequence of downloadContent() and downloadFile().
     * 
     * @param string $uri URI
     * @param string $md5 expected MD5 sum
     * @dataProvider provideTestURIs
     * @see Downloader::downloadFile()
     * @see Downloader::downloadContent()
     */
    public function testDownloadSequence($uri, $md5)
    {
        $downloader = new Downloader();
        
        $content = $downloader->downloadContent($uri);
        $this->assertEquals($md5, md5($content));

        $file = $downloader->downloadFile($uri);
        $this->assertEquals($md5, md5_file($file));
        unlink($file);

        $content = $downloader->downloadContent($uri);
        $this->assertEquals($md5, md5($content));
    }

    /**
     * Tests failing downloadContent();
     * 
     * @expectedException malkusch\bav\DownloaderException
     * @see Downloader::downloadContent()
     */
    public function testFailDownloadContent()
    {
        $downloader = new Downloader();
        $downloader->downloadContent("http://example.org/XXX");
    }

    /**
     * Tests failing downloadFile();
     * 
     * @expectedException malkusch\bav\DownloaderException
     * @see Downloader::downloadFile()
     */
    public function testFailDownloadFile()
    {
        $downloader = new Downloader();
        $file = $downloader->downloadFile("http://example.org/XXX");
    }
}
