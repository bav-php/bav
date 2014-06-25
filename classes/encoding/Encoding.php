<?php

namespace malkusch\bav;

/**
 * This class provides methods for any encoded strings
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
abstract class Encoding
{

    /**
     *  @var String
     */
    protected $enc = 'UTF-8';

    /**
     * @throws UnsupportedEncodingException
     * @param String $encoding
     */
    public function __construct($encoding = 'UTF-8')
    {
        if (! $this->isSupported($encoding)) {
            throw new UnsupportedEncodingException($encoding);

        }
        $this->enc = $encoding;
    }

    /**
     * @return int length of $string
     */
    abstract public function strlen($string);

    /**
     * @param String $string
     * @param int $offset
     * @param int $length
     * @return String
     */
    abstract public function substr($string, $offset, $length = null);

    /**
     * @throws EncodingException
     * @param String $string
     * @param String $from_encoding
     * @return $string the encoded string
     */
    abstract public function convert($string, $from_encoding);

    /**
     * @param String
     * @return bool
     */
    public static function isSupported($encoding)
    {
        return false;
    }

    /**
     * @throws UnsupportedEncodingException
     * @param String $encoding
     * @return Encoding
     */
    public static function getInstance($encoding)
    {
        if (IconvEncoding::isSupported($encoding)) {
            return new IconvEncoding($encoding);

        } elseif (MBEncoding::isSupported($encoding)) {
            return new MBEncoding($encoding);

        } elseif (ISO8859Encoding::isSupported($encoding)) {
            return new ISO8859Encoding($encoding);

        } else {
            throw new UnsupportedEncodingException($encoding);

        }
    }
}
