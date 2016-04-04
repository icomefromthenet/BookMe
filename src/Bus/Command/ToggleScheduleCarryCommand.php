<?php
namespace IComeFromTheNet\BookMe\Bus\Command;

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
class ToggleScheduleCarryCommand implements ValidationInterface 
{
    
    /**
     * @var integer the database id of the new schedule once created
     */ 
    protected $iScheduleDatabaseId;
    
    
    
    public function __construct($iScheduleDatabaseId)
    {
        $this->iScheduleDatabaseId      = $iScheduleDatabaseId;
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