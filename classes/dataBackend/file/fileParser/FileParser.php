<?php

namespace malkusch\bav;

/**
 * This class is responsable for I/O and formating which helps the FileDataBackend.
 *
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
 *
 *
 * @package classes
 * @subpackage dataBackend
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @copyright Copyright (C) 2006 Markus Malkusch
 */
class FileParser
{

    const FILE_ENCODING     = 'ISO-8859-15';
    const BANKID_OFFSET     = 0; // field 1
    const BANKID_LENGTH     = 8;
    const ISMAIN_OFFSET     = 8; // field 2
    const ISMAIN_LENGTH     = 1;
    const NAME_OFFSET       = 9; // field 3
    const NAME_LENGTH       = 58;
    const POSTCODE_OFFSET   = 67; // field 4
    const POSTCODE_LENGTH   = 5;
    const CITY_OFFSET       = 72; // field 5
    const CITY_LENGTH       = 35;
    const SHORTTERM_OFFSET  = 107; // field 6
    const SHORTTERM_LENGTH  = 27;
    const PAN_OFFSET        = 134; // field 7
    const PAN_LENGTH        = 5;
    const BIC_OFFSET        = 139; // field 8
    const BIC_LENGTH        = 11;
    const TYPE_OFFSET       = 150; // field 9
    const TYPE_LENGTH       = 2;
    const ID_OFFSET         = 152; // field 10
    const ID_LENGTH         = 6;
    const STATE_OFFSET      = 158; // field 11
    const STATE_LENGTH      = 1;
    const DELETE_OFFSET     = 159; // field 12
    const DELETE_LENGTH     = 1;
    const SUCCESSOR_OFFSET  = 160; // field 13
    const SUCCESSOR_LENGTH  = 8;

    /**
     * This field is only accessible from a new version of the file.
     * The new version is only accessible from the Bundesbank ExtraNet.
     */
    const IBAN_RULE_OFFSET = 168; // field 14 Rule
    const IBAN_RULE_LENGTH = 4;
    const IBAN_VERSION_OFFSET = 172; // field 14 Version
    const IBAN_VERSION_LENGTH = 2;

    /**
     * @var resource
     */
    private $fp;
    
    /**
     * @var string
     */
    private $file = '';

    /**
     * @var int,
     */
    private $lines = 0;

    /**
     * @var int
     */
    private $lineLength = 0;

    /**
     * @var Encoding
     */
    private $encoding;

    /**
     * @param String $file The data source
     */
    public function __construct($file = null)
    {
        $defaultFile =
            __DIR__ . DIRECTORY_SEPARATOR . ".."
            . DIRECTORY_SEPARATOR . ".."
            . DIRECTORY_SEPARATOR . ".."
            . DIRECTORY_SEPARATOR . ".."
            . DIRECTORY_SEPARATOR . "data"
            . DIRECTORY_SEPARATOR . "banklist.txt";

        $this->file = is_null($file) ? $defaultFile: $file;

        $this->encoding = ConfigurationRegistry::getConfiguration()->getEncoding();
    }

    /**
     * @throws FileParserIOException
     * @throws FileParserNotExistsException
     */
    private function init()
    {
        if (is_resource($this->fp)) {
            return;

        }
        $this->fp = @fopen($this->file, 'r');
        if (! is_resource($this->fp)) {
            if (! file_exists($this->file)) {
                throw new FileParserNotExistsException($this->file);

            } else {
                throw new FileParserIOException();

            }

        }


        $dummyLine = fgets($this->fp, 1024);
        if (! $dummyLine) {
            throw new FileParserIOException();

        }
        $this->lineLength = strlen($dummyLine);

        clearstatcache(); // filesize() seems to be 0 sometimes
        $filesize = filesize($this->file);
        if (! $filesize) {
            throw new FileParserIOException(
                "Could not read filesize for '$this->file'."
            );

        }
        $this->lines = floor(($filesize - 1) / $this->lineLength);
    }

    /**
     * @throws FileParserIOException
     * @throws FileParserNotExistsException
     * @return int
     */
    public function getLines()
    {
        $this->init();
        return $this->lines;
    }

