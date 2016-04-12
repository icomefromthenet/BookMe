<?php
namespace IComeFromTheNet\BookMe\Bus\Command;

use DateTime;
use IComeFromTheNet\BookMe\Bus\Middleware\ValidationInterface;
use IComeFromTheNet\BookMe\Bus\Listener\HasEventInterface;
use IComeFromTheNet\BookMe\Bus\Listener\CommandEvent;
use IComeFromTheNet\BookMe\BookMeEvents;


/**
 * This command is used to scan for booking conflicts.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class LookBookingConflictsCommand implements  HasEventInterface, ValidationInterface
{

    /**
     * @var integer the year to check on
     */ 
    protected $iCalYear;

    
    
    public function __construct($iCalYear)
    {
        $this->iCalYear      = $iCalYear;
        
    }
    
    
    /**
     * Fetches the schedule calendar year to check on
     * 
     * @access public
     */ 
    public function getCalYear()
    {
        return $this->iCalYear;
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
            'calendar_year'   => $this->iCalYear,
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
        return BookMeEvents::BOOKING_CONFLICT;  
    }
    

}
/* End of Clas */