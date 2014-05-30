<?php

namespace malkusch\bav;

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
class Validator90 extends ValidatorChain
{

    /**
     * @var Validator
     */
    private $modeF;

    /**
     * @var array
     */
    private $defaultValidators = array();

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);


        $this->defaultValidators["a"] = new Validator06($bank);
        $this->defaultValidators["a"]->setWeights(array(2, 3, 4, 5, 6, 7));
        $this->defaultValidators["a"]->setEnd(3);

        $this->defaultValidators["b"] = new Validator06($bank);
        $this->defaultValidators["b"]->setWeights(array(2, 3, 4, 5, 6));
        $this->defaultValidators["b"]->setEnd(4);

        $this->defaultValidators["c"] = new Validator90c($bank);
        $this->defaultValidators["d"] = new Validator90d($bank);
        $this->defaultValidators["e"] = new Validator90e($bank);
        $this->defaultValidators["g"] = new Validator90g($bank);

        $this->modeF = new Validator06($bank);
        $this->modeF->setWeights(array(2, 3, 4, 5, 6, 7, 8));
        $this->modeF->setEnd(2);
    }

    /**
     */
    protected function init($account)
    {
        parent::init($account);

        $this->validators = $this->account{2} == 9
                          ? array($this->modeF)
                          : $this->defaultValidators;
    }
}
