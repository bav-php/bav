<?php

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";
require_once __DIR__ . "/../../vendor/autoload.php";

/**
 * Tests the Backends.
 * This test needs some memory (about 800M)!
 *
 * @license GPL
 * @author Markus Malkusch <markus@malkusch.de>
 */
class BackendTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var FileDataBackend
     */
    private static $referenceBackend;

    /**
     * Defines the reference backend
     */
    public static function classConstructor()
    {
        self::$referenceBackend = new FileDataBackend();
    }

    /**
     * @return Array The tested backends
     */
    public function provideBackends()
    {
        $pdoBackend = new PDODataBackend(PDOFactory::makePDO());
        $this->setupBackend($pdoBackend);

        $fileBackend = new FileDataBackend();
        $this->setupBackend($fileBackend);
        
        $conn = array(
            'pdo' => PDOFactory::makePDO(),
        );
        $doctrineContainer = DoctrineBackendContainer::buildByConnection($conn, true);

        return array(
            array($pdoBackend),
            array($fileBackend),
            array($doctrineContainer->getDataBackend()),
        );
    }

    private function setupBackend(DataBackend $backend)
    {
        if ($backend->isInstalled()) {
            return;

        }
        $backend->install();
    }

    /**
     */
    public function provideInstallationBackends()
    {
        $pdoBackend = new PDODataBackend(PDOFactory::makePDO(), 'bavtest_');
        $this->setupInstallationBackends($pdoBackend);

        $fileUtil = new FileUtil();

        $fileBackend = new FileDataBackend(tempnam($fileUtil->getTempDirectory(), 'bavtest'));
        $this->setupInstallationBackends($fileBackend);
        
        $conn = array(
            'driver' => 'pdo_sqlite',
            'path' => ":memory:"
        );
        $doctrineContainer = DoctrineBackendContainer::buildByConnection($conn, true);
        $doctrineBackend = $doctrineContainer->getDataBackend();
        $this->setupInstallationBackends($doctrineBackend);

        return array(
            array($pdoBackend),
            array($fileBackend),
            array($doctrineBackend),
        );
    }

    private function setupInstallationBackends(DataBackend $backend)
    {
        if (! $backend->isInstalled()) {
            return;

        }
        $backend->uninstall();
    }

    /**
     * @return Array
     */
    public function provideBanks()
    {
        $banks = array();
        foreach ($this->provideBackends() as $backendArray) {
            $backend = $backendArray[0];
            foreach (self::$referenceBackend->getAllBanks() as $bank) {
                $comparedBank = $backend->getBank($bank->getBankID());
                $banks[] = array($bank, $comparedBank);

            }
        }
        return $banks;
    }

    /**
     * @return Array
     */
    public function provideAgencies()
    {
        $agencies = array();
        foreach ($this->provideBanks() as $banks) {
            $referenceBank = $banks[0];
            $testedBank    = $banks[1];

            $referenceAgencies = array();
            foreach ($referenceBank->getAgencies() as $agency) {
                $referenceAgencies[$agency->getID()] = $agency;

            }

            foreach ($testedBank->getAgencies() as $agency) {
                $agencies[] = array(
                    $referenceAgencies[$agency->getID()],
                    $agency
                );

            }

        }
        return $agencies;
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
     * @dataProvider provideInstallationBackends
     */
    public function testInstallationIsComplete(DataBackend $backend)
    {
        $this->markTestIncomplete();
        //TODO test if the installation process fills all banks
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
     * @dataProvider provideBanks
     */
    public function testBanks(Bank $referenceBank, Bank $testedBank)
    {
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

    /**
     * @dataProvider provideAgencies
     */
    public function testAgencies(Agency $referenceAgency, Agency $testedAgency)
    {
        $this->assertEqualAgency($referenceAgency, $testedAgency);
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
