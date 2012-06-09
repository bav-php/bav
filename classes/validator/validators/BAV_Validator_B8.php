<?php







/**
 * Implements B8
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


class BAV_Validator_B8 extends BAV_Validator_Chain {

    private
    /**
     * @param BAV_Validator_09 Validator
     */
    $_validator9;
  
  
    public function __construct(BAV_Bank $bank) {
        parent::__construct($bank);

        $this->validators[] = new BAV_Validator_20($bank);
        $this->validators[0]->setWeights(array(2, 3, 4, 5, 6, 7, 8, 9, 3));
        
        $this->validators[] = new BAV_Validator_29($bank);
        
        $this->_validator9 = new BAV_Validator_09($bank);
        $this->validators[] = $this->_validator9;
    }

    /**
     * Limits Validator_09 to the accounts
     *
     * @return bool
     */
    protected function useValidator(BAV_Validator $validator) {
        if ($validator !== $this->_validator9) {
            return true;

        }
        $set1 = substr($this->account, 0, 2);
        $set2 = substr($this->account, 0, 3);
        return ($set1 >= 51  && $set1 <= 59)
            || ($set2 >= 901 && $set2 <= 910);
    }


}


?>