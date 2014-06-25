<?php

namespace malkusch\bav;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\ORMException;
use Doctrine\DBAL\DBALException;

/**
 * Use Doctrine ORM as backend.
 *
 * You will need Doctrine as composer dependency.
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 * @link http://www.doctrine-project.org/
 */
class DoctrineDataBackend extends SQLDataBackend
{

    /**
     * @var EntityManager 
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    protected function getNewBank($bankID)
    {
        try {
            $bank = $this->em->find('malkusch\bav\Bank', $bankID);
            if ($bank == null) {
                throw new BankNotFoundException($bankID);

            }
            $bank->setDataBackend($this);
            return $bank;
            
        } catch (ORMException $e) {
            throw new DataBackendException($e);
            
        }
    }

    public function getAgenciesForBank(Bank $bank)
    {
        $query = $this->em->createQuery(
            "select agency from malkusch\bav\Agency agency where agency.id != :mainAgency and agency.bank=:bank"
        );
        $query->setParameters(array(
            "mainAgency" => $bank->getMainAgency()->getID(),
            "bank" => $bank
        ));
        return $query->getResult();
    }

    public function getAllBanks()
    {
        return $this->em->getRepository("malkusch\bav\Bank")->findAll();
    }

    public function getBICAgencies($bic)
    {
        return $this->em->getRepository("malkusch\bav\Agency")->findBy(array(
            "bic" => $bic
        ));
    }

    public function getLastUpdate()
    {
        $lastModified = $this->em->find("malkusch\bav\MetaData", MetaData::LASTMODIFIED);
        if ($lastModified == null) {
            throw new DataBackendException();
            
        }
        return $lastModified->getValue();
    }

    public function getMainAgency(Bank $bank)
    {
        // Return the Doctrine proxy
        return $bank->getMainAgency();
    }
    
    private function getClassesMetadata()
    {
        return array(
            $this->em->getClassMetadata('malkusch\bav\Bank'),
            $this->em->getClassMetadata('malkusch\bav\Agency'),
            $this->em->getClassMetadata('malkusch\bav\MetaData'),
        );
    }

    public function install()
    {
        $tool = new SchemaTool($this->em);
        $classes = $this->getClassesMetadata();
        $tool->createSchema($classes);
        
        $this->update();
    }

    public function isInstalled()
    {
        try {
            $this->em->find("malkusch\bav\MetaData", MetaData::LASTMODIFIED);
            return true;
            
        } catch (DBALException $e) {
            return false;
            
        }
    }

    public function uninstall()
    {
        $tool = new SchemaTool($this->em);
        $classes = $this->getClassesMetadata();
        $tool->dropSchema($classes);
        $this->em->clear();
    }

    public function update()
    {
        $this->em->transactional(function (EntityManager $em) {
            
            // Download data
            $fileUtil = new FileUtil();
            $fileBackend = new FileDataBackend(tempnam($fileUtil->getTempDirectory(), 'bav'));
            $fileBackend->install();

            // Delete all
            $em->createQuery("DELETE FROM malkusch\bav\Agency")->execute();
            $em->createQuery("DELETE FROM malkusch\bav\Bank")->execute();

            // Inserting data
            foreach ($fileBackend->getAllBanks() as $bank) {
                try {
                    $em->persist($bank);
                    
                    $agencies = $bank->getAgencies();
                    $agencies[] = $bank->getMainAgency();
                    foreach ($agencies as $agency) {
                        $em->persist($agency);
                        
                    }
                } catch (NoMainAgencyException $e) {
                    trigger_error(
                        "Skipping bank {$e->getBank()->getBankID()} without any main agency."
                    );
                }
            }
            
            // last modified
            $lastModified = $em->find("malkusch\bav\MetaData", MetaData::LASTMODIFIED);
            if ($lastModified == null) {
                $lastModified = new MetaData();
                
            }
            $lastModified->setName(MetaData::LASTMODIFIED);
            $lastModified->setValue(time());
            $em->persist($lastModified);
        });
    }

    public function free()
    {
        parent::free();
        $this->em->clear();
    }
    
    /**
     * You may use an arbitrary SQL statement to receive Agency objects.
     * Your statement should at least return the id of the agencies.
     *
     * @param string $sql
     * @throws MissingAttributesDataBackendIOException
     * @throws DataBackendIOException
     * @throws DataBackendException
     * @return Agency[]
     */
    public function getAgencies($sql)
    {
        $agencies = array();
        $backend = $this;
        $em = $this->em;
        $this->em->transactional(function () use (&$agencies, $sql, $backend, $em) {
            $stmt = $em->getConnection()->executeQuery($sql);
            
            foreach ($stmt as $result) {
                if (! array_key_exists('id', $result)) {
                    throw new MissingAttributesDataBackendIOException();

                }
                $id = $result["id"];
                $agency = $em->find("malkusch\bav\Agency", $id);
                $agencies[] = $agency;
                
                $agency->getBank()->setDataBackend($backend);

            }
        });
        return $agencies;
    }
}
