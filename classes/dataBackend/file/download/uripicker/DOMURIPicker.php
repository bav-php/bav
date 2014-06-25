<?php

namespace malkusch\bav;

/**
 * Finds the download URI in the Bundesbank HTML page with XPath.
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 */
class DOMURIPicker implements URIPicker
{

    /**
     * Returns true if this implementation is available on this platform.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return class_exists("\DOMXPath");
    }

    /**
     * Returns the download URI from the Bundesbank html page.
     * 
     * @throws URIPickerException
     * @return string
     */
    public function pickURI($html)
    {
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);

        $xpath = new \DOMXpath($doc);

        $result = $xpath->query(
            "(//a[contains(text(), 'Bankleitzahlendateien') and contains(@href, '.txt')]/@href)[1]"
        );
        if ($result->length != 1) {
            throw new URIPickerException("Did not find download URI");

        }
        return $result->item(0)->value;
    }
}
