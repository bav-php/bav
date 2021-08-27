<?php

namespace malkusch\bav;

/**
 * Implements 66
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
class Validator66 extends WeightedIterationValidator
{

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->setWeights(array(2, 3, 4, 5, 6, 0, 0, 7));
        $this->setStart(-2);
        $this->setEnd(1);
    }

    protected function iterationStep()
    {
        $this->accumulator += $this->number * $this->getWeight();
    }

    protected function getResult()
    {
        // update 2014-03-03
        if ($this->account[1] == '9') {
            return true;

        }
        $result = (11 - $this->accumulator % 11) % 10;
        return $this->account[0] == '0' && (string)$result === $this->getCheckNumber();
    }
}
