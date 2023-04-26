<?php

namespace malkusch\bav;

/**
 * Implements E2
 *
 * Copyright (C) 2014  Markus Malkusch <markus@malkusch.de>
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
class ValidatorE2 extends Validator
{
    
    /**
     * @var int[] The addition map.
     */
    private static $prefixes = [
        0 => 4383200,
        1 => 4383201,
        2 => 4383202,
        3 => 4383203,
        4 => 4383204,
        5 => 4383205,
    ];
    
    /**
     * @var bool The validation result.
     */
    private $result;
    
    protected function validate()
    {
        if (in_array($this->account[0], [6, 7, 8, 9])) {
            $this->result = false;
            return;
        }
        
        $validator = new Validator00($this->bank);
        $validator->doNormalization = false;

        $prefixedAccount = self::$prefixes[$this->account[0]] . substr($this->account, 1);
        
        $this->result = $validator->isValid($prefixedAccount);
    }
    
    protected function getResult()
    {
        return $this->result;
    }
}
