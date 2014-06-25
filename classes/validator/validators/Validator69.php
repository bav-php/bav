<?php

namespace malkusch\bav;

/**
 * implements 69
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
class Validator69 extends ValidatorChain
{

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->validators[] = new Validator28($bank);
        $this->validators[0]->setWeights(array(2, 3, 4, 5, 6, 7, 8));

        $this->validators[] = new Validator69b($bank);
    }

    public function isValid($account)
    {
        return ($account >= 9300000000 && $account <= 9399999999) || parent::isValid($account);
    }

    /**
     * @return bool
     */
    protected function useValidator(Validator $validator)
    {
        if ($validator === $this->validators[0] && ($this->account >= 9700000000 && $this->account <= 9799999999)) {
            return false;

        }
        return true;
    }
}
