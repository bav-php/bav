<?php

namespace malkusch\bav;

/**
 * Wrapper for the iconv Functions. If you use this wrapper
 * PHP must be compiled with these functions.
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
class IconvEncoding extends Encoding
{

    /**
     * @return bool
     */
    public static function isSupported($encoding)
    {
        return function_exists("iconv_set_encoding");
    }

    /**
     * @throws UnsupportedEncodingException
     * @param String $encoding
     */
    public function __construct($encoding = 'UTF-8')
    {
        parent::__construct($encoding);

        iconv_set_encoding("internal_encoding", $encoding);
    }

    /**
     * @return int length of $string
     */
    public function strlen($string)
    {
        return iconv_strlen($string);
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
             ? iconv_substr($string, $offset)
             : iconv_substr($string, $offset, $length);
    }

    /**
     * @throws EncodingException
     * @param String $string
     * @param String $from_encoding
     * @return $string the encoded string
     */
    public function convert($string, $from_encoding)
    {
        $encoded = iconv($from_encoding, $this->enc, $string);
        if ($encoded === false) {
            throw new EncodingException();

        }
        return $encoded;
    }
}
