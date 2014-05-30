<?php

namespace malkusch\bav;

/**
 * Implements 24
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
class Validator24 extends WeightedIterationValidator
{

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->setWeights(array(1, 2, 3));
        $this->setStart(0);
        $this->setEnd(-2);
    }

    protected function init($account)
    {
        parent::init($account);

        $this->account = preg_replace('~^([3456]|9..)?0*~', '', $this->account);
    }

    protected function iterationStep()
    {
        $this->accumulator += ($this->number * $this->getWeight() + $this->getWeight()) % 11;
    }

    protected function getResult()
    {
        $result = $this->accumulator % 10;
        return (string)$result === $this->getCheckNumber();
    }
}
