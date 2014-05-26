<?php

namespace malkusch\bav;

/**
 * Default configuration uses BAV_DataBackend_File and any available UTF-8 encoder.
 * 
 * If no UTF-8 encoding is supported ISO-8859-15 will be used.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @license GPL
 * @see BAV_DataBackend_File
 */
class DefaultConfiguration extends Configuration
{

    /**
     * Returns the data backend.
     *
     * @return BAV_DataBackend
     */
    public function getDataBackend()
    {
        if (is_null($this->dataBackend)) {
            $this->setDataBackend(new \BAV_DataBackend_File());

        }
        return parent::getDataBackend();
    }

    /**
     * Returns the encoding.
     *
     * @return BAV_Encoding
     */
    public function getEncoding()
    {
        if (is_null($this->encoding)) {
            $encoding = null;
            try {
                $encoding = \BAV_Encoding::getInstance("UTF-8");

            } catch (\BAV_EncodingException_Unsupported $e) {
                trigger_error("UTF-8 is not supported; bav is falling back to ISO-8859-15", E_WARNING);
                $encoding = \BAV_Encoding::getInstance("ISO-8859-15");

            }
            $this->setEncoding($encoding);

        }
        return parent::getEncoding();
    }
}
