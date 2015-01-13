<?php

namespace malkusch\bav;

/**
 * The API for BAV itself.
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
class BAVTestAPI extends TestAPI
{

    public function __construct()
    {
        parent::__construct();

        $this->setName("bav");
    }

    /**
     * Returns true if the API is available.
     *
     * @return bool
     */
    protected function isAvailable()
    {
        return true;
    }

    /**
     * @param int $bankCode
     * @param int $account
     * @return bool
     * @throws BankNotFoundTestAPIException
     */
    protected function isValid(Bank $bank, $account)
    {
        try {
            return $bank->isValid($account);

        } catch (Exception $e) {
            echo $e->getMessage(), "\n", $e->getTraceAsString();
            exit(1);

        }
    }
}
