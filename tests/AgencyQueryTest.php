<?php

require_once __DIR__ . "/../autoloader/autoloader.php";

/**
 * check BAV_DataBackend_PDO->getAgencies($sql)
 *
 * Copyright (C) 2009  Markus Malkusch <markus@malkusch.de>
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
 * @package test
 * @author Markus Malkusch <markus@malkusch.de>
 * @copyright Copyright (C) 2009 Markus Malkusch
 */


class AgencyQueryTest extends PHPUnit_Framework_TestCase
{


    /**
     * @var BAV_DataBackend_PDO
     */
    private $backend;


    /**
     */
    protected function setUp()
    {
        $this->backend = new BAV_DataBackend_PDO(new PDO('mysql:host=localhost;dbname=test', 'test'));
    }


    public function testOnlyID()
    {
        $agencies = $this->backend->getAgencies(
            'SELECT id FROM bav_agency LIMIT 100');
        $this->assertAgencies($agencies, 100);
    }


    public function testIDAndBank()
    {
        $agencies = $this->backend->getAgencies(
            'SELECT id, bank FROM bav_agency LIMIT 100');
        $this->assertAgencies($agencies, 100);
    }


    public function testNoBank()
    {
        $agencies = $this->backend->getAgencies(
            'SELECT id, name, postcode, city, shortTerm, pan, bic FROM bav_agency LIMIT 100');
        $this->assertAgencies($agencies, 100);
    }


    /**
     * @expectedException BAV_DataBackendException_IO_MissingAttributes
     */
    public function testNoID()
    {
        $result = $this->backend->getAgencies(
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


