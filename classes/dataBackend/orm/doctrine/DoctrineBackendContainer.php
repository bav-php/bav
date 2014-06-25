<?php

namespace malkusch\bav;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

/**
 * Container for DoctrineDataBackend objects.
 * 
 * You will need Doctrine as composer dependency.
 * 
 * @author Markus Malkusch <markus@malkusch.de>
 * @link bitcoin:1335STSwu9hST4vcMRppEPgENMHD2r1REK Donations
 * @license GPL
 * @see DataBackend
 * @link http://www.doctrine-project.org/
 */
class DoctrineBackendContainer extends DataBackendContainer
{
    
    /**
     * @var EntityManager 
     */
    private $em;
    
    /**
     * Return the paths to the XML-Mappings
     * 
     * @return string[]
     */
    public static function getXMLMappings()
    {
        return array(__DIR__ . "/mapping/");
    }
    
    /**
     * Builds a container for a connection.
     * 
     * @param mixed $connection Doctrine::DBAL connection
     * @return DoctrineBackendContainer
     */
    public static function buildByConnection($connection, $isDevMode = false)
    {
        $mappings = self::getXMLMappings();
        $config = Setup::createXMLMetadataConfiguration($mappings, $isDevMode);

        $entityManager = EntityManager::create($connection, $config);
        return new self($entityManager);
    }
    
    /**
     * Injects the EntityManager 
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }
    
    /**
     * Gets the EntityManager
     * 
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }
    
    protected function makeDataBackend()
    {
        return new DoctrineDataBackend($this->em);
    }
}
