<?php

BAV_Autoloader::add('BAV_Validator_00.php');
BAV_Autoloader::add('../BAV_Validator.php');
BAV_Autoloader::add('../../bank/BAV_Bank.php');


/**
 * Copyright (C) 2010  Markus Malkusch <bav@malkusch.de>
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
 * @author Markus Malkusch <bav@malkusch.de>
 * @copyright Copyright (C) 2010 Markus Malkusch
 */
class BAV_Validator_D1 extends BAV_Validator {


    const TRANSFORMATION_A = 436338;
    const TRANSFORMATION_B = 428259;


    static private
	/**
	 * @var Array
	 */
	$transformation = array(
        0 => self::TRANSFORMATION_A,
        3 => self::TRANSFORMATION_A,
        4 => self::TRANSFORMATION_A,
        5 => self::TRANSFORMATION_A,
        9 => self::TRANSFORMATION_A,

        1 => self::TRANSFORMATION_B,
        2 => self::TRANSFORMATION_B,
        6 => self::TRANSFORMATION_B,
        7 => self::TRANSFORMATION_B,
        8 => self::TRANSFORMATION_B
	);

    
    protected
    /**
     * @var String
     */
    $transformedAccount = '',
    /**
     * @var BAV_Validator_00
     */
    $validator;
    
    
    public function __construct(BAV_Bank $bank) {
        parent::__construct($bank);
        
        $this->validator = new BAV_Validator_00($bank);
        $this->validator->setNormalizedSize(10 + strlen(self::TRANSFORMATION_A));
    }
    
    
    protected function validate() {
        $transformationIndex    = $this->account{0};
        $transformationPrefix   = self::$transformation[$transformationIndex];

        $this->transformedAccount = $transformationPrefix . $this->account;
    }
    
    
    /**
     * @return bool
     */
    protected function getResult() {
        return $this->validator->isValid($this->transformedAccount);
    }
    

}