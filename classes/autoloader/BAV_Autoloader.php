<?php
BAV_Autoloader::__static();
BAV_Autoloader::add('exception/BAV_AutoloaderException.php');
BAV_Autoloader::add('exception/BAV_AutoloaderException_UnregisteredClass.php');
BAV_Autoloader::add('exception/BAV_AutoloaderException_Include.php');
BAV_Autoloader::add('exception/BAV_AutoloaderException_Include_FileNotExists.php');
BAV_Autoloader::add('exception/BAV_AutoloaderException_ClassConstructorException.php');


/**
 * An implementation of __autoload().
 * 
 * You may have a look at any class to see how to use BAV_Autoloader.
 * BAV_Autoloader::add() is a better replacement for require_once. The
 * only parameter is the path to the needed class. This path can either
 * be an absolute path or a relative path. The relative path is relativ
 * to the file where BAV_Autoloader::add() is called.
 * 
 * BAV_Autoloader gives PHP the ability to know the class constructor
 * public static function __static();
 * You may define this class constructor (__construct() ist the object
 * constructor) for any classes. It is not defined when exactly __static()
 * will be called. But you can be sure that as soon as your class will be
 * used in any context, __static() was called before. It is for example
 * useful to initialize class attributes. Exceptions in __static() will
 * kill your application.
 * 
 * If you use BAV_Autoloader::add() your class files must have the name of
 * your class with the extension ".php". For example the class "Foo" must be
 * defined in the file "Foo.php". Case matters!
 * 
 * Before you can use BAV_Autoloader you have to require_once this file.
 * 
 * BAV_Autoloader uses spl_autoload_register() to register itself to the
 * Autoloader stack. If you've defined an __autoload() it will be added to
 * the stack before BAV_Autoloader->autoload(). Errors are only handled if
 * BAV_Autoloader is the last in the stack. You have to keep in mind that
 * an __autoload() which is defined after the definition of BAV_Autoload
 * won't be used. Use spl_autoload_register() to register that __autoload().
 * 
 * If you still have problems with more autoloaders you may disable the usage
 * of the spl_autoload stack by calling BAV_Autoloader->loadDirectly().
 * 
 *
 * Copyright (C) 2006  Markus Malkusch <bav@malkusch.de>
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
 * @subpackage autoloader
 * @author Markus Malkusch <bav@malkusch.de>
 * @copyright Copyright (C) 2006 Markus Malkusch
 */
