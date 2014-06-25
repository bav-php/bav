<?php

namespace malkusch\bav;

/**
 * Implements 87
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
class Validator87a extends Validator
{

    protected function validate()
    {
    }

     /**
     * @return bool
     */
    protected function getResult()
    {
        $accountID = $this->account;
        $i      = 0;
        $c2     = 0;
        $d2     = 0;
        $a5     = 0;
        $p      = 0;
        $tab1   = array(0, 4, 3, 2, 6);
        $tab2   = array(7, 1, 5, 9, 8);
        $konto  = array();

        for ($i = 0; $i < strlen($accountID); $i++) {
            $konto[$i+1] = $accountID{$i};
        }

        $i = 4;
        while ($i < count($konto) && $konto[$i] == 0) {
            $i++;
        }

        $c2 = $i % 2;

        while ($i < 10) {
            switch ($konto[$i]) {
                case 0: $konto[$i] = 5;
                    break;
                case 1: $konto[$i] = 6;
                    break;
                case 5: $konto[$i] = 10;
                    break;
                case 6: $konto[$i] = 1;
                    break;
            }

            if ($c2 == $d2) {
                if ($konto[$i] > 5) {
                    if ($c2 == 0 && $d2 == 0) {
                        $c2 = 1;
                        $d2 = 1;
                        $a5 = $a5 + 6 - ($konto[$i] - 6);
                    } else {
                        $c2 = 0;
                        $d2 = 0;
                        $a5 = $a5 + $konto[$i];
                    }
                } else {
                    if ($c2 == 0 && $d2 == 0) {
                        $c2 = 1;
                        $a5 = $a5 + $konto[$i];
                    } else {
                        $c2 = 0;
                        $a5 = $a5 + $konto[$i];
                    }
                }
            } else {
                if ($konto[$i] > 5) {
                    if ($c2 == 0) {
                        $c2 = 1;
                        $d2 = 0;
                        $a5 = $a5 - 6 + ($konto[$i] -6);
                    } else {
                        $c2 = 0;
                        $d2 = 1;
                        $a5 = $a5 - $konto[$i];
                    }
                } else {
                    if ($c2 == 0) {
                        $c2 = 1;
                        $a5 = $a5 - $konto[$i];
                    } else {
                        $c2 = 0;
                        $a5 = $a5 - $konto[$i];
                    }
                }
            }

            $i = $i + 1;
        }

        while ($a5 < 0 || $a5 > 4) {
            if ($a5 > 4) {
                $a5 = $a5 - 5;
            } else {
                $a5 = $a5 + 5;
            }
        }

        if ($d2 == 0) {
            $p = $tab1[$a5];
        } else {
            $p = $tab2[$a5];
        }

        if ($p == $konto[10]) {
            return true;
        } else {
            if ($konto[4] == 0) {

                if ($p > 4) {
                    $p = $p - 5;
                } else {
                    $p = $p + 5;
                }

                if ($p == $konto[10]) {
                    return true;
                }
            }
        }

        return false;
    }
}
