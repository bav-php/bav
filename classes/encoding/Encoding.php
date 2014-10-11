<?php

namespace malkusch\bav;

/**
 * This class provides methods for any encoded strings.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license WTFPL
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
        if (MBEncoding::isSupported($encoding)) {
            return new MBEncoding($encoding);

        } elseif (ISO8859Encoding::isSupported($encoding)) {
            return new ISO8859Encoding($encoding);

        } else {
            throw new UnsupportedEncodingException($encoding);

        }
    }
}
