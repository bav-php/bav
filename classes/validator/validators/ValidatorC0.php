<?php

namespace malkusch\bav;

/**
 * Implements C0
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
class ValidatorC0 extends ValidatorChain implements ValidatorBankDependent
{

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->validators[] = new Validator52($bank);
        $this->validators[0]->setWeights(array(2, 4, 8, 5, 10, 9, 7, 3, 6, 1, 2, 4));

        $this->validators[] = new Validator20($bank);
        $this->validators[1]->setWeights(array(2, 3, 4, 5, 6, 7, 8, 9, 3));
    }

    public function useValidator(Validator $validator)
    {
        return $validator !== $this->validators[0]
            || preg_match('~^00[^0]~', $this->account);
    }
}
