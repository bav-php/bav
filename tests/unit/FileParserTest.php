<?php

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";

/**
 * Tests FileParser
 *
 * @license WTFPL
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @see FileParser
 */
class FileParserTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * Tests using the default file
     * 
     * @see FileParser::__construct()
     */
    public function testDefaultFile()
    {
        $parser = new FileParser();
        $this->assertEquals(
            realpath(__DIR__ . "/../../data/banklist.txt"),
            realpath($parser->getFile())
        );
    }
    
    /**
     * Tests getAgency()
     * 
     * @see FileParser::getAgency()
     */
    public function testGetAgency()
    {
        $this->markTestIncomplete();
    }
    
    /**
     * Tests getBank()
     * 
     * @see FileParser::getBank()
     */
    public function testGetBank()
    {
        $this->markTestIncomplete();
    }
    
    /**
     * Tests getBankID()
     * 
     * @see FileParser::getBankID()
     */
    public function testGetBankID()
    {
        $this->markTestIncomplete();
    }
    
    /**
     * Tests getLineLength()
     * 
     * @see FileParser::getLineLength()
     * @dataProvider provideTestGetLineLength
     */
    public function testGetLineLength($lineLength, $file)
    {
        $parser = new FileParser($file);
        $this->assertEquals($lineLength, $parser->getLineLength());
    }
    
    /**
     * Test cases for testGetLineLength()
     * 
     * @return array
     * @see testGetLineLength()
     */
    public function provideTestGetLineLength()
    {
        return array(
            array(6, __DIR__ . "/../data/fileParserTest/simple.txt"),
            array(170, __DIR__ . "/../data/fileParserTest/bb-excerpt.txt"),
        );
    }
    
    /**
     * Tests getLines()
     * 
     * @see FileParser::getLines()
     * @dataProvider provideTestGetLines
     */
    public function testGetLines($lines, $file)
    {
        $parser = new FileParser($file);
        $this->assertEquals($lines, $parser->getLines());
    }
    
    /**
     * Test cases for testGetLines()
     * 
     * @return array
     * @see testGetLines()
     */
    public function provideTestGetLines()
    {
        return array(
            array(2, __DIR__ . "/../data/fileParserTest/simple.txt"),
            array(10, __DIR__ . "/../data/fileParserTest/bb-excerpt.txt"),
        );
    }
    
    /**
     * Tests readLine()
     * 
     * @see FileParser::readLine()
     * @dataProvider provideTestReadLine
     */
    public function testReadLine($file, $line, $expectedData)
    {
        $parser = new FileParser($file);
        $data = $parser->readLine($line);
        $this->assertEquals($expectedData, $data);
    }
    
    /**
     * Test cases for testReadLine()
     * 
     * @return string[][]
     * @see testReadLine()
     */
    public function provideTestReadLine()
    {
        return array(
            array(
                __DIR__ . "/../data/fileParserTest/simple.txt",
                0,
                "12345\n"
            ),
            array(
                __DIR__ . "/../data/fileParserTest/simple.txt",
                1,
                "67890"
            )
        );
    }
    
    /**
     * Tests seekLine()
     * 
     * @see FileParser::seekLine()
     */
    public function testSeekLine()
    {
        $this->markTestIncomplete();
    }
}
