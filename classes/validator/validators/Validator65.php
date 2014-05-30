<?php

namespace malkusch\bav;

/**
 * Implements 65
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
class Validator65 extends Validator00
{

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->setWeights(array(2, 1, 2, 1, 2, 1, 2, 0, 1, 2));
        $this->setStart(0);
        $this->setChecknumberPosition(7);
    }

    protected function init($account)
    {
        parent::init($account);

        if ($this->account{8} == 9) {
            $this->setEnd(-1);

        } else {
            $this->setEnd(6);

        }
    }
}
