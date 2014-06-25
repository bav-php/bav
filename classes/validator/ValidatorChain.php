<?php

namespace malkusch\bav;

/**
 * This class offers support for algorithmns which uses more algorithmns
 *
 * You have to add the algorithms to the $this->validators array.
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
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @copyright Copyright (C) 2006 Markus Malkusch
 */
class ValidatorChain extends Validator
{

    /**
     * @var Array a list of validators
     */
    protected $validators = array();

    /**
     * Adds a validator to the chain
     *
     * @param Validator $validator Validator
     *
     * @return void
     */
    public function addValidator(Validator $validator)
    {
        $this->validators[] = $validator;
    }

    /**
     * Iterates through the validators.
     *
     * @return bool
     */
    protected function getResult()
    {
        foreach ($this->validators as $validator) {
            if (! $this->continueValidation($validator)) {
                return false;

            }
            if ($this->useValidator($validator) && $validator->isValid($this->account)) {
                return true;

            }

        }
        return false;
    }

    /**
     * should not be used
     */
    final protected function validate()
    {
    }

    /**
     * After each successless iteration step this method will be called and
     * should return if the iteration should stop and the account is invalid.
     *
     * @return bool
     */
    protected function continueValidation(Validator $validator)
    {
        return true;
    }

    /**
     * Decide if you really want to use this validator
     *
     * @return bool
     */
    protected function useValidator(Validator $validator)
    {
        return true;
    }
}
