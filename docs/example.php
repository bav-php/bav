#!/bin/env php
<?php
/**
 * This script shows examples how to use BAV.
 *
 * If you didn't install BAV yet and Configuration::isAutomaticInstallation() is true
 * (default) then BAV will install the bundesbank file which might take a few seconds.
 *
 * @filesource
 * @author Markus Malkusch <markus@malkusch.de>
 * @see BAV
 */

require_once __DIR__ . "/../vendor/autoload.php";

use malkusch\bav\BAV;

/**
 * Let's have some fun with the bank 10000000
 */
try {
    // API Facade with default configuration
    $bav = new BAV();

    // Does the bank exist?
    echo "Bank 10000000 ",
        $bav->isValidBank("10000000") ? "exists" : "doesn't exist", "\n";
    
    // Does the account exist?
    echo "Account 12345 is ",
        $bav->isValidBankAccount("10000000", "12345") ? "valid" : "invalid", "\n";

    // Use PHP's validation filter
    if (filter_var("10000000", FILTER_CALLBACK, $bav->getValidBankFilterCallback())) {
        echo "Bank 10000000 is valid.\n";
        
    } else {
        echo "Bank 10000000 is invalid.\n";
        
    }
    
    // Account filter validation needs a previous call to the bank filter.
    if (filter_var("12345", FILTER_CALLBACK, $bav->getValidAccountFilterCallback())) {
        echo "Account 12345 is valid.\n";
        
    } else {
        echo "Account 12345 is invalid.\n";
        
    }
    
    // Print name of the bank
    $agency = $bav->getMainAgency("10000000");
    echo "{$agency->getName()} {$agency->getCity()}\n";

    /**
     * Are there any more agencies?
     */
    print_r($bav->getAgencies("10000000"));

} catch (BAVException $error) {
    die("Some error happened in the data backend.");

}

/**
 * Now have a look at the special features of PDODataBackend. Note that
 * the script will exit now if the PDO object could not be created. You should
 * edit the PDO object to create a valid DBS connection.
 */

use malkusch\bav\PDODataBackendContainer;

try {
    /*
     * Create the PDO container. If you intend to do so you should
     * create the file bav/configuration.php and return a Configuration which
     * uses PDODataBackendContainer.
     * 
     * @see ConfigurationRegistry
     */
    $backendContainer = new PDODataBackendContainer(
        new \PDO('sqlite::memory:')
    );
    $backend = $backendContainer->getDataBackend();

    /**
     * We can use an arbitrary SQL statement to search for some agencies. This statement
     * needs at least to return the ids of the agencies.
     */
    $agencies = $backend->getAgencies("SELECT id FROM {$backend->getPrefix()}agency LIMIT 10");
    foreach ($agencies as $agency) {
        echo "Found agency {$agency->getPostcode()} of bank {$agency->getBank()->getBankID()}\n";

    }

    /**
     * You perform better if you provide all attributes of the agency table. Let's
     * try it and search all banks in munich.
     */
    $agencies = $backend->getAgencies(
        "SELECT * FROM {$backend->getPrefix()}agency
           WHERE city='München'
           GROUP BY bank"
    );
    foreach ($agencies as $agency) {
        echo "{$agency->getBank()->getBankID()} ({$agency->getName()}, {$agency->getCity()})\n";

    }

} catch (BAVException $error) {
    die($error->getTraceAsString());

}
