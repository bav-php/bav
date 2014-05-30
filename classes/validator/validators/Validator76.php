<?php

namespace malkusch\bav;

/**
 * Implements 76
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
class Validator76 extends WeightedIterationValidator
{

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->setChecknumberPosition(-3);
        $this->setStart(-4);
        $this->setEnd(1);
    }

    public function isValid($account)
    {
        if (parent::isValid($account)) {
            return true;

        }
        $account = ltrim($account, '0') . '00';
        return strlen($account) <= $this->normalizedSize
           &&  parent::isValid($account);
    }

    protected function getWeight()
    {
        return $this->i + 2;
    }

    protected function iterationStep()
    {
        $this->accumulator += $this->number * $this->getWeight();
    }

    protected function getResult()
    {
        $result = $this->accumulator % 11;
        return array_search((int)$this->account{0}, array(0, 4, 6, 7, 8, 9)) !== false
            && $result != 10
            && (string)$result === $this->getCheckNumber();
    }
}
