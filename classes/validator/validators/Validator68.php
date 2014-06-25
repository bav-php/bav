<?php

namespace malkusch\bav;

/**
 * implements 68
 *
 * Copyright (C) 2006  Markus Malkusch <markus@malkusch.de>
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
 *
 * @package classes
 * @subpackage validator
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @copyright Copyright (C) 2006 Markus Malkusch
 */
class Validator68 extends ValidatorChain
{

    /**
     * @var Validator10
     */
    private $validator10;

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->validator10  = new Validator00($bank);
        $this->validator10->setEnd(3);

        $this->validators[] = new Validator00($bank);

        $this->validators[] = new Validator00($bank);
        $this->validators[1]->setWeights(array(2, 1, 2, 1, 2, 0, 0, 1));
    }

    public function isValid($account)
    {
        // Die Kontonummern [..] enthalten keine führenden Nullen.
        $account = ltrim($account, "0");

        switch (strlen($account)) {

            case 10:
                return $account{3} == 9 && $this->validator10->isValid($account);

            case 9:
                if ($account >= 400000000 && $account <= 499999999) {
                    return false;

                }
                return parent::isValid($account);

            case 6:
            case 7:
            case 8:
                return parent::isValid($account);

            default:
                return false;

        }
    }
}
