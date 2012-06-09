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

require_once dirname(__FILE__) . '/InstantAutoloader.php';

$_autoloader = new InstantAutoloader(dirname(__FILE__) . '/index/0.php');
$_autoloader->setBasePath(dirname(__FILE__));
$_autoloader->register();

$_autoloader = new InstantAutoloader(dirname(__FILE__) . '/index/1.php');
$_autoloader->setBasePath(dirname(__FILE__));
$_autoloader->register();
unset($_autoloader);