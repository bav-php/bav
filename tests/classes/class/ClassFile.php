<?php

namespace malkusch\bav;

/**
 * This class is used for debugging purposes. An Object contains the path to the class
 * and can create instances.
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
 */
class ClassFile
{

    /**
     * @var string The path to the class
     */
    private $path = '';

    /**
     * @var string the name of the class
     */
    private $name = '';

    /**
     * @var ClassFile the parent class
     */
    private $parent = null;

    /**
     * @var array contains ClassFile objects which are needed by this class
     */
    private $neededClasses = array();

    /**
     * @var string The class definition
     */
    private $classDefinition = '';

    /**
     * @var array
     */
    private static $instances = array();

    /**
     * The constructor loads the class definition and parses it to initialize
     * {@link $parent} and {@link $neededClasses}.
     *
     * @throws MissingClassException
     * @param string $path
     */
    private function __construct($path)
    {
        $this->path = $path;
        $this->name = basename($path, '.php');


        /**
         * Load the class definition
         */
        require_once $this->path;


        /**
         * check parent class
         */
        preg_match_all(':extends +([a-zA-Z0-9_]+):', $this->getClassDefinition(), $matches);
        foreach ($matches[1] as $match) {
            if (! isset($this->neededClasses[$match])) {
                throw new MissingClassException($this->name, $match);

            }
            $this->parent                  = $this->neededClasses[$match];
            $this->neededClasses['parent'] = $this->parent;
            break;
        }


        /**
         * check needed classes
         */
        preg_match_all(':new +([a-zA-Z0-9_]+)\(:', $this->getClassDefinition(), $matchesNew);
        preg_match_all('/([a-zA-Z0-9_]+)::/', $this->getClassDefinition(), $matchesStatic);
        foreach (array_merge($matchesNew[1], $matchesStatic[1]) as $match) {
            if (! isset($this->neededClasses[$match])) {
                throw new MissingClassException($this->name, $match);

            }
        }
    }

    /**
     * @throws MissingClassException
     * @param string $path
     * @return ClassFile
     */
    public static function getClassFile($path)
    {
        if (! isset(self::$instances[$path])) {
            self::$instances[$path] = new self($path);

        }
        return self::$instances[$path];
    }

    /**
     * @throws ClassFileIOException
     * @throws MissingClassException
     * @param string $dir
     * @return array ClassFile objects
     */
    public static function getClassFiles($dir)
    {
        $classFiles = array();
        $dh         = opendir($dir);
        if (! $dh) {
            throw new ClassFileIOException();

        }
        while (($file = readdir($dh)) !== false) {
            $path = $dir.$file;
            if (is_file($path)) {
                $classFiles[] = self::getClassFile($path);

            }
        }
        closedir($dh);
        return $classFiles;
    }

    /**
     * @return BAV a new instance of {@link $name}
     */
    public function getInstance()
    {
        if (func_num_args() == 0) {
            return new $this->name();

        }
        $argStr = '';
        $args   = func_get_args();
        for ($i = 0; $i < func_num_args(); $i++) {
            $argStr .= '$args['.$i.'], ';

        }
        eval('$instance = new $this->name('.substr($argStr, 0, -2).');');
        return $instance;
    }

    /**
     * @throws ClassFileIOException
     * @return string
     */
    public function getClassDefinition()
    {
        if (is_null($this->classDefinition)) {
            $this->classDefinition = '';
            $fp                    = fopen($this->path, 'r');
            if (! $fp) {
                throw new ClassFileIOException();

            }
            $this->classDefinition = fread($fp, filesize($this->path));
            fclose($fp);


            /**
             * delete comments
             */
            $this->classDefinition = preg_replace('_/\*.*\*/_sU', '', $this->classDefinition);

        }
        return $this->classDefinition;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
