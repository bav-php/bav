<?php

namespace malkusch\bav;

/**
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
 *
 *
 * @package classes
 * @subpackage validator
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @copyright Copyright (C) 2006 Markus Malkusch
 */
class ValidatorB2 extends Validator
{

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var Validator02
     */
    protected $mode1;

    /**
     * @var Validator00
     */
    protected $mode2;

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->mode1 = new Validator02($bank);
        $this->mode1->setWeights(array(2, 3, 4, 5, 6, 7, 8, 9));

        $this->mode2 = new Validator00($bank);
        $this->mode2->setWeights(array(2, 1));
    }

    protected function validate()
    {
        $this->validator = $this->account{0} <= 7
                         ? $this->mode1
                         : $this->mode2;
    }

    /**
     * @return bool
     */
    protected function getResult()
    {
        return $this->validator->isValid($this->account);
    }
}
