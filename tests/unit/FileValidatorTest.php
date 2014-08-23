<?php

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";

/**
 * Tests FileValidator.
 *
 * @license WTFPL
 * @author Markus Malkusch <markus@malkusch.de>
 * @see BAV
 */
class FileValidatorTest extends \PHPUnit_Framework_TestCase
{

    public function testValidate()
    {
        $backend = new FileDataBackend();
        $file = $backend->getFile();

        $validator = new FileValidator();
        $validator->validate($file);
    }

    /**
     * @expectedException malkusch\bav\InvalidFilesizeException
     */
    public function testInvalidFileSize()
    {
        $validator = new FileValidator();
        $validator->validate(__FILE__);
    }

    /**
     * @expectedException malkusch\bav\InvalidLineLengthException
     */
    public function testInvalidLineLength()
    {
        $backend = new FileDataBackend();
        $file = $backend->getFile();

        $invalidFile = __DIR__ . "/../data/invalidLength.txt";
        copy($file, $invalidFile);

        $fp = fopen($invalidFile, "c");
        fputs($fp, "invalid line\n");

        $validator = new FileValidator();
        $validator->validate($invalidFile);
    }

    /**
     * @expectedException malkusch\bav\InvalidLineLengthException
     */
    public function testNotConstantLineLength()
    {
        $backend = new FileDataBackend();
        $file = $backend->getFile();

        $invalidFile = __DIR__ . "/../data/notConstantLength.txt";
        copy($file, $invalidFile);

        $fp = fopen($invalidFile, "a");
        fputs($fp, "X\n");

        $validator = new FileValidator();
        $validator->validate($invalidFile);
    }

    /**
     * @expectedException malkusch\bav\FieldException
     */
    public function testInvalidFirstLineContent()
    {
        $backend = new FileDataBackend();
        $file = $backend->getFile();

        $invalidFile = __DIR__ . "/../data/invalidFirstLineContent.txt";
        copy($file, $invalidFile);

        $fp = fopen($invalidFile, "c");
        fputs($fp, "XXX");

        $validator = new FileValidator();
        $validator->validate($invalidFile);
    }
}
