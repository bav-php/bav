<?php





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
 * @copyright Copyright (C) 2010 Markus Malkusch
 */
class BAV_Validator_D5 extends BAV_Validator {


    private
    /**
     * @var BAV_Validator
     */
    $_validator,
    /**
     * @var BAV_Validator_06
     */
    $_validator1,
    /**
     * @var BAV_Validator_Chain
     */
    $_validatorChain;

    public function __construct(BAV_Bank $bank) {
        parent::__construct($bank);

        $this->_validator1 = new BAV_Validator_06($bank);
        $this->_validator1->setWeights(array(2, 3, 4, 5, 6, 7, 8, 0, 0));

        $this->_validatorChain = new BAV_Validator_Chain($bank);

        $validator2 = new BAV_Validator_06($bank);
        $validator2->setWeights(array(2, 3, 4, 5, 6, 7, 0, 0, 0));
        $this->_validatorChain->addValidator($validator2);

        $validator3 = new BAV_Validator_06($bank);
        $validator3->setWeights(array(2, 3, 4, 5, 6, 7, 0, 0, 0));
        $validator3->setDivisor(7);
        $this->_validatorChain->addValidator($validator3);

        $validator4 = new BAV_Validator_06($bank);
        $validator4->setWeights(array(2, 3, 4, 5, 6, 7, 0, 0, 0));
        $validator4->setDivisor(10);
        $this->_validatorChain->addValidator($validator4);
    }


    /**
     * Uses the validator
     *
     * @return bool
     */
    protected function getResult() {
        return $this->_validator->isValid($this->account);
    }


    /**
     * decide which validators are used
     *
     * @return void
     */
    protected function validate() {
        $this->_validator
            = substr($this->account, 2, 2) == 99
            ? $this->_validator1
            : $this->_validatorChain;
    }


}