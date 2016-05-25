<?php
namespace IComeFromTheNet\BookMe\Bus\Command;

use IComeFromTheNet\BookMe\Bus\Middleware\ValidationInterface;
use IComeFromTheNet\BookMe\Bus\Listener\HasEventInterface;
use IComeFromTheNet\BookMe\Bus\Listener\CommandEvent;
use IComeFromTheNet\BookMe\BookMeEvents;


/**
 * This command is used to rollover last years timeslots
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class RolloverTimeslotCommand implements ValidationInterface, HasEventInterface
{

    
    /**
     * @var integer the calendar year to rollover
     */ 
    protected $iCalendarYearRollover;
  
    /**
     * @var integer number of schedules that been rolledover
     */ 
    protected $iRolloverNumber;
    
    
    public function __construct($iCalendarYearRollover)
    {
        $this->iCalendarYearRollover    = $iCalendarYearRollover;
        
    }
    
    
    /**
    * Return the calendar year to rollover
    * 
    * @return integer 
    */ 
    public function getCalendarYearRollover()
    {
        return $this->iCalendarYearRollover;
    }
    
    /**
    * Return Number of timeslots rolledover
    * 
    * @return integer 
    */ 
    public function getRollOverNumber()
    {
        return $this->iRolloverNumber;
    }
    
    /**
    * Fetch Number of timeslots rolledover
    * 
    * @param integer    $iRolloverNumber    The number of schedules rolledover during this command
    */ 
    public function setRollOverNumber($iRolloverNumber)
    {
        return $this->iRolloverNumber = $iRolloverNumber;
    }
    
    
    //---------------------------------------------------------
    # validation interface
    
    
    public function getRules()
    {
        return [
            'integer' => [
                ['calendar_year']
            ]
            ,'min' => [
                ['calendar_year',2000]
            ]
            ,'required' => [
                ['calendar_year']
            ]
        ];
    }
    
    
    public function getData()
    {
        return [
            'calendar_year' => $this->iCalendarYearRollover,
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
        return BookMeEvents::SLOT_ROLLOVER;  
    }

  
}
/* End of Clas */