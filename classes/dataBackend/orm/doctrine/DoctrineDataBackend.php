<?php

namespace malkusch\bav;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\SchemaValidator;
use Doctrine\ORM\ORMException;

/**
 * Use Doctrine ORM as backend.
 *
 * You will need Doctrine as composer dependency.
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @license GPL
 * @link http://www.doctrine-project.org/
 */
class DoctrineDataBackend extends DataBackend
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
        $schemaValidator = new SchemaValidator($this->em);
        $validation = $schemaValidator->validateClass($this->em->getClassMetadata("malkusch\bav\Bank"));
        return empty($validation);
    }

    public function uninstall()
    {
        $tool = new SchemaTool($this->em);
        $classes = $this->getClassesMetadata();
        $tool->dropSchema($classes);
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
            $lastModified = new MetaData();
            $lastModified->setName(MetaData::LASTMODIFIED);
            $lastModified->setValue(time());
            $em->persist($lastModified);
        });
    }
}
