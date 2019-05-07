<?php

namespace malkusch\bav;

/**
 * Implements 74
 *
 * Copyright (C) 2007  Markus Malkusch <markus@malkusch.de>
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
class Validator74 extends Validator00
{

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->setWeights(array(2, 1));
    }

    public function isValid($account)
    {
        return strlen($account) >= 2 && parent::isValid($account);
    }

    protected function getResult()
    {
        return $this->variantOne() || $this->variantTwo();
    }

    private function variantOne()
    {
        if (parent::getResult()) {
            return true;
        } elseif (strlen(ltrim($this->account, '0')) == 6) {
            $nextHalfDecade = round($this->accumulator / 10) * 10 + 5;
            $check = ($nextHalfDecade - $this->accumulator) % 10;

            return (string)$check === $this->getChecknumber();
        }

        return false;
    }

    private function variantTwo()
    {
        $validator = new Validator04($this->bank);
        $validator->setWeights([2, 3, 4, 5, 6, 7, 2, 3, 4]);
        $validator->setDivisor(11);

        return $validator->isValid($this->account);
    }
}