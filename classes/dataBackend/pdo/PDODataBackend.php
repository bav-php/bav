<?php

namespace malkusch\bav;

/**
 * Use any DBS as backend. In addition to the DataBackend methods you
 * may use getAgencies($sql) which returns an array of Agency objects.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 */
class PDODataBackend extends SQLDataBackend
{

    /**
     * @var array
     */
    private $agencies = array();

    /**
     * @var string
     */
    private $agencyAttributes =
        "a.id, name, postcode, city, shortTerm AS 'shortTerm', pan, bic";

    /**
     * @var StatementContainer
     */
    private $statementContainer;

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @param String $prefix the prefix of the table names.
     */
    public function __construct(\PDO $pdo, $prefix = "bav_")
    {
        $this->pdo    = $pdo;
        $this->prefix = $prefix;
        $this->statementContainer = new StatementContainer($pdo);

        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * You may use an arbitrary SQL statement to receive Agency objects.
     * Your statement should at least return the id of the agencies.
     *
     * If you want to perform well your query should provide all attributes of
     * a Agency object (id, name, postcode, city, shortTerm, bank, pan, bic).
     * If you don't, BAV needs to do one additional query for each object.
     *
     * @param string $sql
     * @throws MissingAttributesDataBackendIOException
     * @throws DataBackendIOException
     * @throws DataBackendException
     * @return Agency[]
     */
    public function getAgencies($sql)
    {
        try {
            $agencies = array();

            foreach ($this->pdo->query($sql) as $result) {
                if (! $this->isValidAgencyResult($result)) {
                    if (! array_key_exists('id', $result)) {
                        throw new MissingAttributesDataBackendIOException();

                    }
                    $stmt = $this->statementContainer->prepare(
                        "SELECT $this->agencyAttributes, bank FROM {$this->prefix}agency a
                            WHERE id = :agency"
                    );
                    $stmt->execute(array(':agency' => $result['id']));
                    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                    if ($result === false) {
                        throw new DataBackendIOException();

                    }
                }
                if (! array_key_exists('bank', $result)) {
                    $stmt = $this->statementContainer->prepare(
                        "SELECT bank FROM {$this->prefix}agency
                            WHERE id = :agency"
                    );
                    $stmt->execute(array(':agency' => $result['id']));
                    $bankResult = $stmt->fetch(\PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                    if ($bankResult === false) {
                        throw new DataBackendIOException();

                    }
                    $result['bank'] = $bankResult['bank'];

                }
                $agencies[] = $this->getAgencyObject($this->getBank($result['bank']), $result);

            }
            return $agencies;

        } catch (\PDOException $e) {
            throw new DataBackendIOException();

        } catch (BankNotFoundException $e) {
            throw new \LogicException($e);

        }
    }

    /**
     * @see DataBackend::update()
     * @throws DataBackendException
     */
    public function update()
    {
        $useTA = false;
        try {
            $fileUtil = new FileUtil();
            $fileBackend = new FileDataBackend(tempnam($fileUtil->getTempDirectory(), 'bav'));
            $fileBackend->install();

            $insertBank     = $this->pdo->prepare(
                "INSERT INTO {$this->prefix}bank
                    (id, validator, mainAgency)
                    VALUES(:bankID, :validator, :mainAgency)"
            );
            $insertAgency   = $this->pdo->prepare(
                "INSERT INTO {$this->prefix}agency
                    (id, name, postcode, city, shortTerm, pan, bic, bank)
                    VALUES (:id, :name, :postcode, :city, :shortTerm, :pan, :bic, :bank)"
            );
            try {
                $this->pdo->beginTransaction();
                $useTA = true;

            } catch (\PDOException $e) {
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
                } catch (NoMainAgencyException $e) {
                    trigger_error(
                        "Skipping bank {$e->getBank()->getBankID()} without any main agency."
                    );

                }
            }

            // Update modification timestamp
            $modificationStmt = $this->pdo->prepare(
                "UPDATE {$this->prefix}meta SET value=:value WHERE name=:name"
            );
            $modificationStmt->execute(array(
                ":name"  => MetaData::LASTMODIFIED,
                ":value" => time()
            ));


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

            } catch (\PDOException $e2) {
                throw new DataBackendIOException(
                    get_class($e) . ": {$e->getMessage()}\nadditionally: {$e2->getMessage()}"
                );

            }

        }
    }

