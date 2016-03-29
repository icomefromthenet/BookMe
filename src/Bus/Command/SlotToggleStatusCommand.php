<?php
namespace IComeFromTheNet\BookMe\Bus\Command;

use IComeFromTheNet\BookMe\Bus\Middleware\ValidationInterface;
use IComeFromTheNet\BookMe\Bus\Listener\HasEventInterface;
use IComeFromTheNet\BookMe\Bus\Listener\CommandEvent;
use IComeFromTheNet\BookMe\BookMeEvents;


/**
 * This command is used to add a new slot
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class SlotToggleStatusCommand implements ValidationInterface
{

  
    /**
     * @var integer database if
     */ 
    protected $iTimeslotDatabaseId;
  
    
    public function __construct($iTimeslotDatabaseId)
    {
        $this->iTimeslotDatabaseId = $iTimeslotDatabaseId;    
    }

  
    /**
    * Fetch the database id of the new timeslot
    * 
    * @access public
    */ 
    public function getTimeSlotId()
    {
      return $this->iTimeslotDatabaseId;
    }
    
    //---------------------------------------------------------
    # validation interface
    
    
    public function getRules()
    {
      // Max 12 hours 720 minutes
      
      return [
        'integer' => [
            ['timeslot_id']
        ]
        ,'required' => [
           ['timeslot_id']
        ]
      ];
    }
    
    
    public function getData()
    {
      return [
        'timeslot_id' => $this->iTimeslotDatabaseId
      ];
    }
    

  
}
/* End of Clas */