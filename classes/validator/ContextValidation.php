<?php

namespace malkusch\bav;

/**
 * Context validation
 * 
 * You have to validate first a bank to set a context. Then you can validate
 * an account.
 * 
 * This class provides callbacks for filter validation.
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 */
class ContextValidation
{
    
    /**
     * @var bool
     */
    private $initialized = false;
    
    /**
     * @var DataBackend
     */
    private $backend;
    
    /**
     * @var Bank
     */
    private $bank;
    
    /**
     * Injects the backend.
     */
    public function __construct(DataBackend $backend)
    {
        $this->backend = $backend;
    }
    
    /**
     * Returns true if a bank exists.
     * 
     * This method sets the bank context and should be called first.
     *
     * @throws DataBackendException
     * @param string $bankID
     * @return bool
     * @see DataBackend::isValidBank()
     */
    public function isValidBank($bankID)
    {
        try {
            $this->initialized = true;
            $this->bank = $this->backend->getBank($bankID);
            return true;
            
        } catch (BankNotFoundException $e) {
            $this->bank = null;
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
     * @see Bank::isValid()
     * @throws InvalidContextException isValidBank() was not called before.
     * @return bool
     */
    public function isValidAccount($account)
    {
        if (! $this->initialized) {
            throw new InvalidContextException("You have to call isValidBank() before.");
            
        }
        
        // No valid bank makes every account valid
        if ($this->bank == null) {
            return true;
            
        }
        
        return $this->bank->isValid($account);
    }
    
    /**
     * Returns the third call back parameter for filter_var() for validating
     * a bank.
     * 
     * filter_var($bankID, FILTER_CALLBACK, $validation->getValidBankFilterCallback());
     * 
     * @return array
     * @see isValidBank()
     * @see filter_var()
     */
    public function getValidBankFilterCallback()
    {
        $validation = $this;
        return array("options" => function ($bankID) use ($validation) {
            return $validation->isValidBank($bankID);
        });
    }
    
    /**
     * Returns the third call back parameter for filter_var() for validating
     * a bank account.
     * 
     * filter_var($account, FILTER_CALLBACK, $validation->getValidBankFilterCallback());
     * 
     * @return array
     * @see isValidAccount()
     * @see filter_var()
     */
    public function getValidAccountFilterCallback()
    {
        $validation = $this;
        return array("options" => function ($account) use ($validation) {
            return $validation->isValidAccount($account);
        });
    }
}
