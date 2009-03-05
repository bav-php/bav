<?php
BAV_Autoloader::add('../bank/BAV_Bank.php');
BAV_Autoloader::add('BAV_DataBackend.php');
BAV_Autoloader::add('fileParser/BAV_FileParser.php');
BAV_Autoloader::add('fileParser/BAV_FileParserContext.php');
BAV_Autoloader::add('fileParser/exception/BAV_FileParserException.php');
BAV_Autoloader::add('fileParser/exception/BAV_FileParserException_IO.php');
BAV_Autoloader::add('fileParser/exception/BAV_FileParserException_ParseError.php');
BAV_Autoloader::add('fileParser/exception/BAV_FileParserContextException_Undefined.php');
BAV_Autoloader::add('exception/BAV_DataBackendException.php');
BAV_Autoloader::add('exception/BAV_DataBackendException_IO.php');
BAV_Autoloader::add('exception/BAV_DataBackendException_BankNotFound.php');


/**
 * It uses the huge file from the Bundesbank and uses a binary search to find a row.
 * This is the easiest way to use BAV. BAV can work as a standalone application without
 * any DBS.
 *
 * Copyright (C) 2006  Markus Malkusch <bav@malkusch.de>
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
 */
class BAV_DataBackend_File extends BAV_DataBackend {


    const DOWNLOAD_URI = "http://www.bundesbank.de/zahlungsverkehr/zahlungsverkehr_bankleitzahlen_download.php";


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
        if (! $content) {
            throw new BAV_DataBackendException_IO();
        
        }
        $isTXT = preg_match(':Aktuelle Version der Bankleitzahlendateien.+href *= *"([^"]+\.txt)":sU', $content, $txtMatches); 
        $isZIP = (exec('unzip -v') == '')
               ? false
               : preg_match(':Aktuelle Version der Bankleitzahlendateien.+href *= *"([^"]+txt\.zip)":sU', $content, $zipMatches);
        if (! ($isTXT || $isZIP)) {
            throw new BAV_DataBackendException();
        
        }
        
        $temp    = tempnam($this->getTempdir(), "BAV_");
        $tempH   = fopen($temp, 'w');
        if (! ($temp && $tempH)) {
            throw new BAV_DataBackendException_IO();
        
        }
        $pathParts = explode('/', dirname(self::DOWNLOAD_URI).'/'.($isZIP ? $zipMatches[1] : $txtMatches[1]));
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
        curl_setopt($ch, CURLOPT_URL, $path);
        curl_setopt($ch, CURLOPT_FILE, $tempH);
        if (! curl_exec($ch)) {
            fclose($tempH);
            unlink($temp);
            throw new BAV_DataBackendException_IO();
        
        }
        fclose($tempH);
        curl_close($ch);
        
        if ($isZIP) {
            $file = tempnam($this->getTempdir(), "BAV_");
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
        
        if (! rename($file, $this->parser->getFile())) {
            throw new BAV_DataBackendException_IO();
            
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
            throw new LogicException('A bank without a main agency is invalid.');
            
        } catch (BAV_FileParserContextException_Undefined $e) {
            throw new LogicException('start and end should be defined.');
        
        } catch (BAV_FileParserException_IO $e) {
            throw new BAV_DataBackendException_IO("Parser Exception bei Bank {$bank->getBankID()}");
        
        } catch (BAV_FileParserException_ParseError $e) {
            throw new BAV_DataBackendException();
            
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
            throw new LogicException('start and end should be defined.');
        
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
    private function getTempdir() {
        $tmpDirs = array(
            function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : false,
            @$_ENV['TMP'],
            @$_ENV['TMPDIR'],
            @$_ENV['TEMP'],
            ini_get('upload_tmp_dir'),
            '/tmp'
        );
        
        foreach ($tmpDirs as $tmpDir) {
        	if ($tmpDir && is_writable($tmpDir)) {
        		return realpath($tmpDir);
        		
        	}
        	
        }
        
        $tempfile = tempnam(uniqid(rand(), true), '');
        if (file_exists($tempfile)) {
            unlink($tempfile);
            return realpath(dirname($tempfile));
            
        }
        
        throw new BAV_DataBackendException_IO();
    }
    


}


?>