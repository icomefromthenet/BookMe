<?php
namespace IComeFromTheNet\BookMe\Events;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Types\Type;
use IComeFromTheNet\BookMe\BookMeException;


/**
 * Database App Writter.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class AppDatabaseLogger implements AppLoggerInterface
{
    
    /**
     *  @var Doctrine\DBAL\Connection
     */ 
    protected $doctrine;    
    
    /**
     * @var array of doctrine schema types
     */ 
    protected $schema;
    
    
    /**
     *  Fetch a doctrine schema map.
     * 
     * @return array[Doctrine\DBAL\Types\Type]
     */ 
    public function getSchema()
    {
        if($this->schema == null) {
            
            $types = array();
            
            $type['activity_id']            = Type::getType('integer');
            $type['activity_date']          = Type::getType('datetime');
            $type['activity_name']          = Type::getType('string');
            $type['activity_description']   = Type::getType('string');
            $type['usename']                = Type::getType('string');
            $type['entity_id']              = Type::getType('integer');
        
            $this->schema = $types;
        }
        
        return $this->schema;
    }
    
    //--------------------------------------------------------------------------
    # AppLoggerInterface
    
    /**
     * Write the event log
     * 
     * @access  protected
     * @param   string  The name of the activity
     * @param   string  A description of the activity
     * @param   string  The user who did the activity
     * @param   integer  An optional entity id. 
     * 
     */ 
    public function writeLog($activityName,$activityDescription,$username,$entityID = null)
    {
       $db = $this->getDatabaseAdapter();
    
       # validate params
       $this->validate('activity_name',$activityName);
       $this->validate('activity_description',$activityDescription);
       $this->validate('username',$username);
       
       try {
           
            $sql = 'INSERT INTO app_activity_log (activity_id,activity_date,activity_name,activity_description,username,entity_id) 
               VALUES (NULL,NOW(),:activity_name,:activity_description,:username,:entity_id)';
       
            $params = array(':activity_name'=> $activityName
                        ,':activity_description' => $activityDescription
                        ,':username' => $username
                        ,':entity_id' => $entityID);
       
           
            $db->executeUpdate($sql,$params,$this->getSchema());
       }
       catch(DBALException $e) {
           throw new BookMeException($e->getMessage(),0,$e);
       }
        
        # fetch activity id    
        return $db->lastInsertId();
    }
    
    
    /**
     * Ensure that values are within acceptable ranges.
     * 
     * For example doctrine will not valdiate sizes for varchar fields
     * 
     * @param   string  $param      The database colum to validate
     * @param   mixed   $value      The value to validate
     * @throws BookMeException if error found
     */ 
    public function validate($param,$value)
    {
        $length = 0;
        $valid = true;
        
        switch($param) {
            case 'activity_name':
                $length = 32;
                if(mb_strlen($value) > $length || empty($value)) {
                    $valid = false;   
                }
            break;
            case 'activity_description': 
            case 'username':
                $length = 255;
                if(mb_strlen($value) > $length || empty($value)) {
                    $valid = false;
                }
                
            break;
            default : $valid = null;
        }
        
        if(false === $valid) {
            throw new BookMeException("The param $param must be under or equal to length $length and not empty");
        }
        
        return $valid;
    }
    
    
    //--------------------------------------------------------------------------
    # Constructor
    
    public function __construct(Connection $doctrine) 
    {
        $this->doctrine = $doctrine;
        
    }
    
    //--------------------------------------------------------------------------
    # Properties
    
    
    /**
     *  Gets the database adapter
     * 
     * @access public
     * @return  Doctrine\DBAL\Connection
     */ 
    public function getDatabaseAdapter()
    {
        return $this->doctrine;
    }
    
}
/* End of class */ 