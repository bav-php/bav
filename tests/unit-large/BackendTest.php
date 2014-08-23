<?php

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";

/**
 * Tests the Backends.
 * This test needs some memory (about 550M)!
 *
 * @license WTFPL
 * @author Markus Malkusch <markus@malkusch.de>
 */
class BackendTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var FileDataBackend
     */
    private static $referenceBackend;
    
    /**
     * @var DataBackend[]
     */
    private static $freeableDatabackends = array();

    /**
     * Defines the reference backend
     */
    public static function classConstructor()
    {
        self::$referenceBackend = new FileDataBackend();
    }

    /**
     * @return DataBackend[] The tested backends
     */
    public function provideBackends()
    {
        $backends = array();
        
        $backends[] = new PDODataBackend(PDOFactory::makePDO());
        $backends[] = new FileDataBackend();
        
        $conn = array(
            "pdo" => PDOFactory::makePDO(),
        );
        $doctrineContainer = DoctrineBackendContainer::buildByConnection($conn, true);
        $backends[] = new DoctrineDataBackend($doctrineContainer->getEntityManager());

        foreach ($backends as &$backend) {
            if (! $backend->isInstalled()) {
                $backend->install();

            }
            
            self::$freeableDatabackends[] = $backend;
            
            $backend = array($backend);
            
        };
        
        return $backends;
    }

    /**
     * @return DataBackend[]
     */
    public function provideInstallationBackends()
    {
        $backends = array();
        
        $backends[] = new PDODataBackend(PDOFactory::makePDO(), "bavtest_");

        $fileUtil = new FileUtil();
        $backends[] = new FileDataBackend(tempnam($fileUtil->getTempDirectory(), "bavtest"));
        
        $conn = array(
            "driver" => "pdo_sqlite",
            "path" => ":memory:"
        );
        $doctrineContainer = DoctrineBackendContainer::buildByConnection($conn, true);
        $backends[] = new DoctrineDataBackend($doctrineContainer->getEntityManager());

        foreach ($backends as &$backend) {
            if ($backend->isInstalled()) {
                $backend->uninstall();

            }
            
            self::$freeableDatabackends[] = $backend;
            
            $backend = array($backend);
            
        };
        
        return $backends;
    }

    protected function tearDown()
    {
        // Reduce memory foot print
        foreach (self::$freeableDatabackends as $backend) {
            $backend->free();
            
        }
    }

    /**
     * @dataProvider provideInstallationBackends
     */
    public function testInstallation(DataBackend $backend)
    {
        $backend->install();
        $backend->update();
        $backend->uninstall();
    }

    /**
     * @dataProvider provideInstallationBackends
     */
    public function testIsInstalled(DataBackend $backend)
    {
        $backend->install();
        $this->assertTrue($backend->isInstalled());

        $backend->uninstall();
        $this->assertFalse($backend->isInstalled());
    }

    /**
     * @dataProvider provideInstallationBackends
     */
    public function testLastUpdate(DataBackend $backend)
    {
        $now = time();
        $backend->install();
        $update = $backend->getLastUpdate();
        $this->assertGreaterThanOrEqual($now, $update);

        $now = time();
        $backend->update();
        $update = $backend->getLastUpdate();
        $this->assertGreaterThanOrEqual($now, $update);

        $backend->uninstall();
    }

    /**
     * Tests that the backend provides all banks and agencies
     * 
     * @dataProvider provideBackends
     */
    public function testInstallationIsComplete(DataBackend $backend)
    {
        $agencies = array();
        
        foreach ($this->provideBanks() as $referenceBank) {
            $bank = $backend->getBank($referenceBank->getBankID());
            
            $this->assertEquals(
                $referenceBank->getValidationType(),
                $bank->getValidationType()
            );
            
            $agencies[$bank->getMainAgency()->getID()] = $bank->getMainAgency();
            foreach ($bank->getAgencies() as $agency) {
                $agencies[$agency->getID()] = $agency;
                
            }
        }
        
        $expectedAgencies = $this->provideAgencies();
        
        $this->assertEquals(count($expectedAgencies), count($agencies));
        
        foreach ($agencies as $id => $agency) {
            $expectedAgency = $expectedAgencies[$id];
            $this->assertEqualAgency($expectedAgency, $agency);
            
        }
    }
    
    /**
     * Read all banks from the bundesbank file.
     * 
     * @return Bank[]
     */
    private function provideBanks()
    {
        $parser = new FileParser();
        $databackend = new FileDataBackend($parser->getFile());
        $banks = array();
        
        for ($line = 0; $line < $parser->getLines(); $line++) {
            $data = $parser->readLine($line);
            $bank = $parser->getBank($databackend, $data);
            $banks[$bank->getBankID()] = $bank;
            
        }
        
        return $banks;
    }
    
    /**
     * Read all agencies from the bundesbank file.
     * 
     * @return Agency[]
     */
    private function provideAgencies()
    {
        $parser = new FileParser();
        $databackend = new FileDataBackend($parser->getFile());
        $agencies = array();
        
        for ($line = 0; $line < $parser->getLines(); $line++) {
            $data = $parser->readLine($line);
            $bank = $parser->getBank($databackend, $data);
            $agency = $parser->getAgency($bank, $data);
            $agencies[$agency->getID()] = $agency;
            
        }
        
        return $agencies;
    }

    /**
     * Testet, dass ein erneutes $backend->getBank($id) das selbe
     * Objekt zurückliefert.
     *
     * @dataProvider provideBackends
     */
    public function testSingleInstances(DataBackend $backend)
    {
        foreach (self::$referenceBackend->getAllBanks() as $refBank) {
            $this->assertTrue(
                $backend->getBank($refBank->getBankID()) === $backend->getBank($refBank->getBankID()),
                "Different objects for bank {$refBank->getBankID()}"
            );
        }
    }

    /**
     * @dataProvider provideBackends
     */
    public function testGetAllBanks(DataBackend $backend)
    {
        $this->assertEquals(
            count(self::$referenceBackend->getAllBanks()),
            count($backend->getAllBanks())
        );
    }

    /**
     * Test loading all banks
     * 
     * @dataProvider provideBackends
     */
    public function testBanks(DataBackend $backend)
    {
        foreach (self::$referenceBackend->getAllBanks() as $referenceBank) {
            $testedBank = $backend->getBank($referenceBank->getBankID());

            $this->assertEquals(
                $referenceBank->getValidationType(),
                $testedBank->getValidationType()
            );

            $this->assertEqualAgency(
                $referenceBank->getMainAgency(),
                $testedBank->getMainAgency()
            );

            $this->assertEquals(
                count($referenceBank->getAgencies()),
                count($testedBank->getAgencies())
            );
            
        }
    }

    /**
     * Tests loading all agencies
     * 
     * @dataProvider provideBackends
     */
    public function testAgencies(DataBackend $backend)
    {
        foreach (self::$referenceBackend->getAllBanks() as $referenceBank) {
            $testedBank = $backend->getBank($referenceBank->getBankID());
            
            $referenceAgencies = array();
            foreach ($referenceBank->getAgencies() as $agency) {
                $referenceAgencies[$agency->getID()] = $agency;

            }

            foreach ($testedBank->getAgencies() as $agency) {
                $referenceAgency = $referenceAgencies[$agency->getID()];
                
                $this->assertEqualAgency($referenceAgency, $agency);

            }
        }
    }

    private function assertEqualAgency(Agency $a, Agency $b)
    {
        $this->assertTrue($a->getBank()->getBankID() === $b->getBank()->getBankID());
        $this->assertTrue($a->getID()                === $b->getID());
        $this->assertTrue($a->getPostcode()          === $b->getPostcode());
        $this->assertTrue($a->getCity()              === $b->getCity());
        $this->assertTrue($a->getName()              === $b->getName());
        $this->assertTrue($a->getShortTerm()         === $b->getShortTerm());
        $this->assertTrue($a->hasPAN()               === $b->hasPAN());
        $this->assertTrue($a->hasBIC()               === $b->hasBIC());
        if ($a->hasPAN()) {
            $this->assertTrue($a->getPAN() === $b->getPAN());

        }
        if ($a->hasBIC()) {
            $this->assertTrue($a->getBIC() === $b->getBIC());

        }
    }

    /**
     * Test cases for testIsValidBIC()
     * 
     * @see testIsValidBIC()
     * @return array
     */
    public function provideTestIsValidBIC()
    {
        $cases = array();
        foreach ($this->provideBackends() as $backendArray) {
            $backend = $backendArray[0];
            $cases[] = array($backend, true, "VZVDDED1XXX");
            $cases[] = array($backend, true, "VZVDDED1001");
            $cases[] = array($backend, false, "VZVDDED1001X");
            $cases[] = array($backend, false, "");
            $cases[] = array($backend, false, "VZVDDED1~~~");

        }
        return $cases;
    }

    /**
     * Tests DataBackend::isValidBIC();
     *
     * @dataProvider provideTestIsValidBIC
     * @see DataBackend::isValidBIC();
     */
    public function testIsValidBIC(DataBackend $backend, $expected, $bic)
    {
        $this->assertEquals($expected, $backend->isValidBIC($bic));
    }

    /**
     * Test cases for testGetBICAgencies()
     * 
     * @see testGetBICAgencies()
     */
    public function provideTestGetBICAgencies()
    {
        $cases = array();
        foreach ($this->provideBackends() as $backendArray) {
            $backend = $backendArray[0];
            $cases[] = array($backend, "XXX", array());
            $cases[] = array($backend, "VZVDDED1XXX", array("52944"));
            $cases[] = array($backend, "VZVDDED1XXX", array("52944"));
            $cases[] = array($backend, "DELBDE33XXX", array("8536", "13567", "33248", "51683"));

        }
        return $cases;
    }

    /**
     * Tests DataBackend::getBICAgencies()
     * 
     * @dataProvider provideTestGetBICAgencies
     * @see DataBackend::getBICAgencies();
     */
    public function testGetBICAgencies(DataBackend $backend, $bic, $expectedAgencyIds)
    {
        $agencies = $backend->getBICAgencies($bic);
        $getID = function (Agency $agency) {
            return $agency->getID();
        };
        $agenciesIds = array_map($getID, $agencies);
        
        sort($expectedAgencyIds);
        sort($agenciesIds);
        $this->assertEquals($expectedAgencyIds, $agenciesIds);
    }
}

BackendTest::classConstructor();
