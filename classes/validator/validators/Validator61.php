<?php

namespace malkusch\bav;

/**
 * Implements 61
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
class Validator61 extends WeightedIterationValidator
{

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->setChecknumberPosition(-3);
        $this->setStart(0);
    }

    public function init($account)
    {
        parent::init($account);


        if ($this->account{8} == 8) {
            $this->setWeights(array(2, 1, 2, 1, 2, 1, 2, 0, 1, 2));
            $this->setEnd(-1);

        } else {
            $this->setWeights(array(2, 1));
            $this->setEnd(-4);

        }
    }

    protected function iterationStep()
    {
        $this->accumulator += $this->crossSum($this->number * $this->getWeight());
    }

    protected function getResult()
    {
        $result = (10 - ($this->accumulator % 10)) % 10;
        return (string)$result === $this->getCheckNumber();
    }
}