class BAV_Autoloader {

    
    private
    /**
     * @var bool
     */
    $handleErrors = null,
    /**
     * @var bool
     */
    $loadDirectly = false,
    /**
     * @var array A table where you can find the path to a class
     */
    $classTable = array();
    
    
    private static
    /**
     * @var BAV_Autoloader
     */
    $autoloader;
    
    
    const BACKTRACE_FILE    = 'file';
    const BACKTRACE_LINE    = 'line';
    const BACKTRACE_INDEX   = 1;
    const CLASS_CONSTRUCTOR = '__static';
    
    
    /**
     * This is an example how to use the class constructor.
     */
    static public function __static() {
        self::$autoloader = new self();
    }
    /**
     * There exists only one instance of this object.
     */
    private function __construct() {
        $this->loadDeferred();
    }
    private function handleErrors() {
        return is_null($this->handleErrors)
             ? $this->loadDirectly || array_search(array(__CLASS__, 'autoload'), spl_autoload_functions()) === count(spl_autoload_functions()) - 1
             : $this->handleErrors;
    }
    /**
     * Register a class path. The path may be absolute or relative to
     * file where the class was registered. The name of the class file
     * must be the same as the class name with the extension ".php".
     * For example the class "Foo" must be saved in "Foo.php".
     *
     * @param string $classPath the class path
     * @throws BAV_AutoloaderException
     */
    public function register($classPath) {
        if (empty($classPath)) {
            throw new BAV_AutoloaderException();
            
        }
        
        $split = strrpos($classPath, '/'); 
        if ($split !== false) {
            $split++;
            $dir  = substr($classPath, 0, $split);
            $file = substr($classPath, $split);
            
        } else {
            $dir  = './';
            $file = $classPath;
            
        }
        
        $split = strrpos($file, '.'); 
        if (! $split) {
            throw new BAV_AutoloaderException();
            
        }
        $name = substr($file, 0, $split);
        if (isset($this->classTable[$name])) {
            return;
            
        }
        
        if ($dir{0} != '/') {
            $_context = debug_backtrace();
            $_file    = $_context[self::BACKTRACE_INDEX][self::BACKTRACE_FILE];
            $dir      = dirname(realpath($_file)).'/'.$dir;
            
        }
        $this->classTable[$name] = $dir.$file;
        
        if ($this->loadDirectly) {
            $this->autoload($name);
        
        }
    }
    /**
     * The class will be loaded directly after registering. This is
     * usefull if you have problems with more then one autoloader.
     */
    public function loadDirectly() {
        if (function_exists('__autoload')) {
            spl_autoload_register('__autoload');

        }
        spl_autoload_unregister(array($this, 'autoload'));
        $this->loadDirectly = true;
    }
    /**
     * The class will be loaded when PHP needs the definition. This is
     * default.
     */
    public function loadDeferred() {
        if (function_exists('__autoload')) {
            spl_autoload_register('__autoload');

        }
        spl_autoload_register(array($this, 'autoload'));
        $this->loadDirectly = false;
    }
    /**
     * Don't do anything on errors. This is default, if this autoloader is
     * not the last in the stack.
     */
    public function ignoreErrors() {
        $this->handleErrors = false;
    }
    /**
     * Take care about errors. This is default, if this is the last autoloader
     * in the stack.
     */
    public function dontIgnoreErrors() {
        $this->handleErrors = true;
    }
    /**
     * The handler for loading a class automatically. Errors are handled
     * if this is the last __autoload handler in the stack.
     */
    public function autoload($className) {
    
        try {
            if (! include_once $this->getPath($className)) {
                if (! file_exists($this->getPath($className))) {
                    throw new BAV_AutoloaderException_Include_FileNotExists($this->getPath($className));
                    
                } else {
                    throw new BAV_AutoloaderException_Include($className);
                    
                }
    
            }

            /**
             * call __static() if it exists
             */
            try {
                $reflectionClass = new ReflectionClass($className);
                $static = $reflectionClass->getMethod(self::CLASS_CONSTRUCTOR);
                if ($static->isStatic() && $static->getDeclaringClass()->getName() == $reflectionClass->getName()) {
                    eval($className.'::'.self::CLASS_CONSTRUCTOR.'();');
                
                }
                
            } catch (ReflectionException $e) {
                // No class constructor
                
            }
            
        } catch (BAV_AutoloaderException $e) {
            if (! $this->handleErrors()) {
                return;
            
            }
        
            $debug   = debug_backtrace();
            $context = $debug[self::BACKTRACE_INDEX];
            $file    = $context[self::BACKTRACE_FILE];
            $line    = $context[self::BACKTRACE_LINE];
            
            trigger_error('BAV_AutoloaderException caused in '.$file.':'.$line);
            
            try {
                throw $e;
                
            } catch (BAV_AutoloaderException_UnregisteredClass $e) {
                trigger_error('Class '.$e->getClassName().' was not registered.');
                
            }
        }
    }
    
    
    /**
     * @param string $className
     * @return string the path to the registered class
     * @throws BAV_AutoloaderException_UnregisteredClass
     */
    public function getPath($className) {
        if (! isset($this->classTable[$className])) {
            throw new BAV_AutoloaderException_UnregisteredClass($className);
            
        }
        return $this->classTable[$className];
    }


    /**
     * @return BAV_Autoloader The Singleton
     */
    static public function getInstance() {
        return self::$autoloader;
    }
    
    
    /**
     * BAV_Autoloader:add($classPath) is an alias for
     * BAV_Autoloader::getInstance()->register($classPath);
     *
     * @param string $classPath
     * @throws BAV_AutoloaderException
     * @see register()
     */
    static public function add($classPath) {
        self::getInstance()->register($classPath);
    }


}


?>