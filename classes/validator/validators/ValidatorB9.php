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
class ValidatorB9 extends Validator
{

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var ValidatorB9a
     */
    protected $mode1;

    /**
     * @var ValidatorB9b
     */
    protected $mode2;

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->mode1 = new ValidatorB9a($bank);
        $this->mode2 = new ValidatorB9b($bank);
    }

    protected function validate()
    {
        if (! preg_match('~^000?[^0]~', $this->account)) {
            $this->validator = null;
            return;

        }
        $this->validator = substr($this->account, 0, 3) === '000'
                         ? $this->mode2
                         : $this->mode1;
    }

    /**
     * @return bool
     */
    protected function getResult()
    {
        return ! is_null($this->validator) && $this->validator->isValid($this->account);
    }
}
