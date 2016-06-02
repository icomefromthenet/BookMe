<?php
namespace IComeFromTheNet\BookMe\Bus\Command;

use IComeFromTheNet\BookMe\Bus\Middleware\ValidationInterface;
use IComeFromTheNet\BookMe\Bus\Listener\HasEventInterface;
use IComeFromTheNet\BookMe\Bus\Listener\CommandEvent;
use IComeFromTheNet\BookMe\BookMeEvents;


/**
 * This command is used to rollover last years schedules.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class RolloverSchedulesCommand implements ValidationInterface, HasEventInterface
{

    
    /**
     * @var integer the calendar year to rollover
     */ 
    protected $iNextCalendarYearRollover;
  
    /**
     * @var integer number of schedules that been rolledover
     */ 
    protected $iRolloverNumber;
    
    
    public function __construct($iNextCalendarYearRollover)
    {
        $this->iNextCalendarYearRollover    = $iNextCalendarYearRollover;
        
    }
    
    
    /**
    * Return the calendar year to rollover
    * 
    * @return integer 
    */ 
    public function getNewCalendarYear()
    {
        return $this->iNextCalendarYearRollover;
    }
    
    /**
    * Return Number of Schedules rolledover
    * 
    * @return integer 
    */ 
    public function getRollOverNumber()
    {
        return $this->iRolloverNumber;
    }
    
    /**
    * Fetch Number of Schedules rolledover
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
            'calendar_year' => $this->iNextCalendarYearRollover,
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
        return BookMeEvents::SCHEDULE_ROLLOVER;  
    }

  
}
/* End of Clas */