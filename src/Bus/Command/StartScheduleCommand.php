<?php
namespace IComeFromTheNet\BookMe\Bus\Command;

use IComeFromTheNet\BookMe\Bus\Middleware\ValidationInterface;
use IComeFromTheNet\BookMe\Bus\Listener\HasEventInterface;
use IComeFromTheNet\BookMe\Bus\Listener\CommandEvent;
use IComeFromTheNet\BookMe\BookMeEvents;


/**
 * This command is used to create a new schedule
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class StartScheduleCommand implements ValidationInterface, HasEventInterface
{

    /**
    * @var integer The membership id 
    */
    protected $iMemberDatabaseId;
    
    /*
    * @var integer the timeslot type to assign 
    */
    protected $iTimeSlotDatabbaseId;
    
    /**
     * @var integer the calendar year to assign
     */ 
    protected $iCalendarYear;
    
    /**
     * @var integer the database id of the new schedule once created
     */ 
    protected $iScheduleDatabaseId;
    

    
    
    public function __construct($iMemberDatabaseId, $iTimeSlotDatabbaseId, $iCalendarYear)
    {
        $this->iMemberDatabaseId    = $iMemberDatabaseId;
        $this->iTimeSlotDatabbaseId = $iTimeSlotDatabbaseId;
        $this->iCalendarYear        = $iCalendarYear;
        
    }
    
    
    /**
    * Return the calendar year of this schedule
    * 
    * @return integer 
    */ 
    public function getCalendarYear()
    {
        return $this->iCalendarYear;
    }
    
    /**
    * Return the member database id
    * 
    * @return integer 
    */ 
    public function getMemberId()
    {
        return $this->iMemberDatabaseId;
    }
    
    /**
    * Return the timeslot type database id
    * 
    * @return integer 
    */ 
    public function getTimeSlotId()
    {
        return $this->iTimeSlotDatabbaseId;
    }
    
    /**
    * Sets the schedule database id
    * 
    * @return integer 
    */ 
    public function setScheduleId($iScheduleDatabaseId)
    {
        $this->iScheduleDatabaseId = $iScheduleDatabaseId;
    }
    
    /**
    * Return the schedule database id
    * 
    * @return integer 
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
                ['membership_id'], ['timeslot_id'], ['calendar_year']
            ]
            ,'min' => [
                ['calendar_year',2000], ['timeslot_id',1], ['membership_id',1]
            ]
            ,'required' => [
                ['membership_id'], ['timeslot_id'], ['calendar_year']
            ]
        ];
    }
    
    
    public function getData()
    {
      return [
        'calendar_year' => $this->iCalendarYear,
        'timeslot_id'   => $this->iTimeSlotDatabbaseId,
        'membership_id' => $this->iMemberDatabaseId,
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
     return BookMeEvents::SCHEDULE_START;  
    }

  
}
/* End of Clas */