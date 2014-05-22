<?php








/**
 * Implements 90
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


class BAV_Validator_90 extends BAV_Validator_Chain {


    private
    /**
     * @var BAV_Validator
     */
    $modeF,
    /**
     * @var array
     */
    $defaultValidators = array();


    public function __construct(BAV_Bank $bank) {
        parent::__construct($bank);

        
        $this->defaultValidators["a"] = new BAV_Validator_06($bank);
        $this->defaultValidators["a"]->setWeights(array(2, 3, 4, 5, 6, 7));
        $this->defaultValidators["a"]->setEnd(3);
        
        $this->defaultValidators["b"] = new BAV_Validator_06($bank);
        $this->defaultValidators["b"]->setWeights(array(2, 3, 4, 5, 6));
        $this->defaultValidators["b"]->setEnd(4);
        
        $this->defaultValidators["c"] = new BAV_Validator_90c($bank);
        $this->defaultValidators["d"] = new BAV_Validator_90d($bank);
        $this->defaultValidators["e"] = new BAV_Validator_90e($bank);
        $this->defaultValidators["g"] = new BAV_Validator_90g($bank);
        
        $this->modeF = new BAV_Validator_06($bank);
        $this->modeF->setWeights(array(2, 3, 4, 5, 6, 7, 8));
        $this->modeF->setEnd(2);
    }
    
    
    /**
     */
    protected function init($account) {
        parent::init($account);
        
        $this->validators = $this->account{2} == 9
                          ? array($this->modeF)
                          : $this->defaultValidators;
    }


}


?>
