#!/bin/env php
<?php
/**
 * Installs the test environment
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @license WTFPL
 */

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";

try {
    ConfigurationRegistry::getConfiguration()->setAutomaticInstallation(false);
    $databack = ConfigurationRegistry::getConfiguration()->getDatabackendContainer()->getDataBackend();
    
    // install file
    $databack->install();
    echo "Bundesbank file downloaded.\n";

    // install PDO
    $pdoContainer = new PDODataBackendContainer(PDOFactory::makePDO());
    $pdoContainer->getDataBackend()->install();
    echo "PDO installed.\n";

} catch (DataBackendException $error) {
    die("Installation failed: {$error->getMessage()}\n");

}
