<?php

namespace malkusch\bav;

/**
 * Default configuration uses FileDataBackendContainer and any available UTF-8 encoder.
 * 
 * Automatic installation is enabled.
 * 
 * If no UTF-8 encoding is supported ISO-8859-15 will be used.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @license GPL
 * @see FileDataBackendFactory
 */
class DefaultConfiguration extends Configuration
{

    public function __construct()
    {
        $this->setAutomaticInstallation(true);

        $this->setDataBackendContainer(new FileDataBackendContainer());

        $encoding = null;
        try {
            $encoding = \BAV_Encoding::getInstance("UTF-8");

        } catch (\BAV_EncodingException_Unsupported $e) {
            trigger_error("UTF-8 is not supported; bav is falling back to ISO-8859-15", E_WARNING);
            $encoding = \BAV_Encoding::getInstance("ISO-8859-15");

        }
        $this->setEncoding($encoding);
    }
}