    /**
     * @see DataBackend::install()
     * @throws DataBackendIOException
     */
    public function install()
    {
        try {
            $createOptions = '';
            switch ($this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME)) {

                case 'mysql':
                    $createOptions .= " engine=InnoDB";
                    break;

            }
            $this->pdo->exec(
                "CREATE TABLE {$this->prefix}bank(
                    id          int primary key,
                    validator   char(2) NOT NULL,
                    mainAgency  int NOT NULL

                    /* FOREIGN KEY (mainAgency) REFERENCES {$this->prefix}agency(id) */
                )$createOptions"
            );
            $this->pdo->exec(
                "CREATE TABLE {$this->prefix}agency(
                    id          int primary key,
                    name        varchar(".FileParser::NAME_LENGTH.")        NOT NULL,
                    postcode    varchar(".FileParser::POSTCODE_LENGTH.")    NOT NULL,
                    city        varchar(".FileParser::CITY_LENGTH.")        NOT NULL,
                    shortTerm   varchar(".FileParser::SHORTTERM_LENGTH.")   NOT NULL,
                    bank        int                                             NOT NULL,
                    pan         char(".FileParser::PAN_LENGTH.")            NULL,
                    bic         varchar(".FileParser::BIC_LENGTH.")         NULL,

                    FOREIGN KEY (bank) REFERENCES {$this->prefix}bank(id)
                )$createOptions"
            );
            
            try {
                $this->pdo->exec("CREATE INDEX bic ON {$this->prefix}agency (bic)");

            } catch (\PDOException $e) {
                trigger_error("Failed to create index for bic: {$e->getMessage()}", E_USER_WARNING);

            }

            $this->pdo->exec(
                "CREATE TABLE {$this->prefix}meta(
                    name   char(32) NOT NULL primary key,
                    value  varchar(128)
                )$createOptions"
            );
            $insertMetaStmt = $this->pdo->prepare(
                "INSERT INTO {$this->prefix}meta (name, value) VALUES (:name, :value)"
            );
            $insertMetaStmt->execute(array(
                ":name" => MetaData::LASTMODIFIED,
                ":value" => null
            ));
            $this->update();

        } catch (\PDOException $e) {
            throw new DataBackendIOException($e->getMessage(), 0, $e);

        }
    }

    /**
     * @see DataBackend::uninstall()
     * @throws DataBackendIOException
     */
    public function uninstall()
    {
        try {
            $this->pdo->exec("DROP TABLE {$this->prefix}agency");
            $this->pdo->exec("DROP TABLE {$this->prefix}bank");
            $this->pdo->exec("DROP TABLE {$this->prefix}meta");

        } catch (\PDOException $e) {
            throw new DataBackendIOException();

        }
    }

    /**
     * @see DataBackend::getAllBanks()
     * @throws DataBackendException
     * @return Bank[]
     */
    public function getAllBanks()
    {
        try {
            foreach ($this->pdo->query("SELECT id, validator FROM {$this->prefix}bank") as $bankResult) {
                if (isset($this->instances[$bankResult['id']])) {
                    continue;

                }
                $bank = $this->getBankObject($bankResult);
                $this->instances[$bank->getBankID()] = $bank;

            }
            return array_values($this->instances);

        } catch (\PDOException $e) {
            throw new DataBackendIOException();

        } catch (MissingAttributesDataBackendIOException $e) {
            throw new \LogicException($e);

        }
    }

    /**
     * @throws DataBackendException
     * @throws BankNotFoundException
     * @param string $bankID
     * @return Bank
     * @see DataBackend::getNewBank()
     */
    protected function getNewBank($bankID)
    {
        try {
            $stmt = $this->statementContainer->prepare(
                "SELECT id, validator FROM {$this->prefix}bank WHERE id = :bankID"
            );
            $stmt->execute(array(':bankID' => $bankID));
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($result === false) {
                $stmt->closeCursor();
                throw new BankNotFoundException($bankID);

            }
            $stmt->closeCursor();
            return $this->getBankObject($result);

        } catch (\PDOException $e) {
            $stmt->closeCursor();
            throw new DataBackendIOException();

        } catch (MissingAttributesDataBackendIOException $e) {
            $stmt->closeCursor();
            throw new \LogicException($e);

        }
    }

    /**
     * @return bool
     */
    private function isValidBankResult(Array $result)
    {
        return array_key_exists('id', $result)
            && array_key_exists('validator', $result);
    }

    /**
     * @return bool
     */
    private function isValidAgencyResult(Array $result)
    {
        return array_key_exists('id', $result)
            && array_key_exists('name', $result)
            && array_key_exists('shortTerm', $result)
            && array_key_exists('city', $result)
            && array_key_exists('postcode', $result)
            && array_key_exists('bic', $result)
            && array_key_exists('pan', $result);
    }

    /**
     * @return Bank
     * @throws MissingAttributesDataBackendIOException
     */
    private function getBankObject(Array $fetchedResult)
    {
        if (! $this->isValidBankResult($fetchedResult)) {
            throw new MissingAttributesDataBackendIOException();

        }
        return new Bank($this, $fetchedResult['id'], $fetchedResult['validator']);
    }

    /**
     * @return Agency
     * @throws MissingAttributesDataBackendIOException
     */
    private function getAgencyObject(Bank $bank, Array $fetchedResult)
    {
        if (! $this->isValidAgencyResult($fetchedResult)) {
            throw new MissingAttributesDataBackendIOException();

        }
        if (! array_key_exists($fetchedResult['id'], $this->agencies)) {
            $this->agencies[$fetchedResult['id']] = new Agency(
                $fetchedResult['id'],
                $bank,
                $fetchedResult['name'],
                $fetchedResult['shortTerm'],
                $fetchedResult['city'],
                $fetchedResult['postcode'],
                $fetchedResult['bic'],
                $fetchedResult['pan']
            );

        }
        return $this->agencies[$fetchedResult['id']];
    }

    /**
     * @throws DataBackendException
     * @return Agency
     * @see DataBackend::getMainAgency()
     */
    public function getMainAgency(Bank $bank)
    {
        try {
            $stmt = $this->statementContainer->prepare(
                "SELECT $this->agencyAttributes FROM {$this->prefix}bank b
                    INNER JOIN {$this->prefix}agency a ON b.mainAgency = a.id
                    WHERE b.id = :bankID"
            );
            $stmt->execute(array(":bankID" => $bank->getBankID()));
            $result = $stmt->fetch();
            if ($result === false) {
                throw new DataBackendException();

            }
            $stmt->closeCursor();
            return $this->getAgencyObject($bank, $result);

        } catch (\PDOException $e) {
            $stmt->closeCursor();
            throw new DataBackendIOException($e->getMessage(), 0, $e);

        } catch (MissingAttributesDataBackendIOException $e) {
            $stmt->closeCursor();
            throw new \LogicException($e);

        }
    }

    /**
     * @throws DataBackendException
     * @return Agency[]
     * @see DataBackend::getAgenciesForBank()
     */
    public function getAgenciesForBank(Bank $bank)
    {
        try {
            $stmt = $this->statementContainer->prepare(
                "SELECT $this->agencyAttributes FROM {$this->prefix}agency a
                    WHERE bank = :bankID AND id != :mainAgency"
            );
            $agencies = array();
            $stmt->execute(array(
                ":bankID"       => $bank->getBankID(),
                ":mainAgency"   => $bank->getMainAgency()->getID()));
            foreach ($stmt->fetchAll() as $agencyResult) {
                $agencies[] = $this->getAgencyObject($bank, $agencyResult);

            }
            $stmt->closeCursor();
            return $agencies;

        } catch (\PDOException $e) {
            $stmt->closeCursor();
            throw new DataBackendIOException($e->getMessage(), 0, $e);

        } catch (MissingAttributesDataBackendIOException $e) {
            $stmt->closeCursor();
            throw new \LogicException($e);

        }
    }

    /**
     * Returns the timestamp of the last update.
     *
     * @return int timestamp
     * @throws DataBackendException
     */
    public function getLastUpdate()
    {
        try {
            $stmt = $this->statementContainer->prepare(
                "SELECT value FROM {$this->prefix}meta
                    WHERE name = :name"
            );
            $stmt->execute(array(
                ":name" => MetaData::LASTMODIFIED,
            ));
            $result = $stmt->fetch();
            if ($result === false) {
                throw new DataBackendException();

            }
            $stmt->closeCursor();
            return $result["value"];

        } catch (\PDOException $e) {
            $stmt->closeCursor();
            throw new DataBackendIOException($e->getMessage(), $e->getCode(), $e);

        }
    }

    /**
     * Returns true if the backend was installed.
     *
     * @return bool
     * @throws DataBackendException
     */
    public function isInstalled()
    {
        try {
            
            switch ($this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
                
                case "sqlite":
                    $query =
                        "SELECT count(*) FROM sqlite_master
                            WHERE type='table' AND name = '{$this->prefix}meta'";
                    break;
                
                default:
                    $query =
                        "SELECT CASE WHEN EXISTS(
                            (SELECT * FROM information_schema.tables
                                WHERE table_name='{$this->prefix}meta')
                        ) THEN 1 ELSE 0 END";
                    break;
                
            }
            
            $stmt = $this->statementContainer->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            if ($result === false) {
                throw new DataBackendException();

            }
            $stmt->closeCursor();
            return $result[0] == 1;

        } catch (\PDOException $e) {
            $stmt->closeCursor();
            throw new DataBackendIOException($e->getMessage(), 0, $e);

        }
    }

    /**
     * Returns if a bic is valid.
     *
     * @param string $bic BIC
     * @return bool
     */
    public function isValidBIC($bic)
    {
        try {
            $stmt = $this->statementContainer->prepare(
                "SELECT bic FROM {$this->prefix}agency WHERE bic = :bic GROUP BY (bic)"
            );
            $stmt->execute(array(":bic" => $bic));
            
            $rows = $stmt->fetchAll();
            return ! empty($rows);

        } catch (\PDOException $e) {
            $stmt->closeCursor();
            throw new DataBackendIOException($e->getMessage(), 0, $e);

        }
    }

    /**
     * Returns bank agencies for a given BIC.
     *
     * @param string $bic BIC
     * @return Agency[]
     */
    public function getBICAgencies($bic)
    {
        try {
            $stmt = $this->statementContainer->prepare(
                "SELECT bank, $this->agencyAttributes FROM {$this->prefix}agency a
                    WHERE bic = :bic"
            );
            $agencies = array();
            $stmt->execute(array(":bic" => $bic));
            foreach ($stmt->fetchAll() as $result) {
                $agencies[] = $this->getAgencyObject($this->getBank($result['bank']), $result);

            }
            $stmt->closeCursor();
            return $agencies;

        } catch (\PDOException $e) {
            $stmt->closeCursor();
            throw new DataBackendIOException($e->getMessage(), 0, $e);

        } catch (MissingAttributesDataBackendIOException $e) {
            $stmt->closeCursor();
            throw new \LogicException($e);

        }
    }
        
    public function free()
    {
        parent::free();
        $this->agencies = array();
    }
}
