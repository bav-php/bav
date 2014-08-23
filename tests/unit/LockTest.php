<?php

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";

/**
 * Tests Lock.
 *
 * @license WTFPL
 * @author Markus Malkusch <markus@malkusch.de>
 * @see Lock
 */
class LockTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * Tests checkedLock().
     * 
     * @see Lock::checkedLock()
     */
    public function testCheckedLock()
    {
        $name = __FUNCTION__;
        $checkLock = new Lock($name);
        $lock = new Lock($name);
        
        $this->assertFalse($lock->checkedLock());
        $this->assertFalse($checkLock->nonblockingLock());
        
        $lock->unlock();
    }
    
    /**
     * Tests executeOnce().
     * 
     * @see Lock::executeOnce()
     */
    public function testExecuteOnce()
    {
        $name = __FUNCTION__;
        
        $isExecuted = false;
        $isExecutedCheck = false;
        
        $lock = new Lock($name);
        $lock->executeOnce(function () use (&$isExecuted, &$isExecutedCheck, $name) {
            $isExecuted = true;
            
            $checkLock = new Lock($name);
            $checkLock->nonblockingExecuteOnce(function () use (&$isExecutedCheck) {
                $isExecutedCheck = true;
            });
        });
        
        $this->assertTrue($isExecuted);
        $this->assertFalse($isExecutedCheck);
    }
    
    /**
     * Tests lock().
     * 
     * @see Lock::lock()
     */
    public function testLock()
    {
        $name = __FUNCTION__;
        $checkLock = new Lock($name);
        $lock = new Lock($name);
        
        $lock->lock();
        $this->assertFalse($checkLock->nonblockingLock());
        
        $lock->unlock();
    }
    
    /**
     * Tests nonblockingExecuteOnce().
     * 
     * @see Lock::nonblockingExecuteOnce()
     */
    public function testNonblockingExecuteOnce()
    {
        $name = __FUNCTION__;
        
        $isExecuted = false;
        $isExecutedCheck = false;
        
        $lock = new Lock($name);
        $lock->nonblockingExecuteOnce(function () use (&$isExecuted, &$isExecutedCheck, $name) {
            $isExecuted = true;
            
            $checkLock = new Lock($name);
            $checkLock->nonblockingExecuteOnce(function () use (&$isExecutedCheck) {
                $isExecutedCheck = true;
            });
        });
        
        $this->assertTrue($isExecuted);
        $this->assertFalse($isExecutedCheck);
    }
    
    /**
     * Tests nonblockingLock().
     * 
     * @see Lock::nonblockingLock()
     */
    public function testNonblockingLock()
    {
        $name = __FUNCTION__;
        $checkLock = new Lock($name);
        $lock = new Lock($name);
        
        $this->assertTrue($lock->nonblockingLock());
        $this->assertFalse($checkLock->nonblockingLock());
        
        $lock->unlock();
    }
    
    /**
     * Tests unlock().
     * 
     * @see Lock::unlock()
     */
    public function testUnlock()
    {
        $name = __FUNCTION__;
        $checkLock = new Lock($name);
        $lock = new Lock($name);
        
        $lock->lock();
        $lock->unlock();
        $this->assertTrue($checkLock->nonblockingLock());
        
        $checkLock->unlock();
    }
}
