<?php

namespace malkusch\bav;

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
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @copyright Copyright (C) 2006 Markus Malkusch
 */
abstract class ValidatorIteration extends Validator
{

    /**
     * @var int
     */
    protected $i = 0;

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @var int
     */
    protected $number = 0;

    /**
     * @var int an accumulator for the iteration
     */
    protected $accumulator = 0;

    /**
     * @var int The inclusive beginning point of the iteration
     */
    private $start = -2;

    /**
     * @var int The inclusive ending point of the iteration
     */
    private $end = 0;

    public function __construct(Bank $bank)
    {
        parent::__construct($bank);

        $this->setStart(-2);
        $this->setEnd(0);
    }

    /**
     * @param int $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * @param int $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * @param string $account
     */
    protected function init($account)
    {
        parent::init($account);

        $this->accumulator  = 0;
    }

    protected function validate()
    {
        $start  = $this->getNormalizedPosition($this->start);
        $end    = $this->getNormalizedPosition($this->end);
        $length = abs($end - $start) + 1;

        $this->position = $start;
        $stepping       = (($end - $start < 0) ? -1 : +1);


        for ($this->i = 0; $this->i < $length; $this->i++) {
            $this->number = (int)$this->account{$this->position};
            $this->iterationStep();
            $this->position += $stepping;

        }
    }

    /**
     * The iteration step
     *
     * @param int $i
     */
    abstract protected function iterationStep();
}
