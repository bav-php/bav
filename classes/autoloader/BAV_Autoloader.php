<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * include the real autoloader 
 */
require_once __DIR__ . "/../../autoloader/autoloader.php";

BAV_Autoloader::_triggerDeprecationWarning();

/**
 * Deprecated Autoloader
 * 
 * This autoloader was removed.
 *
 * @deprecated
 */
class BAV_Autoloader
{
    
    public static function _triggerDeprecationWarning()
    {
        trigger_error(
            "BAV_Autoloader is deprecated. Include bav/autoloader/autoloader.php instead.",
            E_USER_DEPRECATED
        );
    }

    public function register($classPath)
    {
        self::_triggerDeprecationWarning();
    }
    
    public function loadDirectly()
    {
        self::_triggerDeprecationWarning();
    }
    
    public function loadDeferred()
    {
        self::_triggerDeprecationWarning();
    }

    public function ignoreErrors()
    {
        self::_triggerDeprecationWarning();
    }

    public function dontIgnoreErrors()
    {
        self::_triggerDeprecationWarning();
    }

    public function autoload($className)
    {
        self::_triggerDeprecationWarning();
    }
    
    public function getPath($className)
    {
        self::_triggerDeprecationWarning();
    }

    static public function getInstance()
    {
        self::_triggerDeprecationWarning();
        return new self();
    }

    static public function add($classPath)
    {
        self::_triggerDeprecationWarning();
    }

}
