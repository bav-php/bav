<?php

namespace malkusch\bav;

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
abstract class TestAPI
{

    /**
     * @var String
     */
    private $name = '';

    /**
     * @param int $account
     * @return bool
     * @throws ValidationTestAPIException
     */
    abstract protected function isValid(Bank $bank, $account);
    
    /**
     * Returns true if the API is available.
     *
     * @return bool
     */
    abstract protected function isAvailable();
    
    /**
     * Return true for known false positives.
     *
     * @return true
     */
    public function ignoreTestCase(Bank $bank, $account)
    {
        return false;
    }

    /**
     * @throws TestAPIUnavailableException
     */
    public function __construct()
    {
        $this->setName(get_class($this));
        
        if (! $this->isAvailable()) {
            throw new TestAPIUnavailableException();

        }
    }

    /**
     * @param string $name
     */
    protected function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param int $account
     * @return TestAPIResult
     */
    public function getResult(Bank $bank, $account)
    {
        try {
            $result = $this->isValid($bank, $account)
                    ? TestAPIResult::VALID
                    : TestAPIResult::INVALID;
            return new TestAPIResult($this, $result);

        } catch (Exception $e) {
            return new TestAPIErrorResult(
                $this,
                TestAPIResult::ERROR,
                $e->getMessage()
            );

        }
    }

    /**
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }
}
