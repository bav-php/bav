<?php

/**
 * This class offers methods for importing a verify.ini
 *
 * import($bankID, $accountID, $isValid = true) adds single
 * accounts. You may specify if the account should be treated
 * as valid or not. Default is valid.
 *
 * After you've added all accounts you should save the file
 * with save($file = null). If you do not specify a file, then
 * it's saved to ../../data/verify.ini. The validation algorithm and
 * the account id is the only data which is saved.
 * Please send this file to Markus Malkusch <markus@malkusch.de>.
 *
 * Copyright (C) 2006  Markus Malkusch <markus@malkusch.de>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * DataBackend
 *
 * @package classes
 * @subpackage verify
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2006 Markus Malkusch
 */
class VerifyImport
{

    /**
     * @var Array Numbers which are valid
     */
    private $validNumbers   = array();

    /**
     * @var Array Numbers which aren't valid
     */
    private $invalidNumbers = array();

    /**
     * @var DataBackend
     */
    private $dataBackend;

    /**
     * @param DataBackend $dataBackend The backend is needed to get the validation algorithm.
     */
    public function __construct(DataBackend $dataBackend)
    {
        $this->dataBackend = $dataBackend;
    }

    /**
     * @return String
     */
    private function getFile($file)
    {
        return is_null($file)
             ? __DIR__.'/../../data/verify.ini'
             : $file;
    }

    /**
     * Imports an existing verify.ini.
     *
     * @param string $file
     * @throws VerifyException
     */
    public function importVerifyFile($file = null)
    {
        $file   = $this->getFile($file);
        $verify = parse_ini_file($file, true);
        if (! $verify) {
            throw new VerifyException("couldn't parse $file");

        }

        $this->mergeVerifyArray($verify['valid'], $this->validNumbers);
        $this->mergeVerifyArray($verify['invalid'], $this->invalidNumbers);
    }

    /**
     * Merges a string array of an existing verify.ini
     */
    private function mergeVerifyArray($verifyArray, & $targetArray)
    {
        if (! is_array($verifyArray)) {
            return;

        }
        foreach ($verifyArray as $type => $string) {
            $type       = (strlen($type) < 2 ? '0' : '').$type;
            $accountIDs = preg_split('~\D+~', $string);
            foreach ($accountIDs as $accountID) {
                $accountID            = $this->normalize($accountID);
                $targetArray[$type][] = $accountID;

            }

        }
    }

    /**
     * @param string $bankID
     * @param string $accountID
     * @param bool $isValid if $accountID should be valid or not. Defaults to TRUE for valid.
     * @throws DataBackendException_BankNotFound
     * @throws DataBackendException
     */
    public function import($bankID, $accountID, $isValid = true)
    {
        $bankID     = $this->normalize($bankID);
        $accountID  = $this->normalize($accountID);
        $bank       = $this->dataBackend->getBank($bankID);
        $type       = $bank->getValidator() instanceof Validator_BankDependent
                    ? $bankID
                    : $bank->getValidationType();
        if ($isValid) {
            $this->validNumbers[$type][] = $accountID;

        } else {
            $this->invalidNumbers[$type][] = $accountID;

        }
    }

    /**
     * Removes all none numeric characters from $id.
     *
     * @param string $id
     * @return string
     */
    private function normalize($id)
    {
        return (string) preg_replace('~\D+~', '', $id);
    }

    /**
     * @param string $filePath The file where the arrays are saved (default's to ../../data/verify.ini)
     * @throws VerifyException_IO
     */
    public function save($file = null)
    {
        $file = $this->getFile($file);
        $fp   = fopen($file, 'w');
        if (! is_resource($fp)) {
            throw new VerifyException_IO("Could not open $file.");

        }
        try {
            $this->saveArray($fp, $this->invalidNumbers, 'invalid');
            $this->saveArray($fp, $this->validNumbers, 'valid');
            fclose($fp);

        } catch (VerifyException_IO $e) {
            fclose($fp);
            throw $e;

        }
    }

    /**
     * @param Resource $fp Filepointer
     * @param Array $array Array with bank IDs
     * @param String $name Name of the section
     * @throws VerifyException_IO
     */
    private function saveArray($fp, Array $array, $name)
    {
        if (! fwrite($fp, "\n\n".'['.$name.']')) {
            throw new VerifyException_IO();

        }
        ksort($array);
        foreach ($array as $type => $numbers) {
            $type = ltrim($type, '0');
            if (empty($type)) {
                $type = '0';

            }
            if (! fwrite($fp, "\n".'    '.$type.' = ')) {
                throw new VerifyException_IO();

            }
            $line = implode(', ', array_unique($numbers));
            if (fwrite($fp, $line, strlen($line)) !== strlen($line)) {
                throw new VerifyException_IO();

            }

        }
    }

    /**
     * @param String $bankID
     * @return String
     * @throws DataBackendException_BankNotFound
     * @throws DataBackendException
     */
    private function bankIDToType($bankID)
    {
        return $this->dataBackend->getBank($bankID)->getValidationType();
    }
}
