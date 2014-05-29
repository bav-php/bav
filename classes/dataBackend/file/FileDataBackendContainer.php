<?php

namespace malkusch\bav;

/**
 * Container for BAV_DataBackend_File objects.
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @license GPL
 * @see BAV_DataBackend
 */ 
class FileDataBackendContainer extends DataBackendContainer
{

    /**
     * @var string
     */
    private $file;

    /**
     * Sets the path for the backend.
     * 
     * @param string $file Path to the bundesbank file
     */
    public function __construct($file = null)
    {
        $this->file = $file;
    }

    /**
     * Returns the unconfigured backend which is only created by calling the
     * constructor.
     *
     * @return BAV_DataBackend_File
     */
    protected function makeDataBackend()
    {
        return new \BAV_DataBackend_File($this->file);
    }
}
