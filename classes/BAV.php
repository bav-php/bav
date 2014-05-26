<?php

use malkusch\bav\ConfigurationRegistry;

/**
 * BAV is the super class of the Bank Account Validator project.
 * Every class will inherit this. The main purpose of this class is
 * an implementation of a namespace and set some configuration like
 * the project's encoding.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @deprecated 0.28
 * @license GPL
 */
abstract class BAV
{

    /**
     * @var BAV_Encoding
     * @deprecated 0.28
     * @see Configuration::getEncoding()
     */
    protected static $encoding;

    public static function classConstructor()
    {
        self::$encoding = ConfigurationRegistry::getConfiguration()->getEncoding();
    }

    /**
     * If you want to use another encoding
     *
     * @throws BAV_EncodingException_Unsupported
     * @param mixed $encoding
     * @see BAV_Encoding
     * @deprecated 0.28
     * @see Configuration::setEncoding()
     */
    public static function setEncoding($encoding)
    {
        trigger_error("Use Configuration::setEncoding()", E_USER_DEPRECATED);
        if (! $encoding instanceof BAV_Encoding) {
            $encoding = BAV_Encoding::getInstance($encoding);

        }
        ConfigurationRegistry::getConfiguration()->setEncoding($encoding);
        self::$encoding = $encoding;
    }

    /**
     * @return BAV_Version version of BAV
     */
    public static function getVersion()
    {
        return new BAV_Version('0.28');
    }

    /**
     * @return BAV_Version version of BAV
     * @deprecated 0.28
     * @see getVersion()
     */
    public static function get_bav_version()
    {
        trigger_error("use getVersion()", E_USER_DEPRECATED);
        return self::getVersion();
    }

    /**
     * Returns the version of the API. Note that different BAV versions
     * may have the same API version.
     *
     * @return BAV_Version version of BAV's API
     */
    public static function getApiVersion()
    {
        return new BAV_Version('2.5');
    }

    /**
     * Returns the version of the API. Note that different BAV versions
     * may have the same API version.
     *
     * @deprecated 0.28
     * @return BAV_Version version of BAV's API
     */
    public static function get_bav_api_version()
    {
        trigger_error("use getApiVersion()", E_USER_DEPRECATED);
        return self::getApiVersion();
    }
}
