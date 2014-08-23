<?php

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";

/**
 * Tests DataBackendContainer
 *
 * @license WTFPL
 * @author Markus Malkusch <markus@malkusch.de>
 */
class BackendContainerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Tests automatic installation.
     */
    public function testAutomaticInstallation()
    {
        ConfigurationRegistry::getConfiguration()->setUpdatePlan(null);
        $fileUtil = new FileUtil();
        $container = new FileDataBackendContainer(tempnam($fileUtil->getTempDirectory(), 'bavtest'));

        $this->assertTrue(ConfigurationRegistry::getConfiguration()->isAutomaticInstallation());
    
        $backend = $container->getDataBackend();
        $this->assertTrue($backend->isInstalled());

        $backend->uninstall();
    }

    /**
     * Tests automatic installation.
     */
    public function testAutomaticUpdate()
    {
        $updatePlan = new AutomaticUpdatePlan();
        $updatePlan->setNotice(false);
        ConfigurationRegistry::getConfiguration()->setUpdatePlan($updatePlan);

        $fileUtil = new FileUtil();
        $container = new FileDataBackendContainer(tempnam($fileUtil->getTempDirectory(), 'bavtest'));
        $backend = $container->getDataBackend();
            
        touch($backend->getFile(), strtotime("-1 year"));
        $this->assertTrue($updatePlan->isOutdated($backend));

        $container->applyUpdatePlan($backend);
        $this->assertFalse($updatePlan->isOutdated($backend));

        $backend->uninstall();
        ConfigurationRegistry::getConfiguration()->setUpdatePlan(null);
    }
}
