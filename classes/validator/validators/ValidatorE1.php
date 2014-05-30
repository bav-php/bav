<?php

namespace malkusch\bav;

/**
 * Implements E1
 *
 * Copyright (C) 2014  Markus Malkusch <markus@malkusch.de>
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
 * This class was strong inspired by the class SystemE1 of Jan Schädlich
 * https://github.com/jschaedl/Bav/blob/master/library/Bav/Validator/De/SystemE1.php
 */

class ValidatorE1 extends WeightedIterationValidator
{

    private static $subsitutions = array(48, 49, 50, 51, 52, 53, 54, 55, 56, 57);

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->setWeights(array(1, 2, 3, 4, 5, 6, 11, 10, 9));
        $this->setDivisor(11);
    }

    protected function iterationStep()
    {
        $this->accumulator += self::$subsitutions[$this->number] * $this->getWeight();
    }

    protected function getResult()
    {
        $result = $this->accumulator % $this->divisor;
        if ($result == 10) {
            return false;

        }
        return (string) $result === $this->getCheckNumber();
    }
}
