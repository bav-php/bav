<?php

namespace malkusch\bav;

/**
 * It uses the huge file from the Bundesbank and uses a binary search to find a row.
 * This is the easiest way to use BAV. BAV can work as a standalone application without
 * any DBS.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 */
class FileDataBackend extends DataBackend
{
    
    // @codingStandardsIgnoreStart
    const DOWNLOAD_URI = "http://www.bundesbank.de/Redaktion/DE/Standardartikel/Aufgaben/Unbarer_Zahlungsverkehr/bankleitzahlen_download.html";
    // @codingStandardsIgnoreEnd

    /**
     * @var array
     */
    private $contextCache = array();

    /**
     * @var FileParser
     */
    private $parser;

    /**
     * @var FileUtil
     */
    private $fileUtil;

    /**
     * @param String $file The data source
     */
    public function __construct($file = null)
    {
        $this->parser = new FileParser($file);
        $this->fileUtil = new FileUtil();
    }

    /**
     * Returns the path to the data file.
     *
     * @return string
     */
    public function getFile()
    {
        return $this->parser->getFile();
    }

    /**
     * For the file of March 8th 2010 (blz_20100308.txt)
     * Bundesbank appended new banks at the end of the file.
     * That broked binary search. This method sorts the lines so
     * that binary search is working again.
     *
     * Be aware that this needs some amount of memory.
     *
     * @param String $file
     * @throws DataBackendIOException
     * @throws FileException
     */
    private function sortFile($file)
    {
        //read the unordered bank file
        $lines = file($file);
        if (! is_array($lines) || empty($lines)) {
            throw new DataBackendIOException("Could not read lines in '$file'.");

        }

        //build a sorted index for the bankIDs
        $index = array();
        foreach ($lines as $line => $data) {
            $bankID = substr($data, FileParser::BANKID_OFFSET, FileParser::BANKID_LENGTH);
            $index[$line] = $bankID;

        }
        asort($index);

        //write a sorted bank file atomically
        $temp    = tempnam($this->fileUtil->getTempDirectory(), "");
        $tempH   = fopen($temp, 'w');
        if (! ($temp && $tempH)) {
            throw new DataBackendIOException("Could not open a temporary file.");

        }
        foreach (array_keys($index) as $line) {
            $data = $lines[$line];

            $writtenBytes = fputs($tempH, $data);
            if ($writtenBytes != strlen($data)) {
                throw new DataBackendIOException("Could not write sorted data: '$data' into $temp.");

            }

        }
        fclose($tempH);
        $this->fileUtil->safeRename($temp, $file);
    }

    /**
     * @see DataBackend::uninstall()
     * @throws DataBackendIOException
     */
    public function uninstall()
    {
        if (! unlink($this->parser->getFile())) {
            throw new DataBackendIOException();

        }
    }

    /**
     * @see DataBackend::install()
     * @throws DataBackendIOException
     */
    public function install()
    {
        $this->update();
    }

    /**
     * This method works only if your PHP is compiled with cURL.
     *
     * @see DataBackend::update()
     * @throws DataBackendIOException
     * @throws FileException
     * @throws DownloaderException
     */
    public function update()
    {
        $downloader = new Downloader();
        $content = $downloader->downloadContent(self::DOWNLOAD_URI);

        $uriPicker = new FallbackURIPicker();
        $path = $uriPicker->pickURI($content);

        if (strlen($path) > 0 && $path{0} != "/") {
            $path = sprintf("/%s/%s", dirname(self::DOWNLOAD_URI), $path);

        }
        $pathParts = explode('/', $path);
        foreach ($pathParts as $i => $part) {
            switch ($part) {
                case '..':
                    unset($pathParts[$i-1]);
                    // fall-through as the current part ("..") should be removed as well.

                case '.':
                    unset($pathParts[$i]);
                    break;
            }

        }
        $path = implode('/', $pathParts);
        $urlParts = parse_url(self::DOWNLOAD_URI);
        $url = sprintf("%s://%s%s", $urlParts["scheme"], $urlParts["host"], $path);

        // download file
        $file = $downloader->downloadFile($url);

        // Validate file format.
        $validator = new FileValidator();
        $validator->validate($file);

        // blz_20100308.txt is not sorted.
        $parser     = new FileParser($file);
        $lastBankID = $parser->getBankID($parser->getLines());
        if ($lastBankID < 80000000) {
            $this->sortFile($file);

        }

        $this->fileUtil->safeRename($file, $this->parser->getFile());
        chmod($this->parser->getFile(), 0644);
    }

    /**
     * @throws DataBackendIOException
     * @throws DataBackendException
     * @return Bank[]
     * @see DataBackend::getAllBanks()
     */
    public function getAllBanks()
    {
        try {
            for ($i = 0; $i < $this->parser->getLines(); $i++) {
                if (isset($this->instances[$this->parser->getBankID($i)])) {
                    continue;

                }
                $line = $this->parser->readLine($i);
                $bank = $this->parser->getBank($this, $line);
                $this->instances[$bank->getBankID()] = $bank;
                $this->contextCache[$bank->getBankID()] = new FileParserContext($i);
            }
            return array_values($this->instances);

        } catch (FileParserIOException $e) {
            throw new DataBackendIOException();

        } catch (FileParserException $e) {
            throw new DataBackendException();

        }
    }

