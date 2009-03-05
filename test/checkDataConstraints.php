#!/usr/bin/php
<?php
/**
 * This scripts checks if the banklist.txt fits into our model.
 */
 
 
require_once dirname(__FILE__).'/../classes/autoloader/BAV_Autoloader.php';
BAV_Autoloader::add('../classes/dataBackend/fileParser/BAV_FileParser.php');

class CheckDataConstraints {


    private
    /**
     * @var PDO
     */
    $dbh;
    
    
    public function __construct() {
        $this->dbh = new PDO('mysql:host=localhost;dbname=test', 'test');
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->dbh->beginTransaction();
    }
    
    
    public function __destruct() {
        $this->dbh->commit();
        $this->dbh = null;
    }
    
    
    public function run() {
        $this->initTable(false);
        $this->checkParser();
        $this->checkValidatorCount();
        $this->checkMainAgency();
        $this->checkBLZDatatype();
    }
    
    
    private function checkParser() {
        $parser = new BAV_FileParser();
        
        for($i = 0; $i < $parser->getLines(); $i++) {
            $line = $parser->readLine($i);
            $blz  = mb_substr($line, 0, 8, 'UTF-8');
            $type = mb_substr($line, BAV_FileParser::TYPE_OFFSET, BAV_FileParser::TYPE_LENGTH, 'UTF-8');
            
            if (! preg_match('~^\d{8}$~', $blz)) {
                throw new LogicException("Invalid BLZ: '$blz'");
            
            }
            if (! preg_match('~^[\dA-Z]\d$~', $type)) {
                throw new LogicException("Invalid Type: '$type' in line:\n'$line'");
            
            }
        }
    }
    
    
    /**
     * Every bankID should have exact one validator
     */
    private function checkValidatorCount() {
        $statement = $this->dbh->query("SELECT blz FROM bank GROUP BY blz HAVING count(DISTINCT validator) != 1");
        if($statement->fetch() !== false) {
            throw new RuntimeException("bankID <-> validator is not n:1!");
        
        }
    }
    
    
    /**
     * Every bankID should have exact one mainAgency
     */
    private function checkMainAgency () {
        $statement = $this->dbh->query("SELECT blz FROM bank GROUP BY blz HAVING SUM(isMain) != 1");
        if($statement->fetch() !== false) {
            throw new RuntimeException("Every bankID should have exact one mainAgency");
        
        }
    }
    
    
    /**
     * Every bankID should have exact one mainAgency
     */
    private function checkBLZDatatype () {
        $statement = $this->dbh->query("SELECT blz FROM bank WHERE blz LIKE '0%'");
        if($statement->fetch() !== false) {
            throw new RuntimeException("Every bankID should not start with 0.");
        
        }
    }


    /**
     * Creates a clean database environment
     * @param bool $useTemp wether to work with temporary tables
     */
    private function initTable($useTemp = true) {
        if (! $useTemp) {
            $this->dbh->exec("DROP TABLE IF EXISTS bank");
            
        }
        $this->dbh->exec("CREATE ".($useTemp ? "TEMPORARY" : '')." TABLE IF NOT EXISTS bank (
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
        ) engine=InnoDB");
   
        $fp = fopen(dirname(realpath(__FILE__)).'/../data/banklist.txt', 'r');
        if (! is_resource($fp)) {
            throw new RuntimeException('I/O-Error');
        
        }
        
        $insert = $this->dbh->prepare("INSERT INTO bank (id, blz, isMain, name, plz, city, shortterm, pan, bic, validator)
                                VALUES (:id, :blz, :isMain, :name, :plz, :ort, :shortTerm, :pan, :bic, :validator)");
        $insert->bindParam(':id',         $id);
        $insert->bindParam(':blz',        $blz);
        $insert->bindParam(':isMain',     $isMain);
        $insert->bindParam(':name',       $name);
        $insert->bindParam(':plz',        $plz);
        $insert->bindParam(':ort',        $ort);
        $insert->bindParam(':shortTerm',  $shortTerm);
        $insert->bindParam(':pan',        $pan);
        $insert->bindParam(':bic',        $bic);
        $insert->bindParam(':validator',  $validator);
        
        while ($line = fgets($fp)) {
            $blz        = substr($line, 0, 8);
            $isMain     = ($line{8} === '1') ? 1 : 0;
            $name       = trim(substr($line, 9, 58));
            $plz        = trim(substr($line, 67, 5));
            $ort        = trim(substr($line, 72, 35));
            $shortTerm  = trim(substr($line, 107, 27));
            $pan        = trim(substr($line, 134, 5));
            $bic        = trim(substr($line, 139, 11));
            $validator  = substr($line, 150, 2);
            $id         = substr($line, 152, 6);
        
            $insert->execute();
        
        }
        fclose($fp);
    }

}


$checks = new CheckDataConstraints();
$checks->run();


?>