<?php







/**
 * Implements 52
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


class BAV_Validator_52 extends BAV_Validator_Iteration_Weighted implements BAV_Validator_BankDependent {


    private
    /**
     * @var BAV_Validator_20
     */
    $validator20,
    /**
     * @var int
     */
    $checknumberWeight = 0;


    public function __construct(BAV_Bank $bank) {
        parent::__construct($bank);
        
        $this->setWeights(array(2, 4, 8, 5, 10, 9, 7, 3, 6, 1, 2, 4));
        $this->setStart(-1);
        $this->setEnd(0);
        $this->setChecknumberPosition(5);
        
        $this->validator20 = new BAV_Validator_20($bank);
    }
    
    
    public function isValid($account) {
        try {
            return strlen($account) == 10 && $account{0} == 9
                 ? $this->validator20->isValid($account)
                 : parent::isValid($account);
                 
        } catch (BAV_ValidatorException_ESER $e) {
            return false;
        
        }
    }


    protected function iterationStep() {
        if ($this->position == $this->getEserChecknumberPosition()) {
            $this->checknumberWeight = $this->getWeight();
        
        } else {
            $this->accumulator += $this->number * $this->getWeight();
            
        }
    }
    
    
    protected function normalizeAccount($size) {
        $this->account = $this->getEser8();
    }


    protected function getResult() {
        return 10 === ($this->accumulator % 11 + $this->checknumberWeight * $this->getEserChecknumber()) % 11;
    }

}

?>