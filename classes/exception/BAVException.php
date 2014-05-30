<?php

namespace malkusch\bav;

/**
 * Base exception
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @license GPL
 */
class BAVException extends \Exception
{

    /**
     * Constructs the exception
     */
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
