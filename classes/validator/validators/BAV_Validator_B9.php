<?php






/**
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
class BAV_Validator_B9 extends BAV_Validator {


    protected
    /**
     * @var BAV_Validator
     */
    $validator,
    /**
     * @var BAV_Validator_B9a
     */
    $mode1,
    /**
     * @var BAV_Validator_B9b
     */
    $mode2;


    public function __construct(BAV_Bank $bank) {
        parent::__construct($bank);
        
        $this->mode1 = new BAV_Validator_B9a($bank);
        $this->mode2 = new BAV_Validator_B9b($bank);
    }
    
    
    protected function validate() {
        if (! preg_match('~^000?[^0]~', $this->account)) {
            $this->validator = null;
            return;
        
        }
        $this->validator = substr($this->account, 0, 3) === '000'
                         ? $this->mode2
                         : $this->mode1;
    }
    
    
    /**
     * @return bool
     */
    protected function getResult() {
        return ! is_null($this->validator) && $this->validator->isValid($this->account);
    }
    

}


?>