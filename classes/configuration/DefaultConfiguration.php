<?php

namespace malkusch\bav;

/**
 * Default configuration uses FileDataBackendContainer and any available UTF-8 encoder.
 * 
 * Automatic installation is enabled.
 * 
 * The update plan is set to LogUpdatePlan.
 * 
 * If no UTF-8 encoding is supported ISO-8859-15 will be used.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 */
class DefaultConfiguration extends Configuration
{

    public function __construct()
    {
        $this->setAutomaticInstallation(true);

        $this->setUpdatePlan(new LogUpdatePlan());

        $this->setDataBackendContainer(new FileDataBackendContainer());

        $encoding = null;
        try {
            $encoding = Encoding::getInstance("UTF-8");

        } catch (UnsupportedEncodingException $e) {
            trigger_error("UTF-8 is not supported; bav is falling back to ISO-8859-15", E_WARNING);
            $encoding = Encoding::getInstance("ISO-8859-15");

        }
        $this->setEncoding($encoding);
    }
}
