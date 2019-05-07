<?php

namespace malkusch\bav;

/**
 * implements 68
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
 *
 *
 * @package classes
 * @subpackage validator
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @copyright Copyright (C) 2006 Markus Malkusch
 */
class Validator68 extends ValidatorChain
{

    /**
     * @var Validator10
     */
    private $validator10;

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->validator10  = new Validator00($bank);
        $this->validator10->setEnd(3);

        $this->validators[] = new Validator00($bank);

        $this->validators[] = new Validator00($bank);
        $this->validators[1]->setWeights(array(2, 1, 2, 1, 2, 0, 0, 1));
    }

    protected function init($account)
    {
        parent::init($account);
        
        // Die Kontonummern [..] enthalten keine führenden Nullen.
        $this->account = ltrim($this->account, "0");
    }
    
    protected function getResult()
    {
        switch (strlen($this->account)) {

            case 10:
                return $this->account{3} == 9 && $this->validator10->isValid($this->account);

            case 9:
                if ($this->account >= 400000000 && $this->account <= 499999999) {
                    return false;

                }
                return parent::getResult();

            case 6:
            case 7:
            case 8:
                return parent::getResult();

            default:
                return false;

        }
    }
}