    /**
     * @throws DataBackendIOException
     * @throws BankNotFoundException
     * @param String $bankID
     * @see DataBackend::getNewBank()
     * @return Bank
     */
    public function getNewBank($bankID)
    {
        try {
            $this->parser->rewind();
            /**
             * TODO Binary Search is also possible on $this->contextCache,
             *      to reduce the interval of $offset and $end;
             */
            if (isset($this->contextCache[$bankID])) {
                return $this->findBank(
                    $bankID,
                    $this->contextCache[$bankID]->getLine(),
                    $this->contextCache[$bankID]->getLine()
                );

            } else {
                return $this->findBank($bankID, 0, $this->parser->getLines());

            }

        } catch (FileParserException $e) {
            throw new DataBackendIOException();

        }
    }

    /**
     * @throws BankNotFoundException
     * @throws ParseException
     * @throws FileParserIOException
     * @param int $bankID
     * @param int $offset the line number to start
     * @param int $length the line count
     * @return Bank
     */
    private function findBank($bankID, $offset, $end)
    {
        if ($end - $offset < 0) {
            throw new BankNotFoundException($bankID);

        }
        $line = $offset + (int)(($end - $offset) / 2);
        $blz  = $this->parser->getBankID($line);

        /**
         * This handling is bad, as it may double the work
         */
        if ($blz == '00000000') {
            try {
                return $this->findBank($bankID, $offset, $line - 1);

            } catch (BankNotFoundException $e) {
                return $this->findBank($bankID, $line + 1, $end);

            }

        } elseif (! isset($this->contextCache[$blz])) {
            $this->contextCache[$blz] = new FileParserContext($line);

        }

        if ($blz < $bankID) {
            return $this->findBank($bankID, $line + 1, $end);

        } elseif ($blz > $bankID) {
            return $this->findBank($bankID, $offset, $line - 1);

        } else {
            return $this->parser->getBank($this, $this->parser->readLine($line));

        }
    }

    /**
     * @see DataBackend::getMainAgency()
     * @throws DataBackendException
     * @throws NoMainAgencyException
     * @return Agency
     */
    public function getMainAgency(Bank $bank)
    {
        try {
            $context = $this->defineContextInterval($bank->getBankID());
            for ($line = $context->getStart(); $line <= $context->getEnd(); $line++) {
                $content = $this->parser->readLine($line);
                if ($this->parser->isMainAgency($content)) {
                    return $this->parser->getAgency($bank, $content);

                }
            }
            // Maybe there are banks without a main agency
            throw new NoMainAgencyException($bank);

        } catch (UndefinedFileParserContextException $e) {
            throw new \LogicException("Start and end should be defined.");

        } catch (FileParserIOException $e) {
            throw new DataBackendIOException("Parser Exception at bank {$bank->getBankID()}");

        } catch (ParseException $e) {
            throw new DataBackendException(get_class($e) . ": " . $e->getMessage());

        }
    }

    /**
     * @see DataBackend::getAgenciesForBank()
     * @throws DataBackendIOException
     * @throws DataBackendException
     * @return Agency[]
     */
    public function getAgenciesForBank(Bank $bank)
    {
        try {
            $context = $this->defineContextInterval($bank->getBankID());
            $agencies = array();
            for ($line = $context->getStart(); $line <= $context->getEnd(); $line++) {
                $content = $this->parser->readLine($line);
                if (! $this->parser->isMainAgency($content)) {
                    $agencies[] = $this->parser->getAgency($bank, $content);

                }
            }
            return $agencies;

        } catch (UndefinedFileParserContextException $e) {
            throw new \LogicException("Start and end should be defined.");

        } catch (FileParserIOException $e) {
            throw new DataBackendIOException();

        } catch (ParseException $e) {
            throw new DataBackendException();

        }
    }

    /**
     * @return FileParserContext
     */
    private function defineContextInterval($bankID)
    {
        if (! isset($this->contextCache[$bankID])) {
            throw new \LogicException("The contextCache object should exist!");

        }
        $context = $this->contextCache[$bankID];
        /**
         * Find start
         */
        if (! $context->isStartDefined()) {
            for ($start = $context->getLine() - 1; $start >= 0; $start--) {
                if ($this->parser->getBankID($start) != $bankID) {
                    break;

                }
            }
            $context->setStart($start + 1);

        }
        /**
         * Find end
         */
        if (! $context->isEndDefined()) {
            for ($end = $context->getLine() + 1; $end <= $this->parser->getLines(); $end++) {
                if ($this->parser->getBankID($end) != $bankID) {
                    break;

                }
            }
            $context->setEnd($end - 1);

        }
        return $context;
    }

    /**
     * Returns the timestamp of the last update.
     *
     * @return int timestamp
     * @throws DataBackendException
     */
    public function getLastUpdate()
    {
        $time = filemtime($this->parser->getFile());
        if ($time === false) {
            return new DataBackendException(
                "Could not read modification time from {$this->parser->getFile()}"
            );

        }
        return $time;
    }

    /**
     * Returns true if the backend was installed.
     *
     * @return bool
     */
    public function isInstalled()
    {
        return file_exists($this->parser->getFile())
            && filesize($this->parser->getFile()) > 0;
    }

    /**
     * Returns bank agencies for a given BIC.
     *
     * @todo This method is inefficient. Add index based implementation.
     * @param string $bic BIC
     * @return Agency[]
     */
    public function getBICAgencies($bic)
    {
        $agencies = array();
        foreach ($this->getAllBanks() as $bank) {
            $bankAgencies = $bank->getAgencies();
            $bankAgencies[] = $bank->getMainAgency();
            foreach ($bankAgencies as $agency) {
                if ($agency->hasBIC() && $agency->getBIC() == $bic) {
                    $agencies[] = $agency;

                }
            }
        }
        return $agencies;
    }
    
    public function free()
    {
        parent::free();
        $this->contextCache = array();
    }
}
