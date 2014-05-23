<?php

/**
 * A class for handling versions
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
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2006 Markus Malkusch
 */
class BAV_Version extends BAV
{

    /**
     * @var BAV_Version
     */
    private static $phpVersion;

    /**
     * @var array
     */
    private $elements = array();

    /**
     * @var string
     */
    private $separator = '';

    /**
     * @param string $version
     * @param string $separator
     */
    public function __construct($version, $separator = '.')
    {
        $this->separator = $separator;
        $this->elements  = explode($separator, $version);
    }

    /**
     * @return boolean
     */
    public function isGreater(BAV_Version $version)
    {
        $thoseElements = $version->getElements(count($this->elements));
        $thisElements  = $this->getElements(count($thoseElements));

        foreach ($thisElements as $i => $thisElement) {
            if ($thoseElements[$i] !== $thisElement) {
                return $this->isGreater($thisElement, $thoseElements[$i]);

            }

        }
        return false;
    }

    /**
     * @return boolean
     */
    public function equals(BAV_Version $version)
    {
        $thoseElements = $version->getElements(count($this->elements));
        $thisElements  = $this->getElements(count($thoseElements));

        return $thoseElements == $thisElements;
    }

    /**
     * @return boolean
     */
    public function isLesser(BAV_Version $version)
    {
        return ! $this->equals($version) && ! $this->isGreater($version);
    }

    /**
     * @return string
     */
    public function getString()
    {
        return implode($this->separator, $this->elements);
    }

    /**
     * @return string
     */
    public function getNormalizedVersion()
    {
        $elements = array_merge($this->elements);
        for ($i = count($elements) - 1; $i > 0; $i--) {
            $lastElement = array_pop($elements);
            if ($lastElement !== '0') {
                $elements[] = $lastElement;
                break;

            }

        }
        return implode($this->separator, $elements);
    }

    /**
     * @param string $left
     * @param string $right
     * @return boolean
     */
    private function isGreater($left, $right)
    {
        return $left > $right;
    }

    /**
     * @param int $padding
     * @return array
     */
    private function getElements($padding = null)
    {
        if (is_null($padding) || count($this->elements) >= $padding) {
            return $this->elements;

        }
        return array_pad($this->elements, $padding, '0');
    }

    /**
     * @return BAV_Version
     */
    public static function getPHPVersion()
    {
        if (empty(self::$phpVersion)) {
            self::$phpVersion = new BAV_Version(phpversion());

        }
        return self::$phpVersion;
    }
}
