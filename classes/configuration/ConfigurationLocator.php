<?php

namespace malkusch\bav;

/**
 * Helper for locating a BAV configuration.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 * @see ConfigurationRegistry
 */
class ConfigurationLocator
{
    
    /**
     * @var string[]
     */
    private $paths = array();
    
    /**
     * Sets the paths where a location is expected.
     *
     * Those paths may be relative to the include_path.
     *
     * @param string[] $paths
     */
    public function __construct($paths = array())
    {
        $this->paths = $paths;
    }

    /**
     * Locates a configuration.
     *
     * @return Configuration|null
     * @throws ConfigurationException
     */
    public function locate()
    {
        foreach ($this->paths as $path) {
            $resolvedPath = stream_resolve_include_path($path);
            if (! $resolvedPath) {
                continue;
                
            }
            $configuration = require $resolvedPath;
            if (! $configuration instanceof Configuration) {
                throw new ConfigurationException(
                    "$resolvedPath must return a malkusch\\bav\\Configuration object."
                );

            }
            return $configuration;
            
        }
        return null;
    }
}
