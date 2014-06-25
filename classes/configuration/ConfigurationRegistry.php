<?php

namespace malkusch\bav;

/**
 * Registry for the configuration
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 * @see Configuration
 */
class ConfigurationRegistry
{

    const CONFIGURATION_PATH = "/../../configuration.php";

    /**
     * @var Configuration
     */
    private static $configuration;

    /**
     * locate a configuration or register the default configuration.
     * 
     * You may define the file bav/configuration.php. This file should return
     * Configuration object.
     * 
     * @see DefaultConfiguration
     */
    public static function classConstructor()
    {
        self::setConfiguration(new DefaultConfiguration());

        $file = __DIR__ . self::CONFIGURATION_PATH;
        if (file_exists($file)) {
            $configuration = require_once $file;
            if (! $configuration instanceof Configuration) {
                throw new ConfigurationException("$file must return a Configuration object.");

            }
            self::setConfiguration($configuration);

        }
    }

    /**
     * Register a configuration.
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
