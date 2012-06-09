<?php





/**
 * A bank can validate a bank account (BAV_Bank->isValid(String $account)) and
 * has a bank ID, a main agency (BAV_Bank->getMainAgency()) and optionally some
 * more agencies (BAV_Bank->getAgencies()). Note that the main agency is not
 * included in the array BAV_Bank->getAgencies() (which could even be empty).
 *
 * You should not create BAV_Bank objects directly. Use a BAV_DataBackend object
 * to get a BAV_Bank object.
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
 * @copyright Copyright (C) 2006 Markus Malkusch
 */
class BAV_Bank extends BAV {


    private
    /**
     * @var string
     */
    $bankID = '',
    /**
     * @var string
     */
    $validationType = '',
    /**
     * @var BAV_Validator
     */
    $validator,
    /**
     * @var BAV_DataBackend
     */
    $dataBackend,
    /**
     * @var BAV_Agency
     */
    $mainAgency,
    /**
     * @var Array
     */
    $agencies;


    /**
     * Do not even think to use new BAV_Bank()!
     * Go and use BAV_DataBackend->getBank($bankID).
     *
     * @param string $bankID
     * @param string $validationType
     */
    public function __construct(BAV_DataBackend $dataBackend, $bankID, $validationType) {
        $this->dataBackend = $dataBackend;
        $this->bankID = $bankID;
        $this->validationType = $validationType;
    }
    /**
     * @return string
     */
    public function getValidationType() {
        return $this->validationType;
    }
    /**
     * @return string
     */
    public function getBankID() {
        return (string) $this->bankID;
    }
    /**
     * Every bank has one main agency. This agency is not included in getAgencies().
     *
     * @throws BAV_DataBackendException
     * @return BAV_Agency
     */
    public function getMainAgency() {
        if (is_null($this->mainAgency)) {
            $this->mainAgency = $this->dataBackend->_getMainAgency($this);
        
        }
        return $this->mainAgency;
    }
    /**
     * A bank may have more agencies.
     *
     * @throws BAV_DataBackendException
     * @return array
     */
    public function getAgencies() {
        if (is_null($this->agencies)) {
            $this->agencies = $this->dataBackend->_getAgencies($this);
        
        }
        return $this->agencies;
    }
    /**
     * Use this method to check your bank account.
     *
     * @throws BAV_ValidatorException_NotExists for some reason the validator might not be implemented
     * @param string $account
     * @return bool
     */
    public function isValid($account) {
        return $this->getValidator()->isValid($account);
    }
    /**
     * @throws BAV_ValidatorException_NotExists
     * @return BAV_Validator
     */
    public function getValidator() {
        if (is_null($this->validator)) {
            $this->validator = BAV_Validator::getInstance($this);
        
        }
        return $this->validator;
    }


}


?>