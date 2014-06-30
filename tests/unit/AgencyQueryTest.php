<?php

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";

/**
 * Test SQLDataBackend::getAgencies($sql)
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @licends GPL
 */
class AgencyQueryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @return SQLDataBackend[][]
     */
    public function provideBackends()
    {
        $conn = array(
            "pdo" => PDOFactory::makePDO(),
        );
        $doctrineContainer = DoctrineBackendContainer::buildByConnection($conn, true);
        
        return array(
            array(new PDODataBackend(PDOFactory::makePDO())),
            array($doctrineContainer->getDataBackend())
        );
    }

    /**
     * @dataProvider provideBackends
     */
    public function testOnlyID(SQLDataBackend $backend)
    {
        $agencies = $backend->getAgencies(
            'SELECT id FROM bav_agency LIMIT 100'
        );
        $this->assertAgencies($agencies, 100);
    }

    /**
     * @dataProvider provideBackends
     */
    public function testIDAndBank(SQLDataBackend $backend)
    {
        $agencies = $backend->getAgencies(
            'SELECT id, bank FROM bav_agency LIMIT 100'
        );
        $this->assertAgencies($agencies, 100);
    }

    /**
     * @dataProvider provideBackends
     */
    public function testNoBank(SQLDataBackend $backend)
    {
        $agencies = $backend->getAgencies(
            'SELECT id, name, postcode, city, shortTerm, pan, bic FROM bav_agency LIMIT 100'
        );
        $this->assertAgencies($agencies, 100);
    }

    /**
     * @dataProvider provideBackends
     * @expectedException malkusch\bav\MissingAttributesDataBackendIOException
     */
    public function testNoID(SQLDataBackend $backend)
    {
        $result = $backend->getAgencies(
            'SELECT name, postcode, city, shortTerm, pan, bic, bank FROM bav_agency LIMIT 1'
        );
    }

    private function assertAgencies(Array $agencies, $count)
    {
        $this->assertEquals($count, count($agencies));

        foreach ($agencies as $agency) {
            if ($agency->isMainAgency()) {
                $this->assertTrue(
                    $agency->getBank()->getMainAgency() === $agency,
                    "Inconsistent Main Agency"
                );
                $this->assertTrue(
                    $agency->getBank() === $agency->getBank()->getMainAgency()->getBank(),
                    "Inconsistent Bank"
                );

            } else {
                $includedCount = 0;
                foreach ($agency->getBank()->getAgencies() as $banksAgency) {
                    $this->assertTrue(
                        $banksAgency->getBank() === $agency->getBank(),
                        "Inconsistent bank"
                    );
                    if ($banksAgency === $agency) {
                        $includedCount++;

                    }
                }
                $this->assertEquals(1, $includedCount, "Inconsistent agency");
            }
        }
    }
}
