<?php

namespace malkusch\bav;

/**
 * Helper for BIC
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 */
class BICUtil
{

    /**
     * Extends an 8 character BIC to an 11 character BIC.
     * 
     * @param string $bic BIC
     * @return string
     */
    public static function normalize($bic)
    {
        return (strlen($bic) == 8) ? "{$bic}XXX" : $bic;
    }
}
