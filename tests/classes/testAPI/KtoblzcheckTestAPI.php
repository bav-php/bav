<?php

namespace malkusch\bav;

/**
 * The API for ktoblzcheck
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @license GPL
 * @link http://sourceforge.net/projects/ktoblzcheck/
 */
class KtoblzcheckTestAPI extends TestAPI
{

    const BINARY            = "ktoblzcheck";
    const VALID             = 0;
    const INVALID           = 2;
    const BANK_NOT_FOUND    = 3;

    /**
     * @var String
     */
    private $binary = '';

    /**
     * @var String
     */
    private $bankdata = '';

    /**
     * @param String $bankdata
     * @param String $binary
     * @throws TestAPIException
     */
    public function __construct($bankdata = null, $binary = null)
    {
        if (! is_null($bankdata)) {
            $this->bankdata = realpath($bankdata);

        }
        $this->binary = is_null($binary) ? self::BINARY : realpath($binary);

        parent::__construct();
        $this->setName("ktoblzcheck");
    }

    /**
     * Return true for known false positives.
     * 
     * @return true
     */
    public function ignoreTestCase(Bank $bank, $account)
    {
        if ($account == 0) {
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
        exec("$this->binary --version", $out, $result);
        return $result === 0;
    }

    /**
     * @param int $account
     * @return bool
     * @throws ValidationTestAPIException
     * @throws NotInitializedTestAPIException
     * @throws BankNotFoundTestAPIException
     */
    protected function isValid(Bank $bank, $account)
    {
        $fileParam = empty($this->bankdata) ? '' : "--file=$this->bankdata";
        $cmd = "$this->binary $fileParam '{$bank->getBankID()}' '$account'";
        exec($cmd, $out, $result);

        switch ($result) {

            case self::VALID:
                return true;

            case self::INVALID:
                return false;

            case self::BANK_NOT_FOUND:
                throw new BankNotFoundTestAPIException("Bank not found: {$bank->getBankID()}");

            default:
                throw new ValidationTestAPIException("unknown code $result: " . implode("\n", $out));

        }
    }
}
