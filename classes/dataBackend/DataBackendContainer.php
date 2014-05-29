<?php

namespace malkusch\bav;

/**
 * Container for BAV_DataBackend objects.
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @license GPL
 * @see BAV_DataBackend
 */ 
abstract class DataBackendContainer
{

    /**
     * @var BAV_DataBackend
     */
    private $backend;

    /**
     * Returns the unconfigured backend which is only created by calling the
     * constructor.
     *
     * @return BAV_DataBackend
     */
    abstract protected function makeDataBackend();

    /**
     * Builds a configured data backend.
     *
     * If configured this method would automatically install the backend. I.e. a first
     * call will take some amount of time.
     * 
     * @return BAV_DataBackend
     */
    private function buildDataBackend()
    {
        $configuration = ConfigurationRegistry::getConfiguration();

        $backend = $this->makeDataBackend();

        if ($configuration->isAutomaticInstallation() && ! $backend->isInstalled()) {
            $backend->install();

        }

        return $backend;
    }

    /**
     * Returns a configured data backend.
     *
     * If configured this method would automatically install the backend. I.e. a first
     * call will take some amount of time.
     *
     * @see Configuration::setAutomaticInstallation()
     * @see BAV_DataBackend::install()
     * @return BAV_DataBackend
     */
    public function getDataBackend()
    {
        if (is_null($this->backend)) {
            $this->backend = $this->buildDataBackend();

        }
        return $this->backend;
    }
}
