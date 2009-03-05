#!/usr/bin/php
<?php
error_reporting(E_ALL);

require_once dirname(__FILE__)."/../classes/autoloader/BAV_Autoloader.php";
BAV_Autoloader::add('../classes/BAV.php');
BAV_Autoloader::add('../classes/dataBackend/BAV_DataBackend_PDO.php');
BAV_Autoloader::add('../classes/dataBackend/BAV_DataBackend_File.php');


/**
 * check all DataBackends
 *
 * Copyright (C) 2006  Markus Malkusch <bav@malkusch.de>
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
 * @copyright Copyright (C) 2006 Markus Malkusch
 */


class BAV_CheckBackends extends BAV {


    private
    /**
     * @var BAV_DataBackend_File
     */
    $referenceBackend;


    /**
     */
    public function __construct() {
        $this->referenceBackend = new BAV_DataBackend_File();
        
        $backEnds = array(
            new BAV_DataBackend_PDO(new PDO('mysql:host=localhost;dbname=test', 'test')),
            new BAV_DataBackend_File());
        
        foreach ($backEnds as $backEnd) {
            echo "\n\n*** ", get_class($backEnd), " ***\n";
            $this->fetchAll($backEnd);
            $this->compare($backEnd);
            
        }
    }
    
    
    private function fetchAll(BAV_DataBackend $backend) {
        $differentObjects = array();
        foreach ($this->referenceBackend->getAllBanks() as $refBank) {
            $fetchBankA = $backend->getBank($refBank->getBankID());
            $fetchBankB = $backend->getBank($refBank->getBankID());
            
            if ($fetchBankA !== $fetchBankB) {
                $differentObjects[] = array($fetchBankA, $fetchBankB);
                
            }
        }
        
        echo "different objects:\n";
        foreach ($differentObjects as $objects) {
            echo "**\n",
                 "* Bank: {$objects[0]->getBankID()} !== {$objects[1]->getBankID()}\n",
                 "**\n\n";
        
        }
    }
    
    
    private function compare(BAV_DataBackend $backend) {
        $referenceBanks = $this->referenceBackend->getAllBanks();
        $referenceTable = array();
        foreach ($referenceBanks as $bank) {
            if (isset($referenceTable[$bank->getBankID()])) {
                throw new LogicException("Reference data backend should be consistent.");
            
            }
            $referenceTable[$bank->getBankID()] = $bank;
        
        }
        $checkBanks = $backend->getAllBanks();
        
        $result         = array();
        $unequalBanks   = array();
        foreach ($checkBanks as $bank) {
            if (! isset($result[$bank->getBankID()])) {
                $result[$bank->getBankID()] = 0;
            
            }
            if (! isset($referenceTable[$bank->getBankID()])) {
                continue;
                
            }
            
            $isEqual = true;
            
            $result[$bank->getBankID()]++;
            
            $refBank = $referenceTable[$bank->getBankID()];
            if ($bank->getValidationType() != $refBank->getValidationType()
            || ! $this->isEqualAgency($bank->getMainAgency(), $refBank->getMainAgency())) {
                $isEqual = false;
                
            }
            
            $refAgencies = array();
            foreach($refBank->getAgencies() as $agency) {
                if (isset($refAgencies[$agency->getID()])) {
                    throw new LogicException("Reference data backend should be consistent.");
                    
                }
                $refAgencies[$agency->getID()] = $agency;
            }
            foreach ($bank->getAgencies() as $agency) {
                if (! isset($refAgencies[$agency->getID()])
                || ! $this->isEqualAgency($agency, $refAgencies[$agency->getID()])) {
                    $isEqual = false;
                    break;
                
                }
                unset($refAgencies[$agency->getID()]);
                
            }
            if (! empty($refAgencies)) {
                $isEqual = false;
            
            }
            
            
            if (! $isEqual) {
                $unequalBanks[] = $bank;
                
            }
        }
        
        echo "wrong bank count:\n";
        foreach ($result as $bankID => $count) {
            if ($count === 1) {
                continue;
            
            }
            echo "**\n",
                 "*  Bank: $bankID\n",
                 "* Count: $count\n",
                 "**\n\n";
        
        }
        
        echo "unequal banks:\n";
        foreach ($unequalBanks as $bank) {
            echo "**\n",
                 "* Bank: {$bank->getBankID()}\n",
                 "**\n\n";
        }
    }
    
    
    private function dumpAgency(BAV_Agency $agency) {
        echo "\n\n*********\n";
        var_dump($agency->getBank()->getBankID());
        var_dump($agency->getID());
        var_dump($agency->getPostcode());
        var_dump($agency->getCity());
        var_dump($agency->getName());
        var_dump($agency->getShortTerm());
        var_dump($agency->hasPAN());
        var_dump($agency->hasBIC());
        if ($agency->hasPAN()) {
            var_dump($agency->getPAN());
            
        }
        if ($agency->hasBIC()) {
            var_dump($agency->getBIC());
            
        }
    }
    
    
    private function isEqualAgency(BAV_Agency $a, BAV_Agency $b) {
        return $a->getBank()->getBankID() === $b->getBank()->getBankID()
            && $a->getID() === $b->getID()
            && $a->getPostcode() === $b->getPostcode()
            && $a->getCity() === $b->getCity()
            && $a->getName() === $b->getName()
            && $a->getShortTerm() === $b->getShortTerm()
            && $a->hasPAN() === $b->hasPAN()
            && $a->hasBIC() === $b->hasBIC()
            && ($a->hasPAN() ? $a->getPAN() === $b->getPAN() : true)
            && ($a->hasBIC() ? $a->getBIC() === $b->getBIC() : true);
    }


}
new BAV_CheckBackends();


?>