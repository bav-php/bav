<?php







/**
 * This abstract class defines the API for using other validation
 * projects. It's useful to test other projects agains each other.
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
abstract class BAV_TestAPI extends BAV
{
	
	
	private
	/**
	 * @var String
	 */
	$name = '';
	
	
	/**
     * @param int $account
     * @return bool
     * @throws BAV_TestAPIException_Validation
     */
    abstract protected function isValid(BAV_Bank $bank, $account);
    
	
	public function __construct() {
		$this->setName(get_class($this));
	}
	
	
	/**
	 * @param string $name
	 */
	protected function setName($name) {
		$this->name = $name;	
	}
	
	/**
	 * @param int $account
	 * @return BAV_TestAPIResult
	 */
    public function getResult(BAV_Bank $bank, $account) {
        try {
            $result = $this->isValid($bank, $account)
                    ? BAV_TestAPIResult::VALID
                    : BAV_TestAPIResult::INVALID;
            return new BAV_TestAPIResult($this, $result);
         
        } catch (Exception $e) {
            return new BAV_TestAPIResult_Error(
                $this,
                BAV_TestAPIResult::ERROR,
                $e->getMessage()
            );
         
        }
    }
	
	
	/**
	 * @return String
	 */
	public function getName() {
		return $this->name;
	}
	
	
}


?>