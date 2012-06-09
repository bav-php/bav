<?php





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
 * @copyright Copyright (C) 2006 Markus Malkusch
 */
class BAV_Validator_68 extends BAV_Validator_Chain {


    private
    /**
     * @var BAV_Validator_10
     */
    $validator10;


    public function __construct(BAV_Bank $bank) {
        parent::__construct($bank);
        
        $this->validator10  = new BAV_Validator_00($bank);
        $this->validator10->setEnd(3);
        
        $this->validators[] = new BAV_Validator_00($bank);
        
        $this->validators[] = new BAV_Validator_00($bank);
        $this->validators[1]->setWeights(array(2, 1, 2, 1, 2, 0, 0, 1));
    }
    
    
    public function isValid($account) {
        switch (strlen($account)) {
        
            case 10:
                return $account{3} == 9 && $this->validator10->isValid($account);
                
            case 9:
                if ($account >= 400000000 && $account <= 499999999) {
                    return false;
                
                }
                
            case 6: case 7: case 8: case 9:
                return parent::isValid($account);
        
            default:
                return false;
        
        }
    }


}


?>