<?php







/**
 * Implements A4
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


class BAV_Validator_A4 extends BAV_Validator_Chain {
  
  
    public function __construct(BAV_Bank $bank) {
        parent::__construct($bank);

        $this->validators[] = new BAV_Validator_06($bank);
        $this->validators[0]->setWeights(array(2, 3, 4, 5, 6, 7, 0, 0, 0));
        $this->validators[0]->setEnd(3);
        
        $this->validators[] = new BAV_Validator_A4b($bank);
        
        $this->validators[] = new BAV_Validator_06($bank);
        $this->validators[2]->setWeights(array(2, 3, 4, 5, 6, 0, 0, 0, 0));
        $this->validators[2]->setEnd(4);
        
        $this->validators[] = new BAV_Validator_93($bank);
    }
    
    
    /**
     * Decide if you really want to use this validator
     *
     * @return bool
     */
    protected function useValidator(BAV_Validator $validator) {
        if (substr($this->account, 2, 2) == '99') {
            return $validator === $this->validators[2]
                || $validator === $this->validators[3];
        
        } else {
            return  $validator !== $this->validators[2];
            
        }
    }


}


?>