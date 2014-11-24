<?php
namespace IComeFromTheNet\BookMe\Builder;

use \DateTime;
use DBALGateway\Builder\BuilderInterface;
use IComeFromTheNet\BookMe\Entity\ScheduleGroupEntity;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Connection;


/**
 * Maps a Schedule Group from our Schedule database. 
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class ScheduleGroupBuilder extends AbstractBuilder implements BuilderInterface
{
    
    protected $schema;
    
    
    /*
    * Returns a doctrine DBAL type map
    *
    * @access protected
    * @return array[Doctrine\DBAL\Types\Type]
    */
    protected function getSchema() 
    {
        if($this->schema === NULL) {
            $this->schema = array(
                'valid_to' => Type::getType(Type::DATE),
                'valid_from' => Type::getType(Type::DATE),
                'group_id'  =>  Type::getType(Type::INTEGER),
                'group_name' => Type::getType(Type::STRING)
                
            );
        }
        
        return $this->schema;
    }
    
    
    /**
     * Class Constrcutor
     * 
     * @access public
     * @param Connection $conn the doctrine dbal adapter
     */ 
    public function __construct(Connection $conn)
    {
        parent::__construct($conn);
    }
    
    
    /**
      *  Convert data array into entity
      *
      *  @return mixed
      *  @param array $data
      *  @access public
      */
    public function build($data)
    {
        $entity = new ScheduleGroupEntity();
        
        $this->convertToPHP($data);
        
        foreach($data as $key => $value) {
            
            switch($key) {
                case 'valid_to':
                      $entity->setValidTo($value);
                break;
                case 'valid_from':
                      $entity->setValidFrom($value);
                break;
                case 'group_name':
                     $entity->setName($value);    
                break;    
                case 'group_id':
                     $entity->setGroupID($value);    
                break;    
            }
        }

        return $entity;
    }

    /**
      *  Convert and entity into a data array
      *
      *  @return array
      *  @access public
      */
    public function demolish($entity)
    {

    }
    
}
/* End of Class */