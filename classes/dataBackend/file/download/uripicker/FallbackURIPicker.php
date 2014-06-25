<?php

namespace malkusch\bav;

/**
 * Finds the download URI in the Bundesbank HTML page with any URI picker.
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 */
class FallbackURIPicker implements URIPicker
{

    /**
     * @var URIPicker[]
     */
    private $pickers = array();

    /**
     * Construct available pickers.
     */
    public function __construct()
    {
        $domPicker = new DOMURIPicker();
        if ($domPicker->isAvailable()) {
            $this->pickers[] = $domPicker;

        }

        $regExpPicker = new RegExpURIPicker();
        if ($regExpPicker->isAvailable()) {
            $this->pickers[] = $regExpPicker;

        }
    }

    /**
     * Returns true if this implementation is available on this platform.
     *
     * @return bool
     */
    public function isAvailable()
    {
        return ! empty($this->pickers);
    }

    /**
     * Returns the download URI from the Bundesbank html page.
     * 
     * @throws URIPickerException
     * @return string
     */
    public function pickURI($html)
    {
        $exception = null;
        foreach ($this->pickers as $picker) {
            try {
                return $picker->pickURI($html);

            } catch (URIPickerException $e) {
                $exception = $e;

            }
        }
        throw $exception;
    }
}
