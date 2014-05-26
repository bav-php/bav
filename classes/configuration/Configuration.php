<?php

namespace malkusch\bav;

/**
 * Configuration
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @license GPL
 */
class Configuration
{

    /**
     * @var BAV_Encoding
     */
    protected $encoding;

    /**
     * @var BAV_DataBackend
     */
    protected $dataBackend;

    /**
     * Sets the data backend.
     */
    public function setDataBackend(\BAV_DataBackend $backend)
    {
        $this->dataBackend = $backend;
    }

    /**
     * Returns the data backend.
     *
     * @return BAV_DataBackend
     */
    public function getDataBackend()
    {
        return $this->dataBackend;
    }
    
    /**
     * Sets the encoding.
     */
    public function setEncoding(\BAV_Encoding $encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * Returns the encoding.
     *
     * @return BAV_Encoding
     */
    public function getEncoding()
    {
        return $this->encoding;
    }
}
