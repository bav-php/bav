<?php

namespace malkusch\bav;

/**
 * Perform an update automatically.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 */
class AutomaticUpdatePlan extends UpdatePlan
{

    /**
     * @var string Name of the update lock file
     */
    const UPDATE_LOCK = "bav_update.lock";

    /**
     * @var bool
     */
    private $notice = true;

    /**
     * Set to false if you don't want to see an E_USER_NOTICE about an update.
     * 
     * @param bool $notice
     */
    public function setNotice($notice)
    {
        $this->notice = $notice;
    }

    /**
     * Perform an update.
     * 
     * If enabled this method will send a E_USER_NOTICE about the update.
     * 
     * @see setNotice()
     */
    public function perform(DataBackend $backend)
    {
        $isNotice = $this->notice;
        $lock = new Lock(self::UPDATE_LOCK);
        $lock->nonblockingExecuteOnce(
            function () use ($backend, $isNotice) {
                $backend->update();
                if ($isNotice) {
                    trigger_error("bav's bank data was updated sucessfully.", E_USER_NOTICE);

                }
            }
        );
    }
}
