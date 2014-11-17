<?php
namespace IComeFromTheNet\BookMe\Events;

use DateTime;
use IComeFromTheNet\BookMe\BookMeException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\DBAL\Types\Type;

class AppActivityLogHandler implements EventSubscriberInterface
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
    protected function getSchema()
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
    
    /**
     * Ensure that values are within acceptable ranges.
     * 
     * For example doctrine will not valdiate sizes for varchar fields
     * 
     * @param   string  $param      The database colum to validate
     * @param   mixed   $value      The value to validate
     * @throws BookMeException if error found
     */ 
    protected function validate($param,$value)
    {
        $length = 0;
        $valid = true;
        
        switch($param) {
            case 'activity_name' :
                $length = 32;
                $length = 255;
                if(mb_strlen($param) <= $length) {
                    $valid = true;   
                }
            break;
            case 'activity_description': 
            case 'username':
                $length = 255;
                if(mb_strlen($param) <= $length) {
                    $valid = true;
                }
                
            break;
        }
        
        if(false === $valid) {
            throw new BookMeException("The param $param must be under equal to length $length");
        }
        
        return $valid;
    }
    
    
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
    protected function writeLog($activityName,$activityDescription,$username,$entityID = null)
    {
       $db = $this->getDatabaseAdapter();
    
       # validate params
       $this->validate('activity_name',$activityName);
       $this->validate('activity_description',$activityDescription);
       $this->validate('username',$username);
       
       try {
           
            $sql = 'INSERT INTO app_activity_log (activity_id,activity_date,activity_name,activity_description,username,entity_id) 
               VALUES (null,NOW(),:activity_name,:activity_description,:username,:entity_id)';
       
            $params = array(':activity_name'=> $activityName
                        ,':activity_description' => $activityDescription
                        ,':username' => $username
                        ,':entity_id' => $entityID);
       
           
            $returnedRows = $db->executeUpdate($sql,$params,$this->getSchema());
       }
       catch(DBALException $e) {
           throw new BookMeException($e->getMessage(),0,$e);
       }
        
        # fetch activity id    
        
    }
    
    //--------------------------------------------------------------------------
    # Constructor
    
    public function __construct(Connection $doctrine) 
    {
        $this->doctrine = $doctrine;
    }
    
    
    //-------------------------------------------------------------------------
    # Event Handlers and EventSubscriberInterface
    
    public static function getSubscribedEvents()
    {
        return array(
            BookMeEvents::MemberRegistered => array('onMembershipRego',0) 
        );
    }
    
    public function onMembershipRego(Event $event)
    {
        
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
/* End of Class */
