<?php

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";

/**
 * Tests StatementContainer.
 *
 * @license WTFPL
 * @author Markus Malkusch <markus@malkusch.de>
 * @see StatementContainer
 */
class StatementContainerTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @var StatementContainer
     */
    private $statementContainer;
    
    public function setUp()
    {
        $this->statementContainer = new StatementContainer(PDOFactory::makePDO());
    }
    
    /**
     * Tests two equal queries should return the same object
     */
    public function testStoring()
    {
        $query = "SELECT :param as test FROM DUAL";
        $stmt1 = $this->statementContainer->prepare($query);
        $stmt2 = $this->statementContainer->prepare($query);
        
        $this->assertSame($stmt1, $stmt2);
    }
    
    /**
     * Tests the query
     */
    public function testQuery()
    {
        $query = "SELECT :param as test FROM DUAL";

        $stmt = $this->statementContainer->prepare($query);
        $stmt->execute(array(":param" => 5));
        $this->assertEquals(array("5"), $stmt->fetchAll(\PDO::FETCH_COLUMN));
        
        $stmt2 = $this->statementContainer->prepare($query);
        $stmt2->execute(array(":param" => 4));
        $this->assertEquals(array("4"), $stmt2->fetchAll(\PDO::FETCH_COLUMN));
    }
}
