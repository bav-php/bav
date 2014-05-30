<?php

namespace malkusch\bav;

/**
 * Perform an update automatically.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @license GPL
 */
class AutomaticUpdatePlan extends UpdatePlan
{

    /**
     * @var bool
     */
    private $notice = true;

    /**
     * Set to false if you don't want to see an E_USER_NOTICE about an update.
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
        // TODO lock concurrent updates
        $backend->update();
        if ($this->notice) {
            trigger_error("bav's bank data was updated sucessfully.", E_USER_NOTICE);

        }
    }
}
