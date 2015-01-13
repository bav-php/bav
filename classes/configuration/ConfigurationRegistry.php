<?php

namespace malkusch\bav;

/**
 * Registry for the configuration
 *
 * BAV uses this static container for its runtime configuration. Per default the
 * registry is initialized with the {@link DefaultConfiguration}. You can
 * set your own configuration with {@link setConfiguration()} or preferably
 * by providing the file bav/configuration.php. This file should return a
 * {@link Configuration} object:
 *
 * <code>
 * <?php
 *
 * namespace malkusch\bav;
 *
 * $configuration = new DefaultConfiguration();
 *
 * $pdo = new \PDO("mysql:host=localhost;dbname=test");
 * $configuration->setDataBackendContainer(new PDODataBackendContainer($pdo));
 *
 * $configuration->setUpdatePlan(new AutomaticUpdatePlan());
 *
 * return $configuration;
 * </code>
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 * @see Configuration
 * @api
 */
class ConfigurationRegistry
{

    const BAV_PATH = "/../../configuration.php";
    
    const INCLUDE_PATH = "bav/configuration.php";

    /**
     * @var Configuration
     */
    private static $configuration;

    /**
     * locate a configuration or register the default configuration.
     *
     * You may define the file bav/configuration.php. This file should return
     * a Configuration object.
     *
     * @see DefaultConfiguration
     * @throws ConfigurationException
     */
    public static function classConstructor()
    {
        $locator = new ConfigurationLocator(array(
            __DIR__ . self::BAV_PATH,
            self::INCLUDE_PATH
        ));
        $configuration = $locator->locate();
        if ($configuration == null) {
            $configuration = new DefaultConfiguration();
            
        }
        self::setConfiguration($configuration);
    }

    /**
     * Register a configuration programmatically.
     *
     * Alternatively you can provide the file bav/configuration.php which
     * returns a {@link Configuration} object.
     */
    public static function setConfiguration(Configuration $configuration)
    {
        self::$configuration = $configuration;
    }

    /**
     * Returns the configuration
     *
     * @return Configuration
     */
    public static function getConfiguration()
    {
        return self::$configuration;
    }
}