    /**
     * @throws FileParserIOException
     * @throws FileParserNotExistsException
     */
    public function rewind()
    {
        if (fseek($this->getFileHandle(), 0) === -1) {
            throw new FileParserIOException();

        }
    }

    /**
     * @throws FileParserIOException
     * @throws FileParserNotExistsException
     * @param int $line
     * @param int $offset
     */
    public function seekLine($line, $offset = 0)
    {
        if (fseek($this->getFileHandle(), $line * $this->lineLength + $offset) === -1) {
            throw new FileParserIOException();

        }
    }

    /**
     * @throws FileParserIOException
     * @throws FileParserNotExistsException
     * @param int $line
     * @return string
     */
    public function readLine($line)
    {
        $this->seekLine($line);
        return $this->encoding->convert(fread($this->getFileHandle(), $this->lineLength), self::FILE_ENCODING);
    }

    /**
     * @throws FileParserIOException
     * @throws FileParserNotExistsException
     * @param int $line
     * @return string
     */
    public function getBankID($line)
    {
        $this->seekLine($line, self::BANKID_OFFSET);
        return $this->encoding->convert(fread($this->getFileHandle(), self::BANKID_LENGTH), self::FILE_ENCODING);
    }

    /**
     * @throws FileParserNotExistsException
     * @throws FileParserIOException
     * @return resource
     */
    public function getFileHandle()
    {
        $this->init();
        return $this->fp;
    }

    /**
     * @throws FileParserNotExistsException
     * @throws FileParserIOException
     * @return int
     */
    public function getLineLength()
    {
        $this->init();
        return $this->lineLength;
    }

    /**
     */
    public function __destruct()
    {
        if (is_resource($this->fp)) {
            fclose($this->fp);

        }
    }

    /**
     * @throws ParseException
     * @param string $line
     * @return Bank
     */
    public function getBank(DataBackend $dataBackend, $line)
    {
        if ($this->encoding->strlen($line) < self::TYPE_OFFSET + self::TYPE_LENGTH) {
            throw new ParseException("Invalid line length in Line $line.");

        }
        $type   = $this->encoding->substr($line, self::TYPE_OFFSET, self::TYPE_LENGTH);
        $bankID = $this->encoding->substr($line, self::BANKID_OFFSET, self::BANKID_LENGTH);
        return new Bank($dataBackend, $bankID, $type);
    }

    /**
     * @throws ParseException
     * @param string $line
     * @return Agency
     */
    public function getAgency(Bank $bank, $line)
    {
        if ($this->encoding->strlen($line) < self::ID_OFFSET + self::ID_LENGTH) {
            throw new ParseException("Invalid line length.");

        }
        $id   = trim($this->encoding->substr($line, self::ID_OFFSET, self::ID_LENGTH));
        $name = trim($this->encoding->substr($line, self::NAME_OFFSET, self::NAME_LENGTH));
        $shortTerm = trim($this->encoding->substr($line, self::SHORTTERM_OFFSET, self::SHORTTERM_LENGTH));
        $city = trim($this->encoding->substr($line, self::CITY_OFFSET, self::CITY_LENGTH));
        $postcode = $this->encoding->substr($line, self::POSTCODE_OFFSET, self::POSTCODE_LENGTH);
        $bic = trim($this->encoding->substr($line, self::BIC_OFFSET, self::BIC_LENGTH));
        $pan = trim($this->encoding->substr($line, self::PAN_OFFSET, self::PAN_LENGTH));
        return new Agency($id, $bank, $name, $shortTerm, $city, $postcode, $bic, $pan);
    }

    /**
     * @throws ParseException
     * @param string $line
     * @return bool
     */
    public function isMainAgency($line)
    {
        if ($this->encoding->strlen($line) < self::TYPE_OFFSET + self::TYPE_LENGTH) {
            throw new ParseException("Invalid line length.");

        }
        return $this->encoding->substr($line, self::ISMAIN_OFFSET, 1) === '1';
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }
}
