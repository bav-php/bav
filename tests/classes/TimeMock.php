<?php

namespace malkusch\bav;

/**
 * time() mock.
 *
 * This mock works only if the built-in wasn't used before. I.e. tests
 * which want to mock time() have to run before other tests which might
 * call somewhere in the stack an unmocked time(). If the built in was
 * called before there is no chance to mock time() anymore.
 */
function time()
{
    return TimeMock::time();
}

/**
 * Mock for PHP's time() built-in.
 *
 * @license WTFPL
 * @author Markus Malkusch <markus@malkusch.de>
 */
class TimeMock
{

    /**
     * @var int
     */
    private static $time = null;

    public static function time()
    {
        return is_null(self::$time) ? \time() : self::$time;
    }

    /**
     * Disable mocking of the time.
     */
    public static function disable()
    {
        self::$time = null;
    }

    /**
     * Sets the moc time.
     *
     * @param int $time moc time
     */
    public static function setTime($time)
    {
        return self::$time = $time;
    }
}
