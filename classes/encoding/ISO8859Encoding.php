<?php

namespace malkusch\bav;

/**
 * This wrapper supports PHP's built-in functions for the ISO-8859-* encodings.
 *
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
 * @subpackage dataBackend
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @copyright Copyright (C) 2006 Markus Malkusch
 */
class ISO8859Encoding extends Encoding
{

    /**
     * @return bool
     */
    public static function isSupported($encoding)
    {
        return preg_match('~^ISO-8859-([1-9]|1[0-5])$~', $encoding);
    }

    /**
     * @return int length of $string
     */
    public function strlen($string)
    {
        return strlen($string);
    }

    /**
     * @param String $string
     * @param int $offset
     * @param int $length
     * @return String
     */
    public function substr($string, $offset, $length = null)
    {
        return is_null($length)
             ? substr($string, $offset)
             : substr($string, $offset, $length);
    }

    /**
     * @throws EncodingException
     * @param String $string
     * @param String $from_encoding
     * @return $string the encoded string
     */
    public function convert($string, $from_encoding)
    {
        if ($from_encoding == $this->enc) {
            return $string;

        }
        throw new EncodingException();
    }
}
