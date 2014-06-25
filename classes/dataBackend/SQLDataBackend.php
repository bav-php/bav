<?php

namespace malkusch\bav;

/**
 * Additional methods for SQL based data backends
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 */
abstract class SQLDataBackend extends DataBackend
{
    
    /**
     * You may use an arbitrary SQL statement to receive Agency objects.
     * Your statement should at least return the id of the agencies.
     *
     * @param string $sql
     * @throws MissingAttributesDataBackendIOException
     * @throws DataBackendIOException
     * @throws DataBackendException
     * @return Agency[]
     */
    abstract public function getAgencies($sql);
}
