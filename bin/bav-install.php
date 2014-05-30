#!/bin/env php
<?php
/**
 * BAV installer for the Bundesbank bank data file.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @license GPL
 * @see DataBackend
 */

namespace malkusch\bav;

require_once __DIR__ . "/../autoloader/autoloader.php";

try {
    ConfigurationRegistry::getConfiguration()->setAutomaticInstallation(false);
    $databack = ConfigurationRegistry::getConfiguration()->getDatabackendContainer()->getDataBackend();

    $databack->install();
    echo "Bundesbank file downloaded.\n";

} catch (DataBackendException $error) {
    die("Installation failed: {$error->getMessage()}\n");

}