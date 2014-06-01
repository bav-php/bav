<?php

namespace malkusch\bav;

/**
 * Facade for bav's API.
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @license GPL
 */
class BAV
{

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var DataBackend
     */
    private $backend;

    /**
     * Inject the configuration.
     * 
     * If the $configuration is null the configuration from
     * ConfigurationRegistry::getConfiguration() will be used.
     * 
     * @see ConfigurationRegistry
     */
    public function __construct(Configuration $configuration = null)
    {
        if (is_null($configuration)) {
            $configuration = ConfigurationRegistry::getConfiguration();

        }
        $this->configuration = $configuration;

        $this->backend = $configuration->getDataBackendContainer()->getDataBackend();
    }

    /**
     * Returns the data backend
     *
     * @return DataBackend
     */
    public function getDataBackend()
    {
        return $this->backend;
    }

    /**
     * Updates bav with a new bundesbank file.
     *
     * You might consider enabling automatic update with setting 
     * AutomaticUpdatePlan as configuration.
     * 
     * @see AutomaticUpdatePlan
     * @see Configuration::setUpdatePlan()
     * @throws DataBackendException
     */
    public function update()
    {
        $this->getDataBackend()->update();
    }

    /**
     * Returns true if both the bank exists and the account is valid.
     *
     * @throws DataBackendException for some reason the validator might not be implemented
     * @param string $bankID
     * @param string $account
     * @see bankExists()
     * @see getBank()
     * @see Bank::isValid()
     * @return bool
     */
    public function isValidBankAccount($bankID, $account)
    {
        try {
            $bank = $this->getBank($bankID);
            return $bank->isValid($account);

        } catch (BankNotFoundException $e) {
            return false;

        }
    }

    /**
     * Returns true if a bank exists
     *
     * @throws DataBackendException
     * @param string $bankID
     * @return bool
     * @see DataBackend::bankExists()
     */
    public function bankExists($bankID)
    {
        return $this->backend->bankExists($bankID);
    }

    /**
     * Every bank has one main agency.
     * 
     * This agency is not included in getAgencies().
     *
     * @throws DataBackendException
     * @throws BankNotFoundException
     * @see Bank::getMainAgency()
     * @see getAgencies()
     * @return Agency
     */
    public function getMainAgency($bankID)
    {
        return $this->getBank($bankID)->getMainAgency();
    }

    /**
     * A bank may have more agencies.
     *
     * @throws DataBackendException
     * @throws BankNotFoundException
     * @return Agency[]
     */
    public function getAgencies($bankID)
    {
        return $this->getBank($bankID)->getAgencies();
    }
    
    /**
     * With this method you get the Bank objects for certain IDs. Note
     * that a call to this method with an identical id will return the same
     * objects.
     *
     * @throws BankNotFoundException
     * @throws DataBackendException
     * @param string $bankID
     * @return Bank
     * @see DataBackend::bankExists()
     */
    public function getBank($bankID)
    {
        return $this->backend->getBank($bankID);
    }
}
