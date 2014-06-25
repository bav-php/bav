<?php

namespace malkusch\bav;

/**
 * Facade for BAV's API.
 * 
 * This class provides methods for validation of German bank accounts.
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
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
     * @var ContextValidation
     */
    private $contextValidation;

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
        
        $this->contextValidation = new ContextValidation($this->backend);
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
     * @see isValidBank()
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
     * Returns true if the account is valid for the current context.
     * 
     * You have to have called isValidBank() before! If the current context
     * is no valid bank every account will validate to true.
     *
     * @param string $account
     * @see isValidBank()
     * @see ContextValidation::isValidAccount()
     * @throws InvalidContextException isValidBank() was not called before.
     * @return bool
     */
    public function isValidAccount($account)
    {
        return $this->contextValidation->isValidAccount($account);
    }

    /**
     * Returns true if a bank exists
     *
     * @throws DataBackendException
     * @param string $bankID
     * @return bool
     * @see ContextValidation::isValidBank()
     */
    public function isValidBank($bankID)
    {
        return $this->contextValidation->isValidBank($bankID);
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
     * The main agency is not included in this list.
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
     * @see DataBackend::isValidBank()
     */
    public function getBank($bankID)
    {
        return $this->backend->getBank($bankID);
    }

    /**
     * Returns bank agencies for a given BIC.
     *
     * @param string $bic BIC
     * @return Agency[]
     */
    public function getBICAgencies($bic)
    {
        return $this->backend->getBICAgencies(BICUtil::normalize($bic));
    }

    /**
     * Returns if a bic is valid.
     *
     * @param string $bic BIC
     * @return bool
     */
    public function isValidBIC($bic)
    {
        return $this->backend->isValidBIC(BICUtil::normalize($bic));
    }
    
    /**
     * Returns the third call back parameter for filter_var() for validating
     * a bank.
     * 
     * filter_var($bankID, FILTER_CALLBACK, $bav->getValidBankFilterCallback());
     * 
     * @return array
     * @see isValidBank()
     * @see filter_var()
     */
    public function getValidBankFilterCallback()
    {
        return $this->contextValidation->getValidBankFilterCallback();
    }
    
    /**
     * Returns the third call back parameter for filter_var() for validating
     * a bank account.
     * 
     * filter_var($account, FILTER_CALLBACK, $bav->getValidBankFilterCallback());
     * 
     * @return array
     * @see isValidAccount()
     * @see filter_var()
     */
    public function getValidAccountFilterCallback()
    {
        return $this->contextValidation->getValidAccountFilterCallback();
    }
}
