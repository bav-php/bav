<?php

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";

/**
 * Tests ValidatorFactory.
 *
 * @author Markus Malkusch <markus@malkusch.de>
 * @license WTFPL
 * @see ValidatorFactory
 */
class ValidatorFactoryTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @var ValidatorFactory
     */
    private $factory;
    
    protected function setUp()
    {
        $this->factory = new ValidatorFactory();
    }
    
    /**
     * Tests build()
     * 
     * @param string $type Validation type
     * @see ValidatorFactory::build()
     * @dataProvider provideTestBuild
     */
    public function testBuild($type)
    {
        $bav = new BAV();
        $bank = new Bank($bav->getDataBackend(), null, $type);
        
        $validator = $this->factory->build($bank);
        $this->assertInstanceOf("\\malkusch\\bav\\Validator$type", $validator);
    }
    
    /**
     * Test cases for testBuild()
     * 
     * @return string[][]
     * @see testBuild()
     */
    public function provideTestBuild()
    {
        $cases = array();
        
        $files = ClassFile::getClassFiles(__DIR__.'/../../classes/validator/validators/');
        foreach ($files as $class) {
            if (! preg_match('~^Validator([A-Z0-9]{2})$~', $class->getName(), $matchType)) {
                continue;

            }
            $type = $matchType[1];
            $cases[] = array($type);
            
        }
        
        return $cases;
    }
}
