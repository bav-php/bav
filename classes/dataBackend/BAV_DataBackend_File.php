<?php














/**
 * It uses the huge file from the Bundesbank and uses a binary search to find a row.
 * This is the easiest way to use BAV. BAV can work as a standalone application without
 * any DBS.
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
 * @FIXME BAV_DataBackend_File is broken as Bundesbank appends new Banks at the end of the file
 */
class BAV_DataBackend_File extends BAV_DataBackend {


    const DOWNLOAD_URI = "http://www.bundesbank.de/Redaktion/DE/Standardartikel/Kerngeschaeftsfelder/Unbarer_Zahlungsverkehr/bankleitzahlen_download.html";


    private
    /**
     * @var array
     */
    $contextCache = array(),
    /**
     * @var BAV_FileParser
     */
    $parser;


    /**
     * @param String $file The data source
     */
    public function __construct($file = null) {
        $this->parser = new BAV_FileParser($file);
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
     * @throws BAV_DataBackendException_IO
     */
    private function sortFile($file) {
        //read the unordered bank file
        $lines = file($file);
        if (! is_array($lines) || empty($lines)) {
            throw new BAV_DataBackendException_IO("Could not read lines in '$file'.");
            
        }
        
        //build a sorted index for the bankIDs
        $index = array();
        foreach ($lines as $line => $data) {
            $bankID = substr($data, BAV_FileParser::BANKID_OFFSET, BAV_FileParser::BANKID_LENGTH);
            $index[$line] = $bankID;
            
        }
        asort($index);
        
        //write a sorted bank file atomically
        $temp    = tempnam(self::getTempdir(), "BAV_");
        $tempH   = fopen($temp, 'w');
        if (! ($temp && $tempH)) {
            throw new BAV_DataBackendException_IO("Could not open a temporary file.");
        
        }
        foreach (array_keys($index) as $line) {
            $data = $lines[$line];
            
            $writtenBytes = fputs($tempH, $data);
            if ($writtenBytes != strlen($data)) {
                throw new BAV_DataBackendException_IO("Could not write sorted data: '$data' into $temp.");
                
            }
            
        }
        fclose($tempH);
        $this->safeRename($temp, $file);
    }
    
    
    /**
     * @see BAV_DataBackend::uninstall()
     * @throws BAV_DataBackendException_IO
     */
    public function uninstall() {
        if (! unlink($this->parser->getFile())) {
            throw new BAV_DataBackendException_IO();
        
        }
    }
    /**
     * @see BAV_DataBackend::install()
     * @throws BAV_DataBackendException_IO
     */
    public function install() {
        $this->update();
    }
    /**
     * This method works only if your PHP is compiled with cURL.
     * TODO: test this with a proxy
     * 
     * @see BAV_DataBackend::update()
     * @throws BAV_DataBackendException_IO
     */
    public function update() {
        $ch = curl_init(self::DOWNLOAD_URI);
        if (! is_resource($ch)) {
            throw new BAV_DataBackendException_IO();
            
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($ch);
        $curl_info = curl_getinfo($ch);
        if ($curl_info['http_code'] >= 400) {
            throw new BAV_DataBackendException_IO(
                sprintf(
                    "Failed to download '%s'. HTTP Code: %d",
                    self::DOWNLOAD_URI,
                    $curl_info['http_code']
                )
            );
        }
        if (! $content) {
            throw new BAV_DataBackendException_IO(
                "Failed to download '" . self::DOWNLOAD_URI . "'."
            );
        
        }
        $isTXT = preg_match('/Bankleitzahlendateien ungepackt.+href *= *"([^"]+\.txt[^"]*)"/sU', $content, $txtMatches);
        $isZIP = (exec('unzip -v') == '')
               ? false
               : preg_match('/Bankleitzahlendateien gepackt.+href *= *"([^"]+\.zip[^"]*)"/sU', $content, $zipMatches);
               
        /**
         * There is an unresolved bug, that doesn't allow to uncompress
         * the zip archive. Zip support is disabled until it's repaired.
         * 
         * @see http://sourceforge.net/forum/message.php?msg_id=7555232
         * TODO enable Zip support
         */
        $isZIP = false;
               
        if (! ($isTXT || $isZIP)) {
            throw new BAV_DataBackendException("Could not find a file.");
        
        }
        
        $temp    = tempnam(self::getTempdir(), "BAV_");
        $tempH   = fopen($temp, 'w');
        if (! ($temp && $tempH)) {
            throw new BAV_DataBackendException_IO();
        
        }
        $path = $isZIP ? $zipMatches[1] : $txtMatches[1];
        if (strlen($path) > 0 && $path{0} != "/") {
            $path = sprintf("/%s/%s", dirname(self::DOWNLOAD_URI), $path);
            
        }
        $pathParts = explode('/', $path);
        foreach($pathParts as $i => $part) {
            switch ($part) {
                case '..':
                    unset($pathParts[$i-1]);
                    
                case '.':
                    unset($pathParts[$i]);
                    break;
            }

        }
        $path = implode('/', $pathParts);
        $urlParts = parse_url(self::DOWNLOAD_URI);
        $url = sprintf("%s://%s%s", $urlParts["scheme"],  $urlParts["host"], $path);
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FILE, $tempH);
        if (! curl_exec($ch)) {
            fclose($tempH);
            unlink($temp);
            throw new BAV_DataBackendException_IO(
                curl_error($ch), curl_errno($ch)
            );
        
        }
        fclose($tempH);
        curl_close($ch);

        if ($isZIP) {
            $file = tempnam(self::getTempdir(), "BAV_");
            if (! $file) {
                unlink($temp);
                throw new BAV_DataBackendException_IO();
            
            }
            system('unzip -qqp '.$temp.' > '.$file, $error);
            if (! unlink($temp) || $error !== 0) {
                unlink($file);
                throw new BAV_DataBackendException_IO();
            
            }
        
        } else {
            $file = $temp;
        
        }

        // blz_20100308.txt is not sorted.
        $parser     = new BAV_FileParser($file);
        $lastBankID = $parser->getBankID($parser->getLines());
        if ($lastBankID < 80000000) {
            $this->sortFile($file);

        }

        $this->safeRename($file, $this->parser->getFile());
    }


    /**
     * Renames a file atomically between different filesystems.
     *
     * @param String $source path of the source
     * @param String $destination path of the destination
     * @throws BAV_DataBackendException_IO
     */
    private function safeRename($source, $destination) {
        $isRenamed = @rename($source, $destination);
        if ($isRenamed) {
            return;

        }

        // copy to the target filesystem
        $tempFileOnSameFS = "$destination.tmp";

        $isCopied = copy($source, $tempFileOnSameFS);
        if (! $isCopied) {
            throw new BAV_DataBackendException_IO(
                "failed to copy $source to $tempFileOnSameFS."
            );

        }

        $isUnlinked = unlink($source);
        if (! $isUnlinked) {
            trigger_error("Failed to unlink $source.");

        }

        $isRenamed = rename($tempFileOnSameFS, $destination);
        if (! $isRenamed) {
            throw new BAV_DataBackendException_IO(
                "failed to rename $tempFileOnSameFS to $destination."
            );

        }
    }


    /**
     * @throws BAV_DataBackendException_IO
     * @throws BAV_DataBackendException
     * @return array
     * @see BAV_DataBackend::getAllBanks()
     */
    public function getAllBanks() {
        try {
            for ($i = 0; $i < $this->parser->getLines(); $i++) {
                if (isset($this->instances[$this->parser->getBankID($i)])) {
                    continue;
                
                }
                $line = $this->parser->readLine($i);
                $bank = $this->parser->getBank($this, $line);
                $this->instances[$bank->getBankID()] = $bank;
                $this->contextCache[$bank->getBankID()] = new BAV_FileParserContext($i);
            }
            return array_values($this->instances);
            
        } catch (BAV_FileParserException_IO $e) {
            throw new BAV_DataBackendException_IO();
        
        } catch (BAV_FileParserException $e) {
            throw new BAV_DataBackendException();
        
        }
    }
    /**
     * @throws BAV_DataBackendException_IO
     * @throws BAV_DataBackendException_BankNotFound
     * @param String $bankID
     * @see BAV_DataBackend::getNewBank()
     * @return BAV_Bank
     */
    public function getNewBank($bankID) {
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
                            $this->contextCache[$bankID]->getLine());
        
            } else {
                return $this->findBank($bankID, 0, $this->parser->getLines());
                
            }
            
        } catch (BAV_FileParserException $e) {
            throw new BAV_DataBackendException_IO();
        
        }
    }
    /**
     * @throws BAV_DataBackendException_BankNotFound
     * @throws BAV_FileParserException_ParseError
     * @throws BAV_FileParserException_IO
     * @param int $bankID
     * @param int $offset the line number to start
     * @param int $length the line count
     * @return BAV_Bank
     */
    private function findBank($bankID, $offset, $end) {
        if ($end - $offset < 0) {
            throw new BAV_DataBackendException_BankNotFound($bankID);
        
        }
        $line = $offset + (int)(($end - $offset) / 2);
        $blz  = $this->parser->getBankID($line);
        
        /**
         * This handling is bad, as it may double the work
         */
        if ($blz == '00000000') {
            try { 
                return $this->findBank($bankID, $offset, $line - 1);
                
            } catch (BAV_DataBackendException_BankNotFound $e) {
                return $this->findBank($bankID, $line + 1, $end);
            
            }
            
        } elseif (! isset($this->contextCache[$blz])) {
           $this->contextCache[$blz] = new BAV_FileParserContext($line);

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
     * @see BAV_DataBackend::_getMainAgency()
     * @throws BAV_DataBackendException
     * @throws BAV_DataBackendException_NoMainAgency
     * @return BAV_Agency
     */
    public function _getMainAgency(BAV_Bank $bank) {
        try {
            $context = $this->defineContextInterval($bank->getBankID());
            for ($line = $context->getStart(); $line <= $context->getEnd(); $line++) {
                $content = $this->parser->readLine($line);
                if ($this->parser->isMainAgency($content)) {
                    return $this->parser->getAgency($bank, $content);
                
                }
            }
            // Maybe there are banks without a main agency
            throw new BAV_DataBackendException_NoMainAgency($bank);
            
        } catch (BAV_FileParserContextException_Undefined $e) {
            throw new LogicException("Start and end should be defined.");
        
        } catch (BAV_FileParserException_IO $e) {
            throw new BAV_DataBackendException_IO("Parser Exception at bank {$bank->getBankID()}");
        
        } catch (BAV_FileParserException_ParseError $e) {
            throw new BAV_DataBackendException(get_class($e) . ": " . $e->getMessage());
            
        }
    }
    /**
     * @see BAV_DataBackend::_getAgencies()
     * @throws BAV_DataBackendException_IO
     * @throws BAV_DataBackendException
     * @return array
     */
    public function _getAgencies(BAV_Bank $bank) {
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
            
        } catch (BAV_FileParserContextException_Undefined $e) {
            throw new LogicException("Start and end should be defined.");
        
        } catch (BAV_FileParserException_IO $e) {
            throw new BAV_DataBackendException_IO();
        
        } catch (BAV_FileParserException_ParseError $e) {
            throw new BAV_DataBackendException();
            
        }
    }
    
    
    /**
     * @return BAV_FileParserContext
     */
    private function defineContextInterval($bankID) {
        if (! isset($this->contextCache[$bankID])) {
            throw new LogicException("The contextCache object should exist!");

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
     * @throws BAV_DataBackendException_IO
     * @return String a writable directory for temporary files
     */
    public static function getTempdir() {
        $tmpDirs = array(
            function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : false,
            empty($_ENV['TMP'])    ? false : $_ENV['TMP'],
            empty($_ENV['TMPDIR']) ? false : $_ENV['TMPDIR'],
            empty($_ENV['TEMP'])   ? false : $_ENV['TEMP'],
            ini_get('upload_tmp_dir'),
            '/tmp'
        );
        
        foreach ($tmpDirs as $tmpDir) {
        	if ($tmpDir && is_writable($tmpDir)) {
        		return realpath($tmpDir);
        		
        	}
        	
        }
        
        $tempfile = tempnam(uniqid(mt_rand(), true), '');
        if (file_exists($tempfile)) {
            unlink($tempfile);
            return realpath(dirname($tempfile));
            
        }
        
        throw new BAV_DataBackendException_IO();
    }
    

}