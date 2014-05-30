<?php

namespace malkusch\bav;

/**
 * Implements 90g
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
 */
class Validator90g extends WeightedIterationValidator
{

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->setWeights(array(2, 1));

        /*
         * The specification is not clear about 4 or 3.
         * see https://github.com/malkusch/bav/issues/14
         */
        $this->setEnd(3);
    }

    protected function iterationStep()
    {
        $this->accumulator += $this->number * $this->getWeight();
    }

    protected function getResult()
    {
        $rest = $this->accumulator % 7;
        $checknumber = $rest == 0 ? 0 : (7 - $rest);
        return (string)$checknumber === $this->getCheckNumber();
    }
}
