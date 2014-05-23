<?php
/**
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


require_once __DIR__ . '/AgencyQueryTest.php';
require_once __DIR__ . '/BackendTest.php';
require_once __DIR__ . '/DataConstraintTest.php';
require_once __DIR__ . '/ValidatorTest.php';
require_once __DIR__ . '/VerifyImportTest.php';


class TestSuite extends PHPUnit_Framework_TestSuite
{
	
	
	/**
	 * @return TestSuite
	 */
    public static function suite()
    {
        $suite = new self();
        
        $suite->addTestSuite('AgencyQueryTest');
        $suite->addTestSuite('DataConstraintTest');
        $suite->addTestSuite('ValidatorTest');
        $suite->addTestSuite('VerifyImportTest');
        $suite->addTestSuite('BackendTest');
        
        return $suite;
    }
	
	
}


