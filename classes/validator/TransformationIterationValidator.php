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
abstract class TransformationIterationValidator extends ValidatorIteration
{

    /**
     * @var Array
     */
    private $rowIteration = array();

    /**
     * @var Array
     */
    private $matrix = array();

    /**
     * The iteration step
     */
    protected function iterationStep()
    {
        $this->accumulator += $this->getTransformedNumber();
    }

    /**
     */
    public function setMatrix(Array $matrix)
    {
        $this->matrix = $matrix;
        if (empty($this->rowIteration)) {
            for ($i = 0; $i < count($matrix); $i++) {
                $this->rowIteration[] = $i;

            }

        }
    }

    /**
     */
    public function setRowIteration(Array $rowIteration)
    {
        $this->rowIteration = $rowIteration;
    }

    /**
     * @return array
     */
    protected function getTransformationRow()
    {
        return $this->matrix[$this->rowIteration[$this->i % count($this->rowIteration)]];
    }

    /**
     * @param int $i
     * @return int
     */
    protected function getTransformedNumber()
    {
        $row = $this->getTransformationRow();
        return array_key_exists($this->number, $row)
             ? $row[$this->number]
             : 0;
    }
}
