<?php

namespace malkusch\bav;

/**
 * Implements D5
 *
 * Copyright (C) 2010  Markus Malkusch <markus@malkusch.de>
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
 * @copyright Copyright (C) 2010 Markus Malkusch
 */
class ValidatorD5 extends Validator
{

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var Validator06
     */
    private $validator1;

    /**
     * @var ValidatorChain
     */
    private $validatorChain;

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->validator1 = new Validator06($bank);
        $this->validator1->setWeights(array(2, 3, 4, 5, 6, 7, 8, 0, 0));

        $this->validatorChain = new ValidatorChain($bank);

        $validator2 = new Validator06($bank);
        $validator2->setWeights(array(2, 3, 4, 5, 6, 7, 0, 0, 0));
        $this->validatorChain->addValidator($validator2);

        $validator3 = new Validator06($bank);
        $validator3->setWeights(array(2, 3, 4, 5, 6, 7, 0, 0, 0));
        $validator3->setDivisor(7);
        $this->validatorChain->addValidator($validator3);

        $validator4 = new Validator06($bank);
        $validator4->setWeights(array(2, 3, 4, 5, 6, 7, 0, 0, 0));
        $validator4->setDivisor(10);
        $this->validatorChain->addValidator($validator4);
    }

    /**
     * Uses the validator
     *
     * @return bool
     */
    protected function getResult()
    {
        return $this->validator->isValid($this->account);
    }

    /**
     * decide which validators are used
     *
     * @return void
     */
    protected function validate()
    {
        $this->validator
            = substr($this->account, 2, 2) == 99
            ? $this->validator1
            : $this->validatorChain;
    }
}
