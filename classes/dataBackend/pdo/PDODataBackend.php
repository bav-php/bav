<?php

namespace malkusch\bav;

/**
 * Use any DBS as backend. In addition to the DataBackend methods you
 * may use getAgencies($sql) which returns an array of Agency objects.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @license GPL
 */
class PDODataBackend extends DataBackend
{

    /**
     * @var array
     */
    private $agencies = array();

    /**
     * @var \PDOStatement
     */
    private $selectBank;

    /**
     * @var \PDOStatement
     */
    private $selectMainAgency;

    /**
     * @var \PDOStatement
     */
    private $selectAgencies;

    /**
     * @var \PDOStatement
     */
    private $selectAgency;

    /**
     * @var \PDOStatement
     */
    private $selectAgencysBank;

    /**
     * @var \PDOStatement
     */
    private $selectMeta;

    /**
     * @var \PDOStatement
     */
    private $selectInstalled;

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var string last modification name
     */
    const META_MODIFICATION = "lastModified";

    /**
     * @param String $prefix the prefix of the table names.
     */
    public function __construct(\PDO $pdo, $prefix = "bav_")
    {
        $this->pdo    = $pdo;
        $this->prefix = $prefix;

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
            $this->prepareStatements();
            $agencies = array();

            foreach ($this->pdo->query($sql) as $result) {
                if (! $this->isValidAgencyResult($result)) {
                    if (! array_key_exists('id', $result)) {
                        throw new MissingAttributesDataBackendIOException();

                    }
                    $this->selectAgency->execute(array(':agency' => $result['id']));
                    $result = $this->selectAgency->fetch(\PDO::FETCH_ASSOC);
                    $this->selectAgency->closeCursor();
                    if ($result === false) {
                        throw new DataBackendIOException();

                    }
                }
                if (! array_key_exists('bank', $result)) {
                    $this->selectAgencysBank->execute(array(':agency' => $result['id']));
                    $bankResult = $this->selectAgencysBank->fetch(\PDO::FETCH_ASSOC);
                    $this->selectAgencysBank->closeCursor();
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
            throw new LogicException($e);

        }
    }

    /**
     * @throws \PDOException
     */
    private function prepareStatements()
    {
        if (! is_null($this->selectBank)) {
            return;
        }

        $this->selectBank = $this->pdo->prepare("SELECT id, validator FROM {$this->prefix}bank WHERE id = :bankID");

        $agencyAttributes
            = "a.id, name, postcode, city, shortTerm AS 'shortTerm', pan, bic";
        $this->selectMainAgency = $this->pdo->prepare(
            "SELECT $agencyAttributes FROM {$this->prefix}bank b
                INNER JOIN {$this->prefix}agency a ON b.mainAgency = a.id
                WHERE b.id = :bankID"
        );
        $this->selectAgencies = $this->pdo->prepare(
            "SELECT $agencyAttributes FROM {$this->prefix}agency a
                WHERE bank = :bankID AND id != :mainAgency"
        );
        $this->selectAgency = $this->pdo->prepare(
            "SELECT $agencyAttributes, bank FROM {$this->prefix}agency a
                WHERE id = :agency"
        );
        $this->selectAgencysBank = $this->pdo->prepare(
            "SELECT bank FROM {$this->prefix}agency
                WHERE id = :agency"
        );
        $this->selectMeta = $this->pdo->prepare(
            "SELECT value FROM {$this->prefix}meta
                WHERE name = :name"
        );
        $this->selectInstalled = $this->pdo->prepare(
            "SELECT CASE WHEN EXISTS(
                (SELECT * FROM information_schema.tables WHERE table_name='{$this->prefix}meta')
            ) THEN 1 ELSE 0 END"
        );
    }

    /**
     * @see DataBackend::update()
     * @throws DataBackendException
     */
    public function update()
    {
        $useTA = false;
        try {
            $fileBackend = new FileDataBackend(tempnam(FileDataBackend::getTempdir(), 'bav'));
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
                ":name"  => self::META_MODIFICATION,
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
            $this->pdo->exec(
                "CREATE TABLE {$this->prefix}meta(
                    name   char(32) NOT NULL primary key,
                    value  varchar(128)
                )$createOptions"
            );
            $insertMetaStmt = $this->pdo->prepare(
                "INSERT INTO {$this->prefix}meta
                    SET name  = :name,
                        value = :value"
            );
            $insertMetaStmt->execute(array(
                ":name" => self::META_MODIFICATION,
                ":value" => null
            ));
            $this->update();

        } catch (\PDOException $e) {
            throw new DataBackendIOException($e->getMessage());

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
            $this->selectBank->closeCursor();
            throw new LogicException($e);

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
            $this->prepareStatements();
            $this->selectBank->execute(array(':bankID' => $bankID));
            $result = $this->selectBank->fetch(\PDO::FETCH_ASSOC);
            if ($result === false) {
                $this->selectBank->closeCursor();
                throw new BankNotFoundException($bankID);

            }
            $this->selectBank->closeCursor();
            return $this->getBankObject($result);

        } catch (\PDOException $e) {
            $this->selectBank->closeCursor();
            throw new DataBackendIOException();

        } catch (MissingAttributesDataBackendIOException $e) {
            $this->selectBank->closeCursor();
            throw new LogicException($e);

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
            $this->prepareStatements();
            $this->selectMainAgency->execute(array(":bankID" => $bank->getBankID()));
            $result = $this->selectMainAgency->fetch();
            if ($result === false) {
                throw new DataBackendException();

            }
            $this->selectMainAgency->closeCursor();
            return $this->getAgencyObject($bank, $result);

        } catch (\PDOException $e) {
            $this->selectMainAgency->closeCursor();
            throw new DataBackendIOException($e->getMessage(), $e->getCode(), $e);

        } catch (MissingAttributesDataBackendIOException $e) {
            $this->selectMainAgency->closeCursor();
            throw new LogicException($e);

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

        } catch (\PDOException $e) {
            $this->selectAgencies->closeCursor();
            throw new DataBackendIOException($e->getMessage(), 0, $e);

        } catch (MissingAttributesDataBackendIOException $e) {
            $this->selectAgencies->closeCursor();
            throw new LogicException($e);

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
            $this->prepareStatements();
            $this->selectMeta->execute(array(
                ":name" => self::META_MODIFICATION,
            ));
            $result = $this->selectMeta->fetch();
            if ($result === false) {
                throw new DataBackendException();

            }
            $this->selectMeta->closeCursor();
            return $result["value"];

        } catch (\PDOException $e) {
            $this->selectMeta->closeCursor();
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
            $this->prepareStatements();
            $this->selectInstalled->execute();
            $result = $this->selectInstalled->fetch();
            if ($result === false) {
                throw new DataBackendException();

            }
            $this->selectInstalled->closeCursor();
            return $result[0] == 1;

        } catch (\PDOException $e) {
            $this->selectInstalled->closeCursor();
            throw new DataBackendIOException($e->getMessage(), $e->getCode(), $e);

        }
    }
}
