#!/usr/bin/php
<?php
require_once dirname(__FILE__)."/../classes/autoloader/BAV_Autoloader.php";
BAV_Autoloader::add('../classes/BAV.php');
BAV_Autoloader::add('../classes/verify/BAV_VerifyImport.php');
BAV_Autoloader::add('../classes/dataBackend/BAV_DataBackend_File.php');
BAV_Autoloader::add('../classes/dataBackend/exception/BAV_DataBackendException_BankNotFound.php');


/**
 * A test for BAV_VerifyImport.
 *
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
 * @author Markus Malkusch <bav@malkusch.de>
 * @copyright Copyright (C) 2006 Markus Malkusch
 * @see BAV_VerifyImport
 */


class BAV_CheckVerifyImport extends BAV {


    private
    /**
     * @var array
     */
    $validationMap = array(),
    /**
     * @var array
     */
    $verifyArray = array(),
    /**
     * @var BAV_DataBackend_File
     */
    $databack;
    
    
    public function __construct() {
        $this->databack    = new BAV_DataBackend_File();
        $this->verifyArray = parse_ini_file(dirname(__FILE__).'/../data/verify.ini', true);
        
        if (! $this->verifyArray) {
            throw new RuntimeException("Could not parse verify.ini");
        
        }
        
        foreach ($this->databack->getAllBanks() as $bank) {
            $this->validationMap[$bank->getValidationType()] = $bank;
        
        }        
        
        $this->testSequentialImport();
        $this->testFileImport();
    }
    /**
     * @return BAV_VerifyImport
     */
    private function createImporter() {
        return new BAV_VerifyImport($this->databack);
    }
    
    
    private function testFileImport() {
        $importer = $this->createImporter();
        $importer->importVerifyFile();
        $this->compareImporter($importer);
    }
    
    
    private function testSequentialImport() {
        $importer       = $this->createImporter();
        $notSupported   = array();
        foreach ($this->verifyArray as $expect => $array) {
            foreach ($array as $type => $accounts) {
                try {
                    $type     = (strlen($type) < 2 ? '0' : '').$type;
                    $accounts = preg_split('~\D+~', $accounts);
                    $bankID   = strlen($type) === 2 ? $this->getBank($type)->getBankID() : $type;
                    foreach ($accounts as $account) {
                        $importer->import($bankID, $account, $expect === 'valid' ? true : false);
    
                    }
                    
                } catch (BAV_DataBackendException_BankNotFound $e) {
                    $notSupported[] = $type;
                
                }
                
            }
            
        }
        $this->compareImporter($importer, $notSupported);
    }
    
    
    private function compareImporter(BAV_VerifyImport $importer, Array $notSupported = array()) {
        $file = tempnam('/tmp', 'BAV');
        if (! $file) {
            throw new RuntimeException("Could not create temporary file.");
        
        }
        $importer->save($file);
        $checkArray = parse_ini_file($file, true);
        unlink($file);
        if (! $checkArray) {
            throw new RuntimeException("Could not parse temporary file $file.");
        
        }
        foreach ($this->verifyArray as $expect => $array) {
            foreach ($array as $type => $accounts) {
                if (array_search($type, $notSupported) !== false || preg_replace('~\D~', '', $checkArray[$expect][$type]) === preg_replace('~\D~', '', $accounts)) {
                    unset($checkArray[$expect][$type]);
                
                }else {
                    throw new LogicException(
                        "$type is not equal!\n".
                        "Should be: '$accounts'\n".
                        "       Is: '{$checkArray[$expect][$type]}'");
                
                }
                
            }
            
        }
        if (! (empty($checkArray['valid']) && empty($checkArray['invalid']))) {
            throw new LogicException("checkArray is not empty.");
        
        }
    }
    
    
    /**
     * @param String $validationType
     * @return BAV_Bank
     */
    private function getBank($validationType) {
        if (! isset($this->validationMap[$validationType])) {
            throw new BAV_DataBackendException_BankNotFound($validationType);
        
        }
        return $this->validationMap[$validationType];
    }

}


new BAV_CheckVerifyImport();

?>