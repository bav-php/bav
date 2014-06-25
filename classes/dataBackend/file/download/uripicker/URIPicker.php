<?php

namespace malkusch\bav;

/**
 * Finds the download URI in the Bundesbank HTML page.
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 */
interface URIPicker
{

    /**
     * Returns true if this implementation is available on this platform.
     *
     * @return bool
     */
    public function isAvailable();

    /**
     * Returns the download URI from the Bundesbank html page.
     * 
     * @throws URIPickerException
     * @return string
     */
    public function pickURI($html);
}
