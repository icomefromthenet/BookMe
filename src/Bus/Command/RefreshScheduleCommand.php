<?php
namespace IComeFromTheNet\BookMe\Bus\Command;

use IComeFromTheNet\BookMe\Bus\Middleware\ValidationInterface;
use IComeFromTheNet\BookMe\Bus\Listener\HasEventInterface;
use IComeFromTheNet\BookMe\Bus\Listener\CommandEvent;
use IComeFromTheNet\BookMe\BookMeEvents;


/**
 * This command is used to refresh a schedule by compling the rules
 * that are linked to it.
 * 
 * This will not clear bookings but my leave them in conflict.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class RefreshScheduleCommand implements  ValidationInterface
{

 
    /**
    * @var integer the schedule database id
    */ 
    protected $iScheduleDatabaseId;
    
    
    
    
    public function __construct($iScheduleDatabaseId)
    {
        $this->iScheduleDatabaseId = $iScheduleDatabaseId;
        
    }
    
    
  
    /**
     * Fetches the database id of the schedule to use
     * 
     * @access public
     */ 
    public function getScheduleId()
    {
        return $this->iScheduleDatabaseId;
    }
    
    
   
    
    //---------------------------------------------------------
    # validation interface
    
    
    public function getRules()
    {
        return [
            'integer' => [
                ['schedule_id']
            ]
            ,'min' => [
                ['schedule_id',1]
            ]
            ,'required' => [
                ['schedule_id']
            ]
            
        ];
    }
    
    
    public function getData()
    {
        return [
            'schedule_id' => $this->iScheduleDatabaseId,
        ];
    }
    
    
   

}
/* End of Clas */