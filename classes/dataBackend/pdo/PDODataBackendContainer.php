<?php

namespace malkusch\bav;

/**
 * Container for PDODataBackend objects.
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 * @see DataBackend
 */
class PDODataBackendContainer extends DataBackendContainer
{

    /**
     * @var \PDO
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
     * @return PDODataBackend
     */
    protected function makeDataBackend()
    {
        return new PDODataBackend($this->pdo, $this->prefix);
    }
}
