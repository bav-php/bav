<?php

namespace malkusch\bav;

/**
 * Implements D8
 *
 * Copyright (C) 2011  Markus Malkusch <markus@malkusch.de>
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
 *
 * @package classes
 * @subpackage validator
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @copyright Copyright (C) 2011 Markus Malkusch
 */
class ValidatorD8 extends Validator
{

    /**
     * @var Validator00
     */
    private $validator;

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->validator = new Validator00($bank);
    }

    protected function validate()
    {

    }

    /**
     * @return bool
     */
    protected function getResult()
    {
        if ($this->account[0] != 0) {
            return $this->validator->isValid($this->account);

        }
        $set = (int) substr($this->account, 0, 3);
        return $set >= 1 && $set <= 9;
    }
}
