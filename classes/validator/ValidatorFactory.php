<?php

namespace malkusch\bav;

/**
 * Validator factory
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
 */
class ValidatorFactory
{
    
    /**
     * Builds a validator for a bank.
     *
     * @return Validator
     * @see Bank::getValidationType()
     * @see Bank::getValidator()
     */
    public function build(Bank $bank)
    {
        $class = sprintf('%s\Validator%s', __NAMESPACE__, $bank->getValidationType());
        return new $class($bank);
    }
}
