<?php
namespace IComeFromTheNet\BookMe\Builder;

use \DateTime;
use DBALGateway\Builder\BuilderInterface;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Connection;


/**
 * Common Builder
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
abstract class AbstractBuilder implements BuilderInterface
{
    
    /*
    * @var Doctrine\DBAL\Connection
    */
    protected $db;
    
    
    /*
    * Returns a doctrine DBAL type map
    *
    * @access protected
    * @return array[Doctrine\DBAL\Types\Type]
    */
    abstract protected function getSchema();

    /**
    * Will convert db values to php types
    * Uses doctrine column types
    *
    * @access public
    * @return mixed
    * @param array $result
    */
    protected function convertToPhp(array &$result)
    {
        $platform = $this->getDatabaseAdapter()->getDatabasePlatform();
        $columns = $this->getSchema();
        
        foreach($columns as $key => $column) {
            if(isset($result[$key]) === true) {
                $result[$key] = $column->convertToPHPValue($result[$key],$platform);
            }
        }
        
        return $result;
    }
    
    /**
     * Class Constrcutor
     * 
     * @access public
     * @param Connection $conn the doctrine dbal adapter
     */ 
    public function __construct(Connection $conn)
    {
        $this->db = $conn;
        
    }
    
    
    /**
      *  Convert data array into entity
      *
      *  @return mixed
      *  @param array $data
      *  @access public
      */
    abstract function build($data);
   

    /**
      *  Convert and entity into a data array
      *
      *  @return array
      *  @access public
      */
    abstract function demolish($entity);
    
    
    /**
     * Return the doctrine DBAL adapter
     *  
     * @return Doctrine\DBAL\Connection;
     */ 
    public function getDatabaseAdapter()
    {
        return $this->db;
    }
    
}
/* End of Class */