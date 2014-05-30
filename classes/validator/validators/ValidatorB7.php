<?php

namespace malkusch\bav;

/**
 * Implements B7
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
class ValidatorB7 extends Validator01
{

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->setWeights(array(3, 7, 1));
    }

    protected function getResult()
    {
        return ($this->isBetween(1000000, 5999999) || $this->isBetween(700000000, 899999999))
             ? parent::getResult()
             : true;
    }
}
