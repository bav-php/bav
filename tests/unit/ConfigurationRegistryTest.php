<?php

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";

/**
 * Tests ConfigurationRegistryTest
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @licends GPL
 * @see ConfigurationRegistry
 */
class ConfigurationRegistryTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * Disable update hook
     */
    protected function tearDown()
    {
        ConfigurationRegistry::getConfiguration()->setUpdatePlan(null);
    }

    /**
     * Tests the initialization with a configuration from the include path.
     * 
     * @see ConfigurationRegistry::classConstructor()
     */
    public function testInitWithIncludePathConfiguration()
    {
        set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . "/../data/");
        
        ConfigurationRegistry::classConstructor();
        $configuration = ConfigurationRegistry::getConfiguration();
        
        $this->assertInstanceOf("malkusch\bav\Configuration", $configuration);
        $this->assertEquals("test", $configuration->getTempDirectory());
        
        restore_include_path();
    }
    
    /**
     * Tests the initialization with the default configuration
     * 
     * @see ConfigurationRegistry::classConstructor()
     */
    public function testInitWithDefaultConfiguration()
    {
        ConfigurationRegistry::classConstructor();
        $configuration = ConfigurationRegistry::getConfiguration();
        
        $this->assertInstanceOf("malkusch\bav\DefaultConfiguration", $configuration);
        $this->assertNotEquals("test", $configuration->getTempDirectory());
    }
}
