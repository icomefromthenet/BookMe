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
class SlotAddCommand implements ValidationInterface, HasEventInterface
{

  /**
   * @var integer This is the minute length of slot 
   */
  protected $iSlotLength;


  protected $iTimeslotDatabaseId;
  
    
  public function __construct($iSlotLength)
  {
        $this->iSlotLength = (integer) $iSlotLength;    
  }
  
  
  /**
   * Return the number of calender years to add
   * 
   * @return integer 
   */ 
  public function getSlotLength()
  {
    return $this->iSlotLength;
  }
  
  /**
   * Set the database id of this new timeslot
   * 
   * @param integer     $iTimeslotDatabaseId    The database id
   */ 
  public function setTimeSlotId($iTimeslotDatabaseId)
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
            ['slot_length']
        ]
        ,'min' => [
           ['slot_length',2]
        ]
        ,'max' => [
           ['slot_length',720]
        ]
      ];
  }
  
  
  public function getData()
  {
      return [
        'slot_length' => $this->iSlotLength
      ];
  }
  
  //----------------------------------------------------------------
  # Has Event Interface
  
  public function getEvent()
  {
      return new CommandEvent($this);
  }
  
    
  public function getEventName()
  {
    return BookMeEvents::SLOT_ADD;  
  }
  
  
}
/* End of Clas */