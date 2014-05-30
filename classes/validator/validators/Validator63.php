<?php

namespace malkusch\bav;

/**
 * Implements 63
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
class Validator63 extends WeightedIterationValidator
{

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->setWeights(array(2, 1));
        $this->setChecknumberPosition(7);
        $this->setStart(6);
        $this->setEnd(1);
    }

    public function isValid($account)
    {
        if (parent::isValid($account)) {
            return true;

        }
        if (substr($this->account, 0, 3) !== '000') {
            return false;

        }
        return parent::isValid(ltrim($account.'00', '0'));
    }

    protected function iterationStep()
    {
        $this->accumulator += $this->crossSum($this->number * $this->getWeight());
    }

    protected function getResult()
    {
        $result = (10 - ($this->accumulator % 10)) % 10;
        return $this->account{0} == '0' && (string)$result === $this->getCheckNumber();
    }
}
