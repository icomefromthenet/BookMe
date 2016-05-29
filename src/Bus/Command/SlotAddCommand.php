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
  
  /**
   * @var integer the calender year to add slot too
   */ 
  protected $iCalYear;
  
    
  public function __construct($iSlotLength, $iCalYear)
  {
        $this->iSlotLength = (integer) $iSlotLength;    
        $this->iCalYear    = (integer) $iCalYear; 
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
  
  /**
   * Fetch the calednar year to add slot on
   * 
   * @return integer  
   */ 
  public function getCalendarYear()
  {
      return $this->iCalYear;
  }
  
  
  //---------------------------------------------------------
  # validation interface
  
  
  public function getRules()
  {
      // Max 12 hours 720 minutes
      
      return [
        'integer' => [
            ['slot_length'],['cal_year']
        ]
        ,'min' => [
           ['slot_length',2], ['cal_year',2000]
        ]
        ,'max' => [
           ['slot_length',720], ['cal_year',3000]
        ]
      ];
  }
  
  
  public function getData()
  {
      return [
        'slot_length' => $this->iSlotLength,
        'cal_year'    => $this->iCalYear
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