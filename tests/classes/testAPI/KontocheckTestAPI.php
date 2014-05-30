<?php

namespace malkusch\bav;

/**
 * The API for  Michael Plugge's kontocheck.
 *
 * Copyright (C) 2009  Markus Malkusch <markus@malkusch.de>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package classes
 * @subpackage verify
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2009 Markus Malkusch
 */
class KontocheckTestAPI extends TestAPI
{

    const NOT_INITIALIZED = -40;
    const BANK_NOT_FOUND  = -4;
    const INVALID_NULL    = -12;
    const INVALID_KTO     = -3;
    const INVALID_FALSE   =  0;

    /**
     * @param String $lutFile
     * @param int $lutVersion
     * @throws TestAPIException
     */
    public function __construct($lutFile, $lutVersion)
    {
        parent::__construct();

        $this->setName("kc");

        if (! lut_init($lutFile, $lutVersion)) {
            throw new TestAPIException("Could not initialize LUT.");

        }
    }

    /**
     * @param int $bank
     * @param int $account
     * @return bool
     * @throws ValidationTestAPIException
     * @throws NotInitializedTestAPIException
     * @throws BankNotFoundTestAPIException
     */
    protected function isValid(Bank $bank, $account)
    {
        $isValid = kto_check_blz($bank->getBankID(), $account);

        switch ($isValid) {

            case self::NOT_INITIALIZED:
                throw new NotInitializedTestAPIException("LUT not initialized");

            case self::BANK_NOT_FOUND:
                throw new BankNotFoundTestAPIException($bank->getBankID());

            case self::INVALID_NULL:
            case self::INVALID_KTO:
            case self::INVALID_FALSE:
                return false;

            default:
                if ($isValid < 0) {
                    throw new ValidationTestAPIException("unknown code $isValid");

                }
                return true;

        }
    }
}
