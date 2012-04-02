#!/usr/bin/php
<?php
/**
 * This script shows examples how to use BAV
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
 * @package example
 * @filesource
 * @author Markus Malkusch <bav@malkusch.de>
 * @copyright Copyright (C) 2006 Markus Malkusch
 * @see BAV_DataBackend
 * @see BAV_Bank
 * @see BAV_Agency
 */


/**
 * We need to require the BAV_Autoloader
 */
require_once dirname(__FILE__)."/../classes/autoloader/BAV_Autoloader.php";


/**
 * Some class definitions are needed in this example
 */
BAV_Autoloader::add('../classes/dataBackend/exception/BAV_DataBackendException.php');
BAV_Autoloader::add('../classes/dataBackend/exception/BAV_DataBackendException_BankNotFound.php');


/**
 * Now we have to decide which data backend we'll use.
 *
 * BAV_DataBackend_File:
 *
 *  BAV_Autoloader::add('../classes/dataBackend/BAV_DataBackend_File.php');
 *  $databack = new BAV_DataBackend_File();
 *
 * or BAV_DataBackend_PDO:
 *
 *  BAV_Autoloader::add('../classes/dataBackend/BAV_DataBackend_PDO.php');
 *  $databack = new BAV_DataBackend_PDO(new PDO('mysql:host=localhost;dbname=test', 'test'));
 *
 *
 * In this example we take BAV_DataBackend_File:
 */
BAV_Autoloader::add('../classes/dataBackend/BAV_DataBackend_File.php');
$databack = new BAV_DataBackend_File();


/**
 * As this is our first time we use it. We have to install the data structure.
 * This should only be called once for installation. For future use you should
 * skip this step.
 */
try {
    $databack->install();

} catch (BAV_DataBackendException $error) {
    die("Installation failed: {$error->getMessage()}\n");

}

/**
 * If you want to update your installed data structure to a new Bundesbank file
 * you have to call update():
 *
 *  $databack->update();
 *
 * Now this is not necessary, as install() calls implicitly update().
 */


/**
 * Let's have some fun with the bank 10000000
 */
try {
    $bank = $databack->getBank(10000000);
    /**
     * Hmm, what name does this bank have?
     */
    echo "{$bank->getMainAgency()->getName()} {$bank->getMainAgency()->getCity()}\n";
    /**
     * Are there any more agencies?
     */
    print_r($bank->getAgencies());
    /**
     * And now we want to see if the account 12345 is valid:
     */
    echo "Account 12345 is ", $bank->isValid(12345) ? "valid" : "invalid", "\n";

} catch (BAV_DataBackendException_BankNotFound $error) {
    /**
     * Now you would know that the bank 10000000 does not exist.
     * BAV_DataBackend->bankExists(10000000) would also tell that:
     */
    if (! $databack->bankExists($error->getBankID())) { // of course that's the same as if(true)
        die("Bank {$error->getBankID()} does not exist.");

    }

} catch (BAV_DataBackendException $error) {
    die("Some error happened in the data backend.");

}


/**
 * As this is a clean example we won't leave anything back so we uninstall the
 * datastructure. For your real life application you won't call uninstall() as
 * you want to keep your data structure.
 */
try {
    $databack->uninstall();

} catch (BAV_DataBackendException $error) {
    die("Uninstallation failed");

}


/**
 * Now have a look at the special features of BAV_DataBackend_PDO. Note that
 * the script will exit now if the PDO object could not be created. You should
 * edit the PDO object to create a valid DBS connection.
 */
try {
    BAV_Autoloader::add('../classes/dataBackend/BAV_DataBackend_PDO.php');
    $databackPDO = new BAV_DataBackend_PDO(new PDO('mysql:host=localhost;dbname=test', 'test'));
    $databackPDO->install();


    /**
     * We can use an arbitrary SQL statement to search for some agencies. This statement
     * needs at least to return the ids of the agencies.
     */
    $agencies = $databackPDO->getAgencies("SELECT id FROM {$databackPDO->getPrefix()}agency LIMIT 10");
    foreach ($agencies as $agency) {
       echo "Found agency {$agency->getPostcode()} of bank {$agency->getBank()->getBankID()}\n";

    }


    /**
     * You perform better if you provide all attributes of the agency table. Let's
     * try it and search all banks in munich.
     */
    $agencies = $databackPDO->getAgencies("
        SELECT * FROM {$databackPDO->getPrefix()}agency
        WHERE city='München'
        GROUP BY bank");
    foreach ($agencies as $agency) {
        echo "{$agency->getBank()->getBankID()} ({$agency->getName()}, {$agency->getCity()})\n";

    }


    $databackPDO->uninstall();

} catch (BAV_DataBackendException $error) {
    die($error->getTraceAsString());

}

?>