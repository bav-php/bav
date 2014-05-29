<?php

namespace malkusch\bav;

require_once __DIR__ . "/../autoloader/autoloader.php";

/**
 * Tests DataBackendContainer
 *
 * @license GPL
 * @author Markus Malkusch <markus@malkusch.de>
 */
class BackendContainerTest extends \PHPUnit_Framework_TestCase
{

    public function provideContainers()
    {
        $fileContainer = new FileDataBackendContainer(tempnam(\BAV_DataBackend_File::getTempdir(), 'bavtest'));

        $pdoContainer = new PDODataBackendContainer(
            new \PDO('mysql:host=localhost;dbname=test', 'test'),
            'bavtest_'
        );

        return array(
            array($fileContainer),
            array($pdoContainer)
        );
    }

    /**
     * Tests automatic installation.
     *
     * @dataProvider provideContainers
     */
    public function testAutomaticInstallation(DataBackendContainer $container)
    {
        $this->assertTrue(ConfigurationRegistry::getConfiguration()->isAutomaticInstallation());
    
        $backend = $container->getDataBackend();
        $this->assertTrue($backend->isInstalled());

        $backend->uninstall();
    }

}
