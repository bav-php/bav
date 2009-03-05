#!/usr/bin/php
<?php
error_reporting(E_ALL);

require_once dirname(__FILE__)."/../classes/autoloader/BAV_Autoloader.php";
BAV_Autoloader::add('../classes/BAV.php');
BAV_Autoloader::add('../classes/dataBackend/BAV_DataBackend_PDO.php');


/**
 * check BAV_DataBackend_PDO->getAgencies($sql)
 *
 * Copyright (C) 2007  Markus Malkusch <bav@malkusch.de>
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
 * @package scripts
 * @subpackage test
 * @author Markus Malkusch <bav@malkusch.de>
 * @copyright Copyright (C) 2007 Markus Malkusch
 */


class BAV_CheckAgencyQuery extends BAV {


    /**
     * @var BAV_DataBackend_PDO
     */
    private $backend;


    /**
     */
    public function __construct() {
        $this->backend = new BAV_DataBackend_PDO(new PDO('mysql:host=localhost;dbname=test', 'test'));
        $this->testNoID();
        
        $this->backend = new BAV_DataBackend_PDO(new PDO('mysql:host=localhost;dbname=test', 'test'));
        $this->testNoBank();
        
        $this->backend = new BAV_DataBackend_PDO(new PDO('mysql:host=localhost;dbname=test', 'test'));
        $this->testOnlyID();
        
        $this->backend = new BAV_DataBackend_PDO(new PDO('mysql:host=localhost;dbname=test', 'test'));
        $this->testIDAndBank();
    }
    
    
    public function testOnlyID() {
        $agencies = $this->backend->getAgencies(
            'SELECT id FROM bav_agency LIMIT 100');
        $this->verifyAgencies($agencies, 100);
    }
    
    
    public function testIDAndBank() {
        $agencies = $this->backend->getAgencies(
            'SELECT id, bank FROM bav_agency LIMIT 100');
        $this->verifyAgencies($agencies, 100);
    }
    
    
    public function testNoBank() {
        $agencies = $this->backend->getAgencies(
            'SELECT id, name, postcode, city, shortTerm, pan, bic FROM bav_agency LIMIT 100');
        $this->verifyAgencies($agencies, 100);
    }
    
    
    public function testNoID() {
        try {
            $result = $this->backend->getAgencies(
                'SELECT name, postcode, city, shortTerm, pan, bic, bank FROM bav_agency LIMIT 1');
            throw new LogicException();

        } catch (BAV_DataBackendException_IO_MissingAttributes $e) {
            // OK
        }
    }
    
    
    private function verifyAgencies(Array $agencies, $count) {
        if (count($agencies) !== $count) {
            throw new LogicException("Too less agencies (".count($agencies)." vs. $count).");

        }
        foreach ($agencies as $agency) {
            if ($agency->isMainAgency()) {
                if ($agency->getBank()->getMainAgency() !== $agency) {
                    throw new LogicException("Inconsistent Main Agency");

                }
                if ($agency->getBank() !== $agency->getBank()->getMainAgency()->getBank()) {
                    throw new LogicException("Inconsistent Bank");

                }
            } else {
                $includedCount = 0;
                foreach ($agency->getBank()->getAgencies() as $banksAgency) {
                    if ($banksAgency->getBank() !== $agency->getBank()) {
                        throw new LogicException("Inconsistent bank");

                    }
                    if ($banksAgency === $agency) {
                        $includedCount++;

                    }
                }
                if ($includedCount !== 1) {
                    throw new LogicException("Inconsistent agency");

                }
            }
        }
    }


}
new BAV_CheckAgencyQuery();


?>