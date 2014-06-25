<?php

namespace malkusch\bav;

/**
 * Entity for storing metadata for an ORM backend.
 * 
 * @internal
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 */
class MetaData
{
    
    const LASTMODIFIED = "lastModified";
    
    /**
     * @var String
     */
    private $name;
    
    /**
     * @var String
     */
    private $value;
    
    /**
     * Sets the name
     * 
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * Gets the name
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Sets the value
     * 
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
    
    /**
     * Gets the value
     * 
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
