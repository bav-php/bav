<?php

namespace malkusch\bav;

/**
 * Container for DataBackend_PDO objects.
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @license GPL
 * @see DataBackend
 */ 
class PDODataBackendContainer extends DataBackendContainer
{

    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $prefix;

    /**
     * Sets the PDO and the table prefix.
     * 
     * @param String $prefix the prefix of the table names. Default is 'bav_'.
     */
    public function __construct(\PDO $pdo, $prefix = 'bav_')
    {
        $this->pdo = $pdo;
        $this->prefix = $prefix;
    }

    /**
     * Returns the unconfigured backend which is only created by calling the
     * constructor.
     *
     * @return DataBackend_PDO
     */
    protected function makeDataBackend()
    {
        return new \DataBackend_PDO($this->pdo, $this->prefix);
    }
}
