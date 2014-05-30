<?php

namespace malkusch\bav;

/**
 * Implements 27
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
class Validator27 extends TransformationIterationValidator
{

    /**
     * @var Validator00
     */
    private $validator00;

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->validator00 = new Validator00($bank);

        $this->setMatrix(array(
            array(0,1,5,9,3,7,4,8,2,6),
            array(0,1,7,6,9,8,3,2,5,4),
            array(0,1,8,4,6,2,9,5,7,3),
            array(0,1,2,3,4,5,6,7,8,9),
        ));
    }

    /**
     * @param string $account
     * @return bool
     */
    public function isValid($account)
    {
        return (int) $account <= 999999999
             ? $this->validator00->isValid($account)
             : parent::isValid($account);
    }

    /**
     * @return bool
     */
    protected function getResult()
    {
        $result = (10 - ($this->accumulator % 10)) % 10;
        return (string)$result === $this->getCheckNumber();
    }
}
