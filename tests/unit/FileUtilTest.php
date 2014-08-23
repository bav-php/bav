<?php

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";

/**
 * Test FileUtil
 *
 * @see FileUtil
 * @author Markus Malkusch <markus@malkusch.de>
 * @license WTFPL
 */
class FileUtilTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test only renaming in one filesystem.
     */
    public function testSafeRenameOneFS()
    {
        $before = __DIR__ . "/../data/testSafeRenameBefore";
        $after = __DIR__ . "/../data/testSafeRenameAfter";

        touch($before);

        $fileUtil = new FileUtil();
        $fileUtil->safeRename($before, $after);

        $this->assertFileExists($after);

        unlink($after);
    }

    /**
     * Tests a user configured temporary directory
     */
    public function testSetConfiguredTempDirectory()
    {
        $directory = "/root";
        
        $configuration = new DefaultConfiguration();
        $configuration->setTempDirectory($directory);
        
        $fileUtil = new FileUtil($configuration);
        $this->assertEquals($directory, $fileUtil->getTempDirectory());
    }

    /**
     * Tests getting a writable directory.
     */
    public function testGetWritableTempDirectory()
    {
        $fileUtil = new FileUtil();
        $this->assertTrue(is_writable($fileUtil->getTempDirectory()));
    }

    /**
     * Tests getting /tmp.
     * 
     * @requires OS Linux
     */
    public function testLinuxTempDirectory()
    {
        $fileUtil = new FileUtil();
        $this->assertEquals("/tmp", $fileUtil->getTempDirectory());
    }
}
