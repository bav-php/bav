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
abstract class BAV_Validator extends BAV
{


    protected
    /**
     * @var int
     */
    $normalizedSize = 10,
    /**
     * @var string
     */
    $account = '',
    /**
     * @var int
     */
    $checknumberPosition = -1,
    /**
     * @var int
     */
    $eserChecknumberOffset = 0,
    /**
     * @var bool
     */
    $doNormalization = true,
    /**
     * @var BAV_Bank
     */
    $bank;


    public function __construct(BAV_Bank $bank)
    {
        $this->bank = $bank;
        $this->setChecknumberPosition(-1);
    }
    /**
     * @throws BAV_ValidatorException_NotExists
     * @return BAV_Validator
     */
    public static function getInstance(BAV_Bank $bank)
    {
        $type  = trim(strtoupper($bank->getValidationType()));
        $class = "BAV_Validator_$type";
        $file  = __DIR__."/validators/$class.php";
        if (! file_exists($file)) {
            throw new BAV_ValidatorException_NotExists($bank);

        }
        require_once $file;
        return new $class($bank);
    }
    
    
    /**
     * @param string $account
     * @return bool
     */
    public function isValid($account)
    {
    	try {
            $this->init($account);
            $this->validate();
            return ltrim($account, "0") != "" && $this->getResult();
            
    	} catch (BAV_ValidatorException_OutOfBounds $e) {
    		return false;
    		
    	}
    }
    
    
    public function setChecknumberPosition($position)
    {
        $this->checknumberPosition = $position;
    }
    public function setNormalizedSize($size)
    {
        $this->normalizedSize = $size;
    }
    /**
     * @param string $account
     */
    protected function init($account)
    {
        $this->account = $account;
        if ($this->doNormalization) {
            $this->normalizeAccount($this->normalizedSize);
            
        }
    }
    abstract protected function validate();
    /**
     * @return bool
     */
    abstract protected function getResult();
    
    
    /**
     * @return string
     */
    protected function getChecknumber()
    {
        return $this->account{$this->getNormalizedPosition($this->checknumberPosition)};
    }
    /**
     * converts negative positions.
     *
     * @param int $pos
     * @return int
     * @throws BAV_ValidatorException_OutOfBounds
     */
    protected function getNormalizedPosition($pos)
    {
    	if ($pos >= strlen($this->account) || $pos < -strlen($this->account)) {
    		throw new BAV_ValidatorException_OutOfBounds("Cannot access offset $pos in String $this->account");
    		
    	}
    	
        if ($pos >= 0) {
            return $pos;

        }
        return strlen($this->account) + $pos;
    }
    
    
    /**
     * Some validators need this
     *
     * @param int $int
     * @return int
     */
    protected function crossSum($int)
    {
        $sum     = 0;
        $str_int = (string) $int;
        for ($i = 0; $i < strlen($str_int); $i++) {
          //$sum = bcadd($str_int{$i}, $sum);
          $sum += $str_int{$i};

        }
        return $sum;
    }
    
    
    /**
     * @throws BAV_ValidatorException_OutOfBounds
     * @param int $int
     */
    protected function normalizeAccount($size)
    {
        $account = (string) $this->account;
        if (strlen($account) > $size) {
            throw new BAV_ValidatorException_OutOfBounds("Can't normalize $account to size $size.");

        }
        $this->account = str_repeat('0', $size - strlen($account)) . $account;
    }
    /**
     * @throws BAV_ValidatorException_ESER
     * @return string
     */
    protected function getESER8()
    {
        $account = ltrim($this->account, '0');
    
        if (strlen($account) != 8) {
            throw new BAV_ValidatorException_ESER();

        }
        $bankID = $this->bank->getBankID();
        if ($bankID{3} != 5) {
            throw new BAV_ValidatorException_ESER();

        }
        $blzPart = ltrim(substr($bankID, 4), '0');
        
        $this->eserChecknumberOffset = -(4 - strlen($blzPart));
        
        if (empty($blzPart)) {
            throw new BAV_ValidatorException_ESER();

        }
        $accountPart = ltrim(substr($account, 2), '0');
        $eser        = $blzPart.$account{0}.$account{1}.$accountPart;

        return $eser;
    }
    /**
     * @throws BAV_ValidatorException_ESER
     * @return string
     */
    protected function getESER9()
    {
        $bankID  = $this->bank->getBankID();
        $account = ltrim($this->account, '0');


        if (strlen($account) != 9) {
            throw new BAV_ValidatorException_ESER();

        }
        if ($bankID{3} != 5) {
            throw new BAV_ValidatorException_ESER();

        }

        $blzPart0 = substr($bankID, -4, 2);
        $blzPart1 = substr($bankID, -1);

        $accountPart0 = $account{0};
        $t            = $account{1};
        $p            = $account{2};
        $accountTail  = ltrim(substr($account, 3), '0');

        $eser = $blzPart0.$t.$blzPart1.$accountPart0.$p.$accountTail;
        return $eser;
    }
    protected function getEserChecknumberPosition()
    {
        return $this->getNormalizedPosition($this->checknumberPosition + $this->eserChecknumberOffset);
    }
    protected function getEserChecknumber()
    {
        return $this->account{$this->getEserChecknumberPosition()};
    }
    protected function isBetween($a, $b)
    {
        $account = (int) ltrim($this->account, '0');
        
        return $a < $b
             ? $account >= $a && $account <= $b
             : $account >= $b && $account <= $a;
    }

}


?>