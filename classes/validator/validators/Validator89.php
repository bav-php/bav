<?php

namespace malkusch\bav;

/**
 * Implements 89
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
class Validator89 extends Validator06
{

    /**
     * @var Validator10
     */
    private $validator10;

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->setWeights(array(2, 3, 4, 5, 6, 7));
        $this->setEnd(3);

        $this->validator10 = new Validator10($bank);
    }

    protected function iterationStep()
    {
        $this->accumulator += $this->crossSum($this->number * $this->getWeight());
    }

    public function isValid($account)
    {
        $length = strlen(ltrim($account, '0'));
        return (($length == 8 || $length == 9) && $this->validator10->isValid($account))
            || ($length == 7 && parent::isValid($account));
    }
}
