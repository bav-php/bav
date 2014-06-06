<?php

namespace malkusch\bav;

require_once __DIR__ . "/../bootstrap.php";

/**
 * Test if the banklist.txt fits into the model.
 */
class DataConstraintTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PDO
     */
    private static $pdo;

    public static function setUpBeforeClass()
    {

        self::$pdo = PDOFactory::makePDO();
        self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        self::$pdo->exec("DROP TABLE IF EXISTS bank");
        self::$pdo->exec(
            "CREATE TEMPORARY TABLE bank (
                id int primary key,
                blz char(8),
                isMain tinyint(1),
                name varchar(58),
                plz varchar(5),
                city varchar(35),
                shortterm varchar(27),
                pan char(5),
                bic varchar(11),
                validator char(2),
                index(blz),
                index(name),
                index(shortterm),
                index(pan),
                index(bic)
            ) engine=MEMORY"
        );

        $fp = fopen(__DIR__ . '/../../data/banklist.txt', 'r');
        if (! is_resource($fp)) {
            throw new RuntimeException('I/O-Error');

        }

        $insert = self::$pdo->prepare(
            "INSERT INTO bank
                   ( id,  blz,  isMain,  name,  plz,  city,  shortterm,  pan,  bic,  validator)
            VALUES (:id, :blz, :isMain, :name, :plz, :city, :shortTerm, :pan, :bic, :validator)"
        );
        $insert->bindParam(':id', $id);
        $insert->bindParam(':blz', $blz);
        $insert->bindParam(':isMain', $isMain);
        $insert->bindParam(':name', $name);
        $insert->bindParam(':plz', $plz);
        $insert->bindParam(':city', $city);
        $insert->bindParam(':shortTerm', $shortTerm);
        $insert->bindParam(':pan', $pan);
        $insert->bindParam(':bic', $bic);
        $insert->bindParam(':validator', $validator);

        while ($line = fgets($fp)) {
            $blz        = substr($line, 0, 8);
            $isMain     = ($line{8} === '1') ? 1 : 0;
            $name       = trim(substr($line, 9, 58));
            $plz        = trim(substr($line, 67, 5));
            $city       = trim(substr($line, 72, 35));
            $shortTerm  = trim(substr($line, 107, 27));
            $pan        = trim(substr($line, 134, 5));
            $bic        = trim(substr($line, 139, 11));
            $validator  = substr($line, 150, 2);
            $id         = substr($line, 152, 6);

            $insert->execute();

        }
        fclose($fp);
    }

    /**
     * @return Array
     */
    public function provideParsedLines()
    {
        $parser = new FileParser();
        $lines  = array();

        for ($i = 0; $i < $parser->getLines(); $i++) {
            $line = $parser->readLine($i);
            $blz  = mb_substr($line, 0, 8, 'UTF-8');
            $type = mb_substr($line, FileParser::TYPE_OFFSET, FileParser::TYPE_LENGTH, 'UTF-8');

            $lines[] = array($blz, $type);
        }

        return $lines;
    }

    /**
     * @dataProvider provideParsedLines
     */
    public function testParser($blz, $type)
    {
        $this->assertRegExp('~^\d{8}$~', $blz);
        $this->assertRegExp('~^[\dA-Z]\d$~', $type);
    }

    /**
     * Every bankID should have exact one validator
     */
    public function testValidatorCount()
    {
        $statement = self::$pdo->query(
            "SELECT blz FROM bank GROUP BY blz HAVING count(DISTINCT validator) != 1"
        );
        $this->assertFalse(
            $statement->fetch(),
            "bankID <-> validator is not n:1!"
        );
    }

    /**
     * Every bankID should have exact one mainAgency
     */
    public function testMainAgency ()
    {
        $statement = self::$pdo->query("SELECT blz FROM bank GROUP BY blz HAVING SUM(isMain) != 1");
        $this->assertFalse(
            $statement->fetch(),
            "Every bankID should have exact one mainAgency."
        );
    }

    public function testBLZDatatype ()
    {
        $statement = self::$pdo->query("SELECT blz FROM bank WHERE blz LIKE '0%'");
        $this->assertFalse(
            $statement->fetch(),
            "Every bankID should not start with 0."
        );
    }
}
