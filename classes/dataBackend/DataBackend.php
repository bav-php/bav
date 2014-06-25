<?php

namespace malkusch\bav;

/**
 * The DataBackend is an abstract class which is responsable for the
 * datastructure of the banks. If you want to use a custom datastructure
 * you have to implement these methods:
 *
 * install(), update(), uninstall(), getNewBank(), getAllBanks(), getMainAgency(),
 * getAgenciesForBank()
 *
 * When you use this class you should create only one object. The DataBackend
 * is designed to keep all created Bank objects in an array. So you won't get
 * copies of identical Bank objects. That means if you call two times the
 * getBank() methode with the same id, you will receive each time the same object.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 * @see DataBackendContainer
 */
abstract class DataBackend
{

    /**
     * @var array All created Bank objects
     */
    protected $instances = array();

    /**
     * You have to call this method to synchronize your datastructure to the
     * data of the Bundesbank.
     *
     * @throws DataBackendException
     */
    abstract public function update();

    /**
     * Removes the databackend physically.
     *
     * @throws DataBackendException
     */
    abstract public function uninstall();

    /**
     * Installs the databackend. An implementation of install() should also
     * call update() to synchronize to the Bundebank.
     *
     * @throws DataBackendException
     */
    abstract public function install();

    /**
     * Returns true if the backend was installed.
     *
     * @return bool
     * @throws DataBackendException
     */
    abstract public function isInstalled();

    /**
     * Returns the timestamp of the last update.
     *
     * @return int timestamp
     * @throws DataBackendException
     */
    abstract public function getLastUpdate();

    /**
     * With this method you get the Bank objects for certain IDs. Note
     * that a call to this method with an identical id will return the same
     * objects.
     *
     * @throws BankNotFoundException
     * @throws DataBackendException
     * @param string
     * @return Bank
     */
    public function getBank($bankID)
    {
        if (! isset($this->instances[$bankID])) {
            $this->instances[$bankID] = $this->getNewBank($bankID);

        }
        return $this->instances[$bankID];
    }

    /**
     * Return true if a bank exists.
     *
     * @throws DataBackendException
     * @param String $bankID
     * @deprecated 1.0.0
     * @see isValidBank()
     * @return bool
     */
    public function bankExists($bankID)
    {
        trigger_error("bankExists() is deprecated, use isValidBank().", E_USER_DEPRECATED);
        return $this->isValidBank($bankID);
    }

    /**
     * Return true if a bank exists.
     *
     * @throws DataBackendException
     * @param String $bankID
     * @return bool
     */
    public function isValidBank($bankID)
    {
        try {
            $this->getBank($bankID);
            return true;

        } catch (BankNotFoundException $e) {
            return false;

        }
    }

    /**
     * Returns an array with all banks. If you implement this method you should
     * also take care that $instances is used an will be filled correctly in order
     * to garantee that there will never exist two identical objects.
     *
     * @throws DataBackendException
     * @return Bank[]
     */
    abstract public function getAllBanks();

    /**
     * This method will be called by getBank() if getBank() thinks it is necessary to
     * create a new object. You have to return the new object and have not to take care
     * about $instances. getBank() cares about $instances. Throw a BankNotFoundException
     * if the bank does not exist.
     *
     * @throws DataBackendException
     * @throws BankNotFoundException if the bank does not exist
     * @param string
     * @return Bank
     */
    abstract protected function getNewBank($bankID);

    /**
     * If you implement this method you should return the appropriate Agency object. This
     * method is called by Bank->getMainAgency(), if the Bank object doesn't know its
     * main agency.
     *
     * @throws DataBackendException
     * @return Agency
     * @see Bank::getMainAgency()
     * @internal YOU SHOULD NOT CALL THIS METHOD! Use Bank->getMainAgency()
     */
    abstract public function getMainAgency(Bank $bank);

    /**
     * If you implement this method you should return an array with the appropriate Agency
     * objects. This method is called by Bank->getAgencies(), if the Bank object doesn't
     * know its agencies. A bank may have no agencies and will return an empty array.
     *
     * @throws DataBackendException
     * @see Bank::getAgencies()
     * @return Agency[]
     * @internal YOU SHOULD NOT CALL THIS METHOD! Use Bank->getMainAgency()
     */
    abstract public function getAgenciesForBank(Bank $bank);

    /**
     * Returns bank agencies for a given BIC.
     *
     * @param string $bic BIC
     * @return Agency[]
     */
    abstract public function getBICAgencies($bic);

    /**
     * Returns if a bic is valid.
     *
     * @param string $bic BIC
     * @return bool
     */
    public function isValidBIC($bic)
    {
        $agencies = $this->getBICAgencies($bic);
        return ! empty($agencies);
    }
    
    /**
     * Frees memory of cached instances
     */
    public function free()
    {
        $this->instances = array();
    }
}
