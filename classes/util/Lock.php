<?php

namespace malkusch\bav;

/**
 * Helper for locking
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 * @see DataBackend
 */
class Lock
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var resource
     */
    private $handle;

    /**
     * @param string $name lock name
     * @throws LockException
     */
    public function __construct($name)
    {
        $this->name = $name;

        $fileUtil = new FileUtil();
        $lockFile = $fileUtil->getTempDirectory() . DIRECTORY_SEPARATOR . $name;

        $this->handle = fopen($lockFile, "w");
        if (! is_resource($this->handle)) {
            throw new LockException("Could not open lock file $lockFile.");

        }
    }

    /**
     * Get a lock and execute a task.
     *
     * If more processes call this method only the process which aquired the lock
     * will execute the task. The others will block but won't execute the task.
     * 
     * @throws Exception
     */
    public function executeOnce(\Closure $task)
    {
        $isBlocked = $this->checkedLock();
        $error = null;
        try {
            if (! $isBlocked) {
                call_user_func($task);

            }
        } catch (\Exception $e) {
            $error = $e;

        }
        $this->unlock();
        if (! is_null($error)) {
            throw $error;

        }
    }

    /**
     * Get a lock and execute a task only if the lock was aquired.
     *
     * If more processes call this method only the process which aquired the lock
     * will execute the task. The others will continue execution.
     * 
     * @throws Exception
     */
    public function nonblockingExecuteOnce(\Closure $task)
    {
        if (! $this->nonblockingLock()) {
            return;

        }
        $error = null;
        try {
            call_user_func($task);

        } catch (\Exception $e) {
            $error = $e;

        }
        $this->unlock();
        if (! is_null($error)) {
            throw $error;

        }
    }

    /**
     * Blocking lock
     *
     * @throws LockException
     */
    public function lock()
    {
        if (! flock($this->handle, LOCK_EX)) {
            throw new LockException("flock() failed for {$this->name}.");

        }
    }

    /**
     * Nonblocking lock which returns if the lock was aquired
     *
     * @return bool true if the lock was aquired.
     */
    public function nonblockingLock()
    {
        return flock($this->handle, LOCK_EX | LOCK_NB);
    }

    /**
     * Blocking lock which returns if the lock did block.
     *
     * @throws LockException
     * @return bool true if the lock was blocked.
     */
    public function checkedLock()
    {
        $isLocked = ! flock($this->handle, LOCK_EX | LOCK_NB);
        if ($isLocked) {
            $this->lock();

        }
        return $isLocked;
    }

    /**
     * @throws LockException
     */
    public function unlock()
    {
        if (! flock($this->handle, LOCK_UN)) {
            throw new LockException("flock() failed releasing {$this->name}.");

        }
    }
}
