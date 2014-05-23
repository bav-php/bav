<?php

require_once __DIR__ . "/../autoloader/autoloader.php";

/**
 * Tests the Backends.
 * This test needs some memory (about 400M)!
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

class BackendTest extends PHPUnit_Framework_TestCase
{


    private static
    /**
     * @var BAV_DataBackend_File
     */
    $referenceBackend;


    /**
     * Defines the reference backend
     */
    static public function classConstructor() {
        self::$referenceBackend = new BAV_DataBackend_File();
    }
    
    
    /**
     * @return Array The tested backends
     */
    public function provideBackends() {
        return array(
            array(new BAV_DataBackend_PDO(new PDO('mysql:host=localhost;dbname=test', 'test'))),
            array(new BAV_DataBackend_File())
        );
    }
    
    
    /**
     */
    public function provideInstallationBackends() {
        return array(
            array(new BAV_DataBackend_PDO(new PDO('mysql:host=localhost;dbname=test', 'test'), 'bavtest_')),
            array(new BAV_DataBackend_File(tempnam(BAV_DataBackend_File::getTempdir(), 'bavtest')))
        );
    }
    
    
    /**
     * @return Array
     */
    public function provideBanks() {
    	$banks = array();
    	foreach($this->provideBackends() as $backendArray) {
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
    public function provideAgencies() {
        $agencies = array();
        foreach($this->provideBanks() as $banks) {
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
    public function testInstallation(BAV_DataBackend $backend) {
    	$backend->install();
    	$backend->update();
    	$backend->uninstall();
    }
    
    
    /**
     * @dataProvider provideInstallationBackends
     */
    public function testInstallationIsComplete(BAV_DataBackend $backend) {
        $this->markTestIncomplete();
        //TODO test if the installation process fills all banks
    }
    
    
    /**
     * Testet, dass ein erneutes $backend->getBank($id) das selbe
     * Objekt zurückliefert. 
     * 
     * @dataProvider provideBackends
     */
    public function testSingleInstances(BAV_DataBackend $backend) {
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
    public function testGetAllBanks(BAV_DataBackend $backend) {
    	$this->assertEquals(
            count(self::$referenceBackend->getAllBanks()),
            count($backend->getAllBanks())
        );
    }
    
    
    /**
     * @dataProvider provideBanks
     */
    public function testBanks(BAV_Bank $referenceBank, BAV_Bank $testedBank) {
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
    public function testAgencies(BAV_Agency $referenceAgency, BAV_Agency $testedAgency) {
    	$this->assertEqualAgency($referenceAgency, $testedAgency);
    }
    
    
    private function assertEqualAgency(BAV_Agency $a, BAV_Agency $b) {
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


}
BackendTest::classConstructor();