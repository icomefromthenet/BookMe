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
     * @var DateTime check all conflicts with this calendar year and before this date
     */ 
    protected $oNow;

    /**
     * @var integer the conflicts found
     */ 
    protected $iConflictsFound;
    
    
    
    public function __construct($oNow)
    {
        $this->oNow             = $oNow;
        $this->iConflictsFound  = 0;
    }
    
    
    /**
     * Fetches the schedule calendar year to check on
     * 
     * @access public
     */ 
    public function getNow()
    {
        return $this->oNow;
    }
    
    /**
     * Return the number of conflicts found in the search
     * 
     * @return integer
     */ 
    public function getNumberConflictsFound()
    {
        return $this->iConflictsFound;
    }
    
    /**
     * This is the number of conflicts found
     * 
     * @param integer 
     */ 
    public function setNumberConflictsFound($iConflictsFound)
    {
        $this->iConflictsFound = $iConflictsFound;
    }
    
    //---------------------------------------------------------
    # validation interface
    
    
    public function getRules()
    {
        return [
            'instanceOf' => [
                ['now','DateTime']
            ]
        ];
    }
    
    
    public function getData()
    {
        return [
            'now'   => $this->oNow,
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