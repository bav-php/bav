<?php

namespace malkusch\bav;

/**
 * Implements B8
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
 */
class ValidatorB8 extends ValidatorChain
{

    /**
     * @param Validator09 Validator
     */
    private $validator9;

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->validators[] = new Validator20($bank);
        $this->validators[0]->setWeights(array(2, 3, 4, 5, 6, 7, 8, 9, 3));

        $this->validators[] = new Validator29($bank);

        $this->validator9 = new Validator09($bank);
        $this->validators[] = $this->validator9;
    }

    /**
     * Limits Validator09 to the accounts
     *
     * @return bool
     */
    protected function useValidator(Validator $validator)
    {
        if ($validator !== $this->validator9) {
            return true;

        }
        $set1 = substr($this->account, 0, 2);
        $set2 = substr($this->account, 0, 3);
        return ($set1 >= 51  && $set1 <= 59)
            || ($set2 >= 901 && $set2 <= 910);
    }
}
