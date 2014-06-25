<?php

namespace malkusch\bav;

/**
 * Logs a E_USER_WARNING if an update should be performed.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 */
class LogUpdatePlan extends UpdatePlan
{

    /**
     * Log an E_USER_WARNING
     */
    public function perform(DataBackend $backend)
    {
        trigger_error(
            "bav's bank data is outdated. Update the data with e.g. bin/bav-update.php",
            E_USER_WARNING
        );
    }
}
