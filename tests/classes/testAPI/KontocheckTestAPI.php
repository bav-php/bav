<?php

namespace malkusch\bav;

/**
 * The API for Michael Plugge's kontocheck.
 *
 * @license WTFPL
 * @author Markus Malkusch <markus@malkusch.de>
 * @link http://sourceforge.net/projects/kontocheck/
 */
class KontocheckTestAPI extends TestAPI
{

    const NOT_INITIALIZED = -40;
    const BANK_NOT_FOUND  = -4;
    const INVALID_NULL    = -12;
    const INVALID_KTO     = -3;
    const INVALID_FALSE   =  0;

    /**
     * @param String $lutFile
     * @param int $lutVersion
     * @throws TestAPIException
     * @throws TestAPIUnavailableException
     */
    public function __construct($lutFile = null, $lutVersion = null)
    {
        parent::__construct();

        $this->setName("kc");

        if (is_null($lutFile)) {
            $lutFile = __DIR__ . "/../../data/blz.lut2";

        }

        if (is_null($lutVersion)) {
            $lutVersion = 2;

        }

        if (! lut_init($lutFile, $lutVersion)) {
            throw new TestAPIException("Could not initialize LUT.");

        }
    }

    /**
     * Return true for known false positives.
     *
     * @return true
     */
    public function ignoreTestCase(Bank $bank, $account)
    {
        // http://sourceforge.net/p/kontocheck/bugs/11/
        if ($bank->getBankID() == "80063508") {
            return true;

        }

        // http://sourceforge.net/p/kontocheck/bugs/12/
        if ($bank->getValidationType() == "90") {
            return true;

        }

        return parent::ignoreTestCase($bank, $account);
    }

    /**
     * Returns true if the API is available.
     *
     * @return bool
     */
    protected function isAvailable()
    {
        return function_exists("lut_init");
    }

    /**
     * @param int $bank
     * @param int $account
     * @return bool
     * @throws ValidationTestAPIException
     * @throws NotInitializedTestAPIException
     * @throws BankNotFoundTestAPIException
     */
    protected function isValid(Bank $bank, $account)
    {
        $isValid = kto_check_blz($bank->getBankID(), $account);

        switch ($isValid) {
            case self::NOT_INITIALIZED:
                throw new NotInitializedTestAPIException("LUT not initialized");

            case self::BANK_NOT_FOUND:
                throw new BankNotFoundTestAPIException($bank->getBankID());

            case self::INVALID_NULL:
            case self::INVALID_KTO:
            case self::INVALID_FALSE:
                return false;

            default:
                if ($isValid < 0) {
                    throw new ValidationTestAPIException("unknown code $isValid");

                }
                return true;

        }
    }
}
