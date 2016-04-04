<?php
namespace IComeFromTheNet\BookMe\Bus\Command;

use DateTime;
use IComeFromTheNet\BookMe\Bus\Middleware\ValidationInterface;
use IComeFromTheNet\BookMe\Bus\Listener\HasEventInterface;
use IComeFromTheNet\BookMe\Bus\Listener\CommandEvent;
use IComeFromTheNet\BookMe\BookMeEvents;


/**
 * This command is used to create a close a schedule which blackout availability
 * from the given date and stop carryon into future calendar years 
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class StopScheduleCommand implements ValidationInterface, HasEventInterface
{

    
    /**
     * @var integer the database id of the new schedule once created
     */ 
    protected $iScheduleDatabaseId;
    
    /**
     * @var date to stop the schedule on
     */ 
    protected $oStopDate;
    
    
    public function __construct($iScheduleDatabaseId, DateTime $oStopDate)
    {
        $this->oStopDate                = $oStopDate;
        $this->iScheduleDatabaseId      = $iScheduleDatabaseId;
    }
    
   
    
    /**
    * Return the date to backout availability from
    * 
    * @return DateTime 
    */ 
    public function getStopDate()
    {
        return $this->oStopDate;
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
                ['schedule_id']
            ]
            ,'min' => [
                ['schedule_id',1]
            ]
            ,'required' => [
                ['stop_date'], ['schedule_id']
            ]
        ];
    }
    
    
    public function getData()
    {
      
      return [
        'stop_date'   => $this->oStopDate,
        'schedule_id' => $this->iScheduleDatabaseId,
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
        return BookMeEvents::SCHEDULE_STOP;  
    }

  
}
/* End of Clas */