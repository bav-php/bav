<?php

namespace malkusch\bav;

/**
 * Stores prepared statements for reuse.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 */
class StatementContainer
{

    /**
     * @var \PDOStatement[]
     */
    private $statements = array();

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * Inject the pdo.
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Returns a PDOStatement
     * 
     * This method will return the same object for equal queries.
     *
     * @param string $sql
     * @return \PDOStatement
     * @throws \PDOException
     */
    public function prepare($sql)
    {
        if (! array_key_exists($sql, $this->statements)) {
            $this->statements[$sql] = $this->pdo->prepare($sql);

        }
        return $this->statements[$sql];
    }
}
