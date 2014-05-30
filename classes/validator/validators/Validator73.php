<?php

namespace malkusch\bav;

/**
 * Implements 73
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
class Validator73 extends ValidatorChain
{

    /**
     * @var array
     */
    private $defaultValidators = array();

    /**
     * @var array
     */
    private $exceptionValidators = array();

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->defaultValidators[] = new Validator00($bank);
        $this->defaultValidators[0]->setEnd(3);

        $this->defaultValidators[] = new Validator00($bank);
        $this->defaultValidators[1]->setWeights(array(2, 1));
        $this->defaultValidators[1]->setEnd(4);

        $this->defaultValidators[] = new Validator00($bank);
        $this->defaultValidators[2]->setWeights(array(2, 1));
        $this->defaultValidators[2]->setDivisor(7);
        $this->defaultValidators[2]->setEnd(4);

        $this->exceptionValidators = Validator51::getExceptionValidators($bank);
    }

    /**
     */
    protected function init($account)
    {
        parent::init($account);

        if ($this->account{2} == 9) {
            $this->validators = $this->exceptionValidators;

        } else {
            $this->validators = $this->defaultValidators;

        }
    }
}
