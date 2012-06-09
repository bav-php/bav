<?php







/**
 * The API for ktoblzcheck
 *
 * Copyright (C) 2009  Markus Malkusch <markus@malkusch.de>
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
 * @package classes
 * @subpackage verify
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2009 Markus Malkusch
 */
class BAV_TestAPI_Ktoblzcheck extends BAV_TestAPI {


    const BINARY            = "ktoblzcheck";
    const VALID             = 0;
    const INVALID           = 2;
    const BANK_NOT_FOUND    = 3;
    

	
	private
	/**
     * @var String
     */
	$binary = '',
	/**
	 * @var String
	 */
	$bankdata = '';
	
	
	/**
	 * @param String $bankdata
	 * @param String $binary
	 * @throws BAV_TestAPIException
	 */
	public function __construct($bankdata, $binary = null) {
		parent::__construct();
		
		$this->setName("ktoblzcheck");
		
		$this->bankdata = realpath($bankdata);
		$this->binary   = is_null($binary) ? self::BINARY : realpath($binary);
	}
	
	
	/**
	 * @param int $account
	 * @return bool
	 * @throws BAV_TestAPIException_Validation
	 * @throws BAV_TestAPIException_Validation_NotInitialized
	 * @throws BAV_TestAPIException_Validation_BankNotFound
	 */
	protected function isValid(BAV_Bank $bank, $account) {
        exec(
            "$this->binary --file=$this->bankdata {$bank->getBankID()} $account",
            $out,
            $result
        );
        
        switch ($result) {
        
            case self::VALID:
                return true;
                
            case self::INVALID:
                return false;
                
            case self::BANK_NOT_FOUND:
                throw new BAV_TestAPIException_Validation_BankNotFound("Bank not found: {$bank->getBankID()}");                
            
            default:
                throw new BAV_TestAPIException_Validation("unknown code $result: " . implode("\n", $out));
        
        }
	}
	
	
}


?>