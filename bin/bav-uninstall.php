#!/bin/env php
<?php
/**
 * Removes the Bundesbank file.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @license GPL
 * @see DataBackend
 */

namespace malkusch\bav;

require_once __DIR__ . "/../autoloader/autoloader.php";

try {
    $configuration = ConfigurationRegistry::getConfiguration();
    $configuration->setAutomaticInstallation(false);
    $configuration->setUpdatePlan(null);

    $databack = $configuration->getDatabackendContainer()->getDataBackend();
    $databack->uninstall();
    echo "Bundesbank file removed.\n";

} catch (DataBackendException $error) {
    die("Deinstallation failed: {$error->getMessage()}\n");

}