<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * include the real autoloader
 */
require_once __DIR__ . "/../../autoloader/autoloader.php";

BAV_Autoloader::triggerDeprecationWarning();

/**
 * Deprecated Autoloader
 *
 * This autoloader was removed.
 *
 * @deprecated 0.27
 */
class BAV_Autoloader
{

    public static function triggerDeprecationWarning()
    {
        trigger_error(
            "BAV_Autoloader is deprecated. Include bav/autoloader/autoloader.php instead.",
            E_USER_DEPRECATED
        );
    }

    public function register($classPath)
    {
        self::triggerDeprecationWarning();
    }

    public function loadDirectly()
    {
        self::triggerDeprecationWarning();
    }

    public function loadDeferred()
    {
        self::triggerDeprecationWarning();
    }

    public function ignoreErrors()
    {
        self::triggerDeprecationWarning();
    }

    public function dontIgnoreErrors()
    {
        self::triggerDeprecationWarning();
    }

    public function autoload($className)
    {
        self::triggerDeprecationWarning();
    }

    public function getPath($className)
    {
        self::triggerDeprecationWarning();
    }

    public static function getInstance()
    {
        self::triggerDeprecationWarning();
        return new self();
    }

    public static function add($classPath)
    {
        self::triggerDeprecationWarning();
    }
}
