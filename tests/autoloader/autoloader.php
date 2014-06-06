<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Autoloader
 * 
 * This code was generated automatically.
 * Don't edit this file. Changes will get lost when
 * building a new autoloader.
 *
 * @see  AutoloaderBuilder::build()
 * @link http://php-autoloader.malkusch.de/en/
 */

namespace malkusch\autoloader;

require_once __DIR__ . '/InstantAutoloader.php';

$_autoloader = new InstantAutoloader(__DIR__ . '/index/0.php');
$_autoloader->setBasePath(__DIR__);
$_autoloader->register();
unset($_autoloader);