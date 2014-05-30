<?php

namespace malkusch\bav;

/**
 * Implements 75
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
class Validator75 extends Validator00
{

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->setWeights(array(2, 1));
        $this->setStart(4);
        $this->setEnd(-2);
    }

    public function isValid($account)
    {
        $account = ltrim($account, '0');
        $length  = strlen($account);

        if ($length < 6 || $length > 9) {
            return false;

        }
        if ($length == 9) {
            if ($account{0} == 9) {
                $account = substr($account, 1, 6);

            } else {
                $account = substr($account, 0, 6);

            }

        }
        return parent::isValid($account);
    }
}
