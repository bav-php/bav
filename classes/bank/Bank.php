<?php

namespace malkusch\bav;

/**
 * A bank can validate a bank account (Bank->isValid(String $account)) and
 * has a bank ID, a main agency (Bank->getMainAgency()) and optionally some
 * more agencies (Bank->getAgencies()). Note that the main agency is not
 * included in the array Bank->getAgencies() (which could even be empty).
 *
 * You should not create Bank objects directly. Use a DataBackend object
 * to get a Bank object.
 *
 *
 * Copyright (C) 2006  Markus Malkusch <markus@malkusch.de>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *
 * @package classes
 * @subpackage bank
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @copyright Copyright (C) 2006 Markus Malkusch
 */
class Bank
{

    /**
     * @var string
     */
    private $bankID = '';

    /**
     * @var string
     */
    private $validationType = '';

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var DataBackend
     */
    private $dataBackend;

    /**
     * @var Agency
     */
    private $mainAgency;

    /**
     * @var Array
     */
    private $agencies;

    /**
     * Do not even think to use new Bank()!
     * Go and use DataBackend->getBank($bankID).
     *
     * @param string $bankID
     * @param string $validationType
     */
    public function __construct(DataBackend $dataBackend, $bankID, $validationType)
    {
        $this->dataBackend = $dataBackend;
        $this->bankID = $bankID;
        $this->validationType = $validationType;
    }

    /**
     * @return string
     */
    public function getValidationType()
    {
        return $this->validationType;
    }

    /**
     * @return string
     */
    public function getBankID()
    {
        return (string) $this->bankID;
    }

    /**
     * Every bank has one main agency. This agency is not included in getAgencies().
     *
     * @throws DataBackendException
     * @return Agency
     */
    public function getMainAgency()
    {
        if (is_null($this->mainAgency)) {
            $this->mainAgency = $this->dataBackend->getMainAgency($this);

        }
        return $this->mainAgency;
    }

    /**
     * A bank may have more agencies.
     *
     * @throws DataBackendException
     * @return Agency[]
     */
    public function getAgencies()
    {
        if (is_null($this->agencies)) {
            $this->agencies = $this->dataBackend->getAgenciesForBank($this);

        }
        return $this->agencies;
    }
    
    /**
     * @internal
     */
    public function setDataBackend(DataBackend $backend)
    {
        $this->dataBackend = $backend;
    }

    /**
     * Use this method to check your bank account.
     *
     * @throws ValidatorNotExistsException for some reason the validator might not be implemented
     * @param string $account
     * @return bool
     */
    public function isValid($account)
    {
        return $this->getValidator()->isValid($account);
    }

    /**
     * @throws ValidatorNotExistsException
     * @return Validator
     */
    public function getValidator()
    {
        if (is_null($this->validator)) {
            $this->validator = Validator::getInstance($this);

        }
        return $this->validator;
    }
}
