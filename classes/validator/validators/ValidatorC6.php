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
class ValidatorC6 extends Validator
{

    /**
     * @var Array
     */
    private static $transformation = array(
        0 => 4451970,
        1 => 4451981,
        2 => 4451992,
        3 => 4451993,
        4 => 4344992,
        5 => 4344990,
        6 => 4344991,
        7 => 5499570,
        8 => 4451994,
        9 => 5499579
    );

    /**
     * @var String
     */
    protected $transformedAccount = '';

    /**
     * @var Validator00
     */
    protected $validator;

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->validator = new Validator00($bank);
    }

    protected function validate()
    {
        $transformation = array_key_exists($this->account{0}, self::$transformation)
                        ? self::$transformation[$this->account{0}]
                        : '';
        $this->transformedAccount = $transformation . substr($this->account, 1);
        $this->validator->setNormalizedSize(9 + strlen($transformation));
    }

    /**
     * @return bool
     */
    protected function getResult()
    {
        return in_array($this->account{0}, array_keys(self::$transformation))
             ? $this->validator->isValid($this->transformedAccount)
             : false;
    }
}
