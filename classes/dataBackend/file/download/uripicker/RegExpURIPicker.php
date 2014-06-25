<?php

namespace malkusch\bav;

/**
 * Finds the download URI in the Bundesbank HTML page with a regular expression.
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 */
class RegExpURIPicker implements URIPicker
{

    /**
     * Returns true if this implementation is available on this platform.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return function_exists("preg_match");
    }

    /**
     * Returns the download URI from the Bundesbank html page.
     * 
     * @throws URIPickerException
     * @return string
     */
    public function pickURI($html)
    {
        $isMatch = preg_match(
            '/Bankleitzahlendateien ungepackt.+href *= *"([^"]+\.txt[^"]*)"/sU',
            $html,
            $txtMatches
        );
        if (! $isMatch) {
            throw new URIPickerException("Did not find download URI");

        }
        return $txtMatches[1];
    }
}
