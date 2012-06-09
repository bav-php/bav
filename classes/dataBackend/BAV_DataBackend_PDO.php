<?php











/**
 * Use any DBS as backend. In addition to the BAV_DataBackend methods you
 * may use getAgencies($sql) which returns an array of BAV_Agency objects.
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
 * @copyright Copyright (C) 2006 Markus Malkusch
 */
class BAV_DataBackend_PDO extends BAV_DataBackend {


    private
    /**
     * @var array
     */
    $agencies = array(),
    /**
     * @var PDOStatement
     */
    $selectBank,
    /**
     * @var PDOStatement
     */
    $selectMainAgency,
    /**
     * @var PDOStatement
     */
    $selectAgencies,
    /**
     * @var PDOStatement
     */
    $selectAgency,
    /**
     * @var PDOStatement
     */
    $selectAgencysBank,
    /**
     * @var PDO
     */
    $pdo,
    /**
     * @var string
     */
    $prefix = '';


    /**
     * @param String $prefix the prefix of the table names. Default is 'bav_'.
     */
    public function __construct(PDO $pdo, $prefix = 'bav_') {
        $this->pdo    = $pdo;
        $this->prefix = $prefix;

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }


    /**
     * @return string
     */
    public function getPrefix() {
        return $this->prefix;
    }


