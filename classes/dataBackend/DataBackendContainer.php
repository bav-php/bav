<?php

namespace malkusch\bav;

/**
 * Container for DataBackend objects.
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 * @see DataBackend
 */
abstract class DataBackendContainer
{

    /**
     * @var string Name of the installation lock file
     */
    const INSTALL_LOCK = "bav_install.lock";

    /**
     * @var DataBackend
     */
    private $backend;

    /**
     * Returns the unconfigured backend which is only created by calling the
     * constructor.
     *
     * @return DataBackend
     */
    abstract protected function makeDataBackend();

    /**
     * Builds a configured data backend.
     *
     * If configured this method would automatically install the backend. I.e. a first
     * call will take some amount of time.
     * 
     * @return DataBackend
     * @throws DataBackendException
     */
    private function buildDataBackend()
    {
        $configuration = ConfigurationRegistry::getConfiguration();
        $backend = $this->makeDataBackend();

        // Installation
        if ($configuration->isAutomaticInstallation() && ! $backend->isInstalled()) {
            $lock = new Lock(self::INSTALL_LOCK);
            $lock->executeOnce(
                function () use ($backend) {
                    $backend->install();
                }
            );
        }

        // Update hook
        register_shutdown_function(array($this, "applyUpdatePlan"), $backend);

        return $backend;
    }

    /**
     * Shut down hook for applying the update plan.
     */
    public function applyUpdatePlan(DataBackend $backend)
    {
        $plan = ConfigurationRegistry::getConfiguration()->getUpdatePlan();
        if ($plan != null && $plan->isOutdated($backend)) {
            $plan->perform($backend);

        }
    }

    /**
     * Returns a configured data backend.
     *
     * If configured this method would automatically install and update the backend. I.e. 
     * some calls might take longer.
     *
     * @see Configuration::setAutomaticInstallation()
     * @see DataBackend::install()
     * @return DataBackend
     */
    public function getDataBackend()
    {
        if (is_null($this->backend)) {
            $this->backend = $this->buildDataBackend();

        }
        return $this->backend;
    }
}
