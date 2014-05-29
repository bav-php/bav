<?php

/**
 * The BAV_DataBackend is an abstract class which is responsable for the
 * datastructure of the banks. If you want to use a custom datastructure
 * you have to implement these methods:
 *
 * install(), update(), uninstall(), getNewBank(), getAllBanks(), getMainAgency(),
 * getAgenciesForBank()
 *
 * When you use this class you should create only one object. The BAV_DataBackend
 * is designed to keep all created BAV_Bank objects in an array. So you won't get
 * copies of identical BAV_Bank objects. That means if you call two times the
 * getBank() methode with the same id, you will receive each time the same object.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @license GPL
 * @see DataBackendFactory
 */
abstract class BAV_DataBackend extends BAV
{

    /**
     * @var array All created BAV_Bank objects
     */
    protected $instances = array();

    /**
     * You have to call this method to synchronize your datastructure to the
     * data of the Bundesbank.
     *
     * @throws BAV_DataBackendException
     */
    abstract public function update();

    /**
     * Removes the databackend physically.
     *
     * @throws BAV_DataBackendException
     */
    abstract public function uninstall();

    /**
     * Installs the databackend. An implementation of install() should also
     * call update() to synchronize to the Bundebank.
     *
     * @throws BAV_DataBackendException
     */
    abstract public function install();

    /**
     * Returns true if the backend was installed.
     *
     * @return bool
     * @throws BAV_DataBackendException
     */
    abstract public function isInstalled();

    /**
     * Returns the timestamp of the last update.
     *
     * @return int timestamp
     * @throws BAV_DataBackendException
     */
    abstract public function getLastUpdate();

    /**
     * With this method you get the BAV_Bank objects for certain IDs. Note
     * that a call to this method with an identical id will return the same
     * objects.
     *
     * @throws BAV_DataBackendException_BankNotFound
     * @throws BAV_DataBackendException
     * @param string
     * @return BAV_Bank
     */
    public function getBank($bankID)
    {
        if (! isset($this->instances[$bankID])) {
            $this->instances[$bankID] = $this->getNewBank($bankID);

        }
        return $this->instances[$bankID];
    }

    /**
     * Perhaps you just want to know if a bank exists.
     *
     * @throws BAV_DataBackendException
     * @param String $bankID
     * @return bool
     */
    public function bankExists($bankID)
    {
        try {
            $this->getBank($bankID);
            return true;

        } catch (BAV_DataBackendException_BankNotFound $e) {
            return false;

        }
    }

    /**
     * Returns an array with all banks. If you implement this method you should
     * also take care that $instances is used an will be filled correctly in order
     * to garantee that there will never exist two identical objects.
     *
     * @throws BAV_DataBackendException
     * @return array
     */
    abstract public function getAllBanks();

    /**
     * This method will be called by getBank() if getBank() thinks it is necessary to
     * create a new object. You have to return the new object and have not to take care
     * about $instances. getBank() cares about $instances. Throw a BAV_DataBackendException_BankNotFound
     * if the bank does not exist.
     *
     * @throws BAV_DataBackendException
     * @throws BAV_DataBackendException_BankNotFound if the bank does not exist
     * @param string
     * @return BAV_Bank
     */
    abstract protected function getNewBank($bankID);

    /**
     * If you implement this method you should return the appropriate BAV_Agency object. This
     * method is called by BAV_Bank->getMainAgency(), if the BAV_Bank object doesn't know its
     * main agency.
     *
     * @todo remove _getMainAgency() and make getMainAgency() abstract.
     * @throws BAV_DataBackendException
     * @return BAV_Agency
     * @see BAV_Bank::getMainAgency()
     * @internal YOU SHOULD NOT CALL THIS METHOD! Use BAV_Bank->getMainAgency()
     */
    public function getMainAgency(BAV_Bank $bank)
    {
        trigger_error(
            "_getMainAgency() was renamed into getMainAgency().",
            E_USER_DEPRECATED
        );
        return $this->_getMainAgency($bank);
    }

    /**
     * @deprecated 0.28
     * @see getMainAgency()
     */
    public function _getMainAgency(BAV_Bank $bank)
    {
        throw new BadMethodCallException("You have to override getMainAgency()");
    }

    /**
     * If you implement this method you should return an array with the appropriate BAV_Agency
     * objects. This method is called by BAV_Bank->getAgencies(), if the BAV_Bank object doesn't
     * know its agencies. A bank may have no agencies and will return an empty array.
     *
     * @todo remove _getAgencies() and make getAgenciesForBank() abstract.
     * @throws BAV_DataBackendException
     * @see BAV_Bank::getAgencies()
     * @return array
     * @internal YOU SHOULD NOT CALL THIS METHOD! Use BAV_Bank->getMainAgency()
     */
    public function getAgenciesForBank(BAV_Bank $bank)
    {
        trigger_error(
            "_getAgencies() was renamed into getAgenciesForBank().",
            E_USER_DEPRECATED
        );
        return $this->_getAgencies($bank);
    }
    
    /**
     * @deprecated 0.28
     * @see getAgenciesForBank()
     */
    public function _getAgencies(BAV_Bank $bank)
    {
        throw new BadMethodCallException("You have to override getAgenciesForBank()");
    }
}