    /**
     * You may use an arbitrary SQL statement to receive BAV_Agency objects.
     * Your statement should at least return the id of the agencies.
     *
     * If you want to perform well your query should provide all attributes of
     * a BAV_Agency object (id, name, postcode, city, shortTerm, bank, pan, bic).
     * If you don't, BAV needs to do one additional query for each object.
     *
     * @param string $sql
     * @throws BAV_DataBackendException_IO_MissingAttributes
     * @throws BAV_DataBackendException_IO
     * @throws BAV_DataBackendException
     * @return array
     */
    public function getAgencies($sql) {
        try {
            $this->prepareStatements();
            $agencies = array();
            
            foreach ($this->pdo->query($sql) as $result) {
                if (! $this->isValidAgencyResult($result)) {
                    if (! array_key_exists('id', $result)) {
                        throw new BAV_DataBackendException_IO_MissingAttributes();

                    }
                    $this->selectAgency->execute(array(':agency' => $result['id']));
                    $result = $this->selectAgency->fetch(PDO::FETCH_ASSOC);
                    $this->selectAgency->closeCursor();
                    if ($result === false) {
                        throw new BAV_DataBackendException_IO();

                    }
                }
                if (! array_key_exists('bank', $result)) {
                    $this->selectAgencysBank->execute(array(':agency' => $result['id']));
                    $bankResult = $this->selectAgencysBank->fetch(PDO::FETCH_ASSOC);
                    $this->selectAgencysBank->closeCursor();
                    if ($bankResult === false) {
                        throw new BAV_DataBackendException_IO();

                    }
                    $result['bank'] = $bankResult['bank'];

                }
                $agencies[] = $this->getAgencyObject($this->getBank($result['bank']), $result);

            }
            return $agencies;

        } catch (PDOException $e) {
            throw new BAV_DataBackendException_IO();

        } catch (BAV_DataBackendException_BankNotFound $e) {
            throw new LogicException($e);

        }
    }
    
    
    /**
     * @throws PDOException
     */
    private function prepareStatements() {
        if (! is_null($this->selectBank)) {
            return;
        }
        
        $this->selectBank = $this->pdo->prepare("SELECT id, validator FROM {$this->prefix}bank WHERE id = :bankID");
        
        $agencyAttributes
            = "a.id, name, postcode, city, shortTerm AS 'shortTerm', pan, bic";
        $this->selectMainAgency = $this->pdo->prepare(
            "SELECT $agencyAttributes FROM {$this->prefix}bank b
                INNER JOIN {$this->prefix}agency a ON b.mainAgency = a.id
                WHERE b.id = :bankID");
        $this->selectAgencies = $this->pdo->prepare(
            "SELECT $agencyAttributes FROM {$this->prefix}agency a
                WHERE bank = :bankID AND id != :mainAgency");
        $this->selectAgency = $this->pdo->prepare(
            "SELECT $agencyAttributes, bank FROM {$this->prefix}agency a
                WHERE id = :agency");
        $this->selectAgencysBank = $this->pdo->prepare(
            "SELECT bank FROM {$this->prefix}agency
                WHERE id = :agency");
    }


    /**
     * @see BAV_DataBackend::update()
     * @throws BAV_DataBackendException
     */
    public function update() {
        $useTA = false;
        try {
            $fileBackend = new BAV_DataBackend_File(tempnam(BAV_DataBackend_File::getTempdir(), 'bav'));
            $fileBackend->install();
            
            $insertBank     = $this->pdo->prepare(
                "INSERT INTO {$this->prefix}bank
                    (id, validator, mainAgency)
                    VALUES(:bankID, :validator, :mainAgency)");
            $insertAgency   = $this->pdo->prepare(
                "INSERT INTO {$this->prefix}agency
                    (id, name, postcode, city, shortTerm, pan, bic, bank)
                    VALUES (:id, :name, :postcode, :city, :shortTerm, :pan, :bic, :bank)");
            try {
                $this->pdo->beginTransaction();
                $useTA = true;
            
            } catch (PDOException $e) {
                trigger_error("Your DBS doesn't support transactions. Your data may be corrupted.");
            
            }
            $this->pdo->exec("DELETE FROM {$this->prefix}agency");
            $this->pdo->exec("DELETE FROM {$this->prefix}bank");
            
            foreach ($fileBackend->getAllBanks() as $bank) {
            	try {
	                $insertBank->execute(array(
	                    ":bankID"       => $bank->getBankID(),
	                    ":validator"    => $bank->getValidationType(),
	                    ":mainAgency"   => $bank->getMainAgency()->getID(),
	                ));
	                $agencies   = $bank->getAgencies();
	                $agencies[] = $bank->getMainAgency();
	                foreach ($agencies as $agency) {
	                    $insertAgency->execute(array(
	                        ":id"           => $agency->getID(),
	                        ":name"         => $agency->getName(),
	                        ":postcode"     => $agency->getPostcode(),
	                        ":city"         => $agency->getCity(),
	                        ":shortTerm"    => $agency->getShortTerm(),
	                        ":bank"         => $bank->getBankID(),
	                        ":pan"          => $agency->hasPAN() ? $agency->getPAN() : null,
	                        ":bic"          => $agency->hasBIC() ? $agency->getBIC() : null
	                    ));
	                
	                }
            	} catch(BAV_DataBackendException_NoMainAgency $e) {
            	   	trigger_error(
                        "Skipping bank {$e->getBank()->getBankID()} without any main agency."
               	    );
            		
            	}
            }
            if ($useTA) {
                $this->pdo->commit();
                $useTA = false;
                
            }
            $fileBackend->uninstall();
            
        } catch (Exception $e) {
            try {
                if ($useTA) {
                    $this->pdo->rollback();

                }
                throw $e;
                    
            } catch (PDOException $e2) {
                throw new BAV_DataBackendException_IO(
                    get_class($e) . ": {$e->getMessage()}\nadditionally: {$e2->getMessage()}"
                );
                
            }
        
        }
    }


    /**
     * @see BAV_DataBackend::install()
     * @throws BAV_DataBackendException_IO
     */
    public function install() {
        try {
        	$createOptions = '';
        	switch ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME)) {
        		
        		case 'mysql':
        			$createOptions .= " engine=InnoDB";
        			break;
        			
        	}
            $this->pdo->exec("
            
                CREATE TABLE {$this->prefix}bank(
                    id          int primary key,
                    validator   char(2) NOT NULL,
                    mainAgency  int NOT NULL
                    
                    /* FOREIGN KEY (mainAgency) REFERENCES {$this->prefix}agency(id) */
                )$createOptions
            
            ");
            $this->pdo->exec("
            
                CREATE TABLE {$this->prefix}agency(
                    id          int primary key,
                    name        varchar(".BAV_FileParser::NAME_LENGTH.")        NOT NULL,
                    postcode    varchar(".BAV_FileParser::POSTCODE_LENGTH.")    NOT NULL,
                    city        varchar(".BAV_FileParser::CITY_LENGTH.")        NOT NULL,
                    shortTerm   varchar(".BAV_FileParser::SHORTTERM_LENGTH.")   NOT NULL,
                    bank        int                                             NOT NULL,
                    pan         char(".BAV_FileParser::PAN_LENGTH.")            NULL,
                    bic         varchar(".BAV_FileParser::BIC_LENGTH.")         NULL,
                    
                    FOREIGN KEY (bank) REFERENCES {$this->prefix}bank(id)
                )$createOptions
            
            ");
            $this->update();
        
        } catch (PDOException $e) {
            throw new BAV_DataBackendException_IO($e->getMessage());
        
        }
    }
    /**
     * @see BAV_DataBackend::uninstall()
     * @throws BAV_DataBackendException_IO
     */
    public function uninstall() {
        try {
            $this->pdo->exec("DROP TABLE {$this->prefix}bank");
            $this->pdo->exec("DROP TABLE {$this->prefix}agency");
            
        } catch (PDOException $e) {
            throw new BAV_DataBackendException_IO();
        
        }
    }
    

    /**
     * @see BAV_DataBackend::getAllBanks()
     * @throws BAV_DataBackendException
     * @return array
     */
    public function getAllBanks() {
        try {
            foreach ($this->pdo->query("SELECT id, validator FROM {$this->prefix}bank") as $bankResult) {
                if (isset($this->instances[$bankResult['id']])) {
                    continue;
                
                }
                $bank = $this->getBankObject($bankResult);
                $this->instances[$bank->getBankID()] = $bank;
                
            }
            return array_values($this->instances);
            
        } catch (PDOException $e) {
            throw new BAV_DataBackendException_IO();
        
        } catch (BAV_DataBackendException_IO_MissingAttributes $e) {
            $this->selectBank->closeCursor();
            throw new LogicException($e);

        }
    }
    /**
     * @throws BAV_DataBackendException
     * @throws BAV_DataBackendException_BankNotFound
     * @param string $bankID
     * @return BAV_Bank
     * @see BAV_DataBackend::getNewBank()
     */
    protected function getNewBank($bankID) {
        try {
            $this->prepareStatements();
            $this->selectBank->execute(array(':bankID' => $bankID));
            $result = $this->selectBank->fetch(PDO::FETCH_ASSOC);
            if ($result === false) {
                $this->selectBank->closeCursor();
                throw new BAV_DataBackendException_BankNotFound($bankID);
            
            }
            $this->selectBank->closeCursor();
            return $this->getBankObject($result);
            
        } catch (PDOException $e) {
            $this->selectBank->closeCursor();
            throw new BAV_DataBackendException_IO();
        
        } catch (BAV_DataBackendException_IO_MissingAttributes $e) {
            $this->selectBank->closeCursor();
            throw new LogicException($e);

        }
    }
    /**
     * @return bool
     */
    private function isValidBankResult(Array $result) {
        return array_key_exists('id',           $result)
            && array_key_exists('validator',    $result);
    }
    /**
     * @return bool
     */
    private function isValidAgencyResult(Array $result) {
        return array_key_exists('id',           $result)
            && array_key_exists('name',         $result)
            && array_key_exists('shortTerm',    $result)
            && array_key_exists('city',         $result)
            && array_key_exists('postcode',     $result)
            && array_key_exists('bic',          $result)
            && array_key_exists('pan',          $result);
    }
    /**
     * @return BAV_Bank
     * @throws BAV_DataBackendException_IO_MissingAttributes
     */
    private function getBankObject(Array $fetchedResult) {
        if (! $this->isValidBankResult($fetchedResult)) {
            throw new BAV_DataBackendException_IO_MissingAttributes();

        }
        return new BAV_Bank($this, $fetchedResult['id'], $fetchedResult['validator']);
    }
    /**
     * @return BAV_Agency
     * @throws BAV_DataBackendException_IO_MissingAttributes
     */
    private function getAgencyObject(BAV_Bank $bank, Array $fetchedResult) {
        if (! $this->isValidAgencyResult($fetchedResult)) {
            throw new BAV_DataBackendException_IO_MissingAttributes();

        }
        if (! array_key_exists($fetchedResult['id'], $this->agencies)) {
            $this->agencies[$fetchedResult['id']] = new BAV_Agency(
                $fetchedResult['id'],
                $bank,
                $fetchedResult['name'],
                $fetchedResult['shortTerm'],
                $fetchedResult['city'],
                $fetchedResult['postcode'],
                $fetchedResult['bic'],
                $fetchedResult['pan']);

        }
        return $this->agencies[$fetchedResult['id']];
    }
    /**
     * @throws BAV_DataBackendException
     * @return BAV_Agency
     * @see BAV_DataBackend::_getMainAgency()
     */
    public function _getMainAgency(BAV_Bank $bank) {
        try {
            $this->prepareStatements();
            $this->selectMainAgency->execute(array(":bankID" => $bank->getBankID()));
            $result = $this->selectMainAgency->fetch();
            if ($result === false) {
                throw new BAV_DataBackendException();

            }
            $this->selectMainAgency->closeCursor();
            return $this->getAgencyObject($bank, $result);

        } catch (PDOException $e) {
            $this->selectMainAgency->closeCursor();
            throw new BAV_DataBackendException_IO();

        } catch (BAV_DataBackendException_IO_MissingAttributes $e) {
            $this->selectMainAgency->closeCursor();
            throw new LogicException($e);

        }
    }
    /**
     * @throws BAV_DataBackendException
     * @return array
     * @see BAV_DataBackend::_getAgencies()
     */
    public function _getAgencies(BAV_Bank $bank) {
        try {
            $this->prepareStatements();
            $agencies = array();
            $this->selectAgencies->execute(array(
                ":bankID"       => $bank->getBankID(),
                ":mainAgency"   => $bank->getMainAgency()->getID()));
            foreach ($this->selectAgencies->fetchAll() as $agencyResult) {
                $agencies[] = $this->getAgencyObject($bank, $agencyResult);

            }
            $this->selectAgencies->closeCursor();
            return $agencies;

        } catch (PDOException $e) {
            $this->selectAgencies->closeCursor();
            throw new BAV_DataBackendException_IO();

        } catch (BAV_DataBackendException_IO_MissingAttributes $e) {
            $this->selectAgencies->closeCursor();
            throw new LogicException($e);

        }
    }


}


?>