<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Defines the class InstantAutoloader
 *
 * PHP version 5
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.
 * If not, see <http://php-autoloader.malkusch.de/en/license/>.
 *
 * @category  PHP
 * @package   Autoloader
 * @author    Markus Malkusch <markus@malkusch.de>
 * @copyright 2009 - 2010 Markus Malkusch
 * @license   http://php-autoloader.malkusch.de/en/license/ GPL 3
 * @version   SVN: $Id$
 * @link      http://php-autoloader.malkusch.de/en/
 */

namespace malkusch\autoloader;

/**
 * There might be several InstantAutoloaders deployed in a project.
 * One is enough.
 */
if (! class_exists("malkusch\autoloader\InstantAutoloader", false)) {

    /**
     * An instant autoloader for shipping with project builds
     *
     * You can build a complete index of your project (by calling
     * Autoloader::buildIndex()). If you use AutoloaderIndex_PHPArrayCode as
     * index you can only use this instant autoloader for your project. This class
     * has no further dependency on any other class. Just copy this class and the
     * generated index anywhere into your project.
     *
     * Consider setting the AutoloaderIndexFilter_RelativePath filter.
     *
     * @category PHP
     * @package  Autoloader
     * @author   Markus Malkusch <markus@malkusch.de>
     * @license  http://php-autoloader.malkusch.de/en/license/ GPL 3
     * @version  Release: 1.12
     * @link     http://php-autoloader.malkusch.de/en/
     * @see      Autoloader::buildIndex()
     * @see      AutoloaderIndex_PHPArrayCode()
     * @see      AutoloaderIndexFilter_RelativePath()
     */
    class InstantAutoloader
    {

        const
            /**
             * The name of the class constructor is classConstructor().
             */
            CLASS_CONSTRUCTOR = 'classConstructor';

        private
            /**
             * @var string
             */
            $_basePath = "",
            /**
             * @var array
             */
            $_index = array();

        /**
         * Loads the generated index array
         *
         * The index must be a generated AutoloaderIndex_PHPArrayCode index.
         *
         * @param string $indexPath Path to the generated index
         */
        public function __construct($indexPath)
        {
            $this->_index = require $indexPath;
        }

        /**
         * Registers this autoloader at the autoloader stack
         *
         * @return void
         */
        public function register()
        {
            // spl_autoload_register() disables __autoload(). This might be unwanted.
            if (\function_exists("__autoload")) {
                \spl_autoload_register("__autoload");

            }
            \spl_autoload_register(array($this, "__autoload"));
        }

        /**
         * Includes all class paths
         *
         * You can use this as an alternative to the autoload mechanism. This
         * simply includes all classes without any autoloader.
         *
         * @return void
         */
        public function requireAll()
        {
            foreach ($this->_index as $classPath) {
                $this->_requirePath($classPath);

            }
        }

        /**
         * Sets the base path for the class paths in the index
         *
         * @param string $basePath Base path for the class paths in the index
         *
         * @return void
         */
        public function setBasePath($basePath)
        {
            $this->_basePath = $basePath;
        }

        /**
         * Autoloader callback
         *
         * @param string $class Class name
         *
         * @return void
         */
        public function __autoload($class)
        {
            $this->_normalizeClass($class);

            /*
             * spl_autoload_call() runs the complete stack,
             * even though the class is already defined by
             * a previously registered method.
             */
            if (
                \class_exists($class, false)
                || \interface_exists($class, false)
            ) {
                return;

            }
            if (
                \version_compare(PHP_VERSION, "5.4", '>=')
                && \trait_exists($class, false)
            ) {
                return;

            }
            if (!\array_key_exists($class, $this->_index)) {
                return;

            }

            $this->_requirePath($this->_index[$class]);

            $this->_callClassConstructor($class, self::CLASS_CONSTRUCTOR);
        }

        /**
         * Requires a class path
         *
         * @param string $path Class path
         *
         * @return void
         */
        private function _requirePath($path)
        {
            if (!empty($this->_basePath)) {
                $path = $this->_basePath . DIRECTORY_SEPARATOR . $path;

            }
            require_once $path;
        }

        /**
         * Normalizes the reference of a class name with strtolower()
         *
         * Normalizing is needed as PHP is case insensitive.
         *
         * @param String &$class The reference of a class name
         *
         * @see strtolower().
         * @return void
         */
        private function _normalizeClass(&$class)
        {
            $class = \strtolower($class);
        }

        /**
         * Calls the class constructor
         *
         * If the class $class has the method public static $constructor, it
         * will be called.
         *
         * @param String $class A class which might have a class constructor
         * @param String $constructorName the method name of the class constructor
         *
         * @return bool true if the class constructor was called
         */
        public static function _callClassConstructor($class, $constructorName)
        {
            $reflectionClass = new \ReflectionClass($class);
            if (!$reflectionClass->hasMethod($constructorName)) {
                return false;

            }

            $constructor = $reflectionClass->getMethod($constructorName);
            if (!$constructor->isStatic()) {
                return false;

            }

            if (\version_compare(PHP_VERSION, "5.4", '>=') && $reflectionClass->isTrait()) {
                return false;

            }

            if ($constructor->getDeclaringClass()->getName() != $reflectionClass->getName()) {
                return false;

            }

            $constructor->invoke(null);
            return true;
        }

    }

}