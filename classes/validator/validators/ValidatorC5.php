<?php

namespace malkusch\bav;

/**
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
 *
 *
 * @package classes
 * @subpackage validator
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @copyright Copyright (C) 2007 Markus Malkusch
 */
class ValidatorC5 extends Validator
{

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var Validator75
     */
    protected $mode1;

    /**
     * @var Validator29
     */
    protected $mode2;

    /**
     * @var Validator00
     */
    protected $mode3;

    /**
     * @var Validator09
     */
    protected $mode4;

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->mode1 = new Validator75($bank);
        $this->mode2 = new Validator29($bank);
        $this->mode3 = new Validator00($bank);
        $this->mode4 = new Validator09($bank);
    }

    protected function validate()
    {
        $account = ltrim($this->account, '0');
        $length  = strlen($account);

        switch ($length) {

            case 6:
            case 9:
                if ($account{0} < 9) {
                    $this->validator = $this->mode1;
                }
                break;

            case 8:
                if ($account{0} >= 3 && $account{0} <= 5) {
                    $this->validator = $this->mode4;

                }
                break;

            case 10:
                if ($account{0} == 1 || $account{0} >= 4 && $account{0} <= 6 || $account{0} == 9) {
                    $this->validator = $this->mode2;

                } elseif ($account{0} == 3) {
                    $this->validator = $this->mode3;

                } else {
                    $circle = substr($account, 0, 2);
                    if ($circle == 70 || $circle == 85) {
                        $this->validator = $this->mode4;

                    }
                }
                break;

            default:
                $this->validator = null;
                break;
        }
    }

    /**
     * @return bool
     */
    protected function getResult()
    {
        return ! is_null($this->validator) && $this->validator->isValid($this->account);
    }
}
