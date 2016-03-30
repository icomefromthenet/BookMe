<?php
namespace IComeFromTheNet\BookMe\Bus\Command;

use IComeFromTheNet\BookMe\Bus\Middleware\ValidationInterface;
use IComeFromTheNet\BookMe\Bus\Listener\HasEventInterface;
use IComeFromTheNet\BookMe\Bus\Listener\CommandEvent;
use IComeFromTheNet\BookMe\BookMeEvents;


/**
 * This command is used to add a new team
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class RegisterTeamCommand implements  HasEventInterface, ValidationInterface
{

 
  /**
   * @var integer the database id once the registration is complete
   */ 
  protected $iTeamDatabaseId;
  
  /**
   * @var integer the database id of the timeslot type to use
   */ 
  protected $iTeamTimeSlotId;
    
    
    
  public function __construct($iTeamTimeSlotId)
  {
    $this->iTeamTimeSlotId = $iTeamTimeSlotId;    
  }
  
  
  /**
   * Load the assigned timeslot type for this team
   * 
   * @return integer the database if of timeslot type
   * 
   */ 
  public function getTimeSlotId()
  {
      return $this->iTeamTimeSlotId;
  }
  
  
  /**
   * Set the database id of this new team
   * 
   * @param integer     $iTeamDatabaseId    The database id
   */ 
  public function setTeamId($iTeamDatabaseId)
  {
      $this->iTeamDatabaseId = $iTeamDatabaseId;
  }
  
  /**
   * Fetch the database id of the new team
   * 
   * @access public
   */ 
  public function getTeamId()
  {
      return $this->iTeamDatabaseId;
  }
  
  //---------------------------------------------------------
  # validation interface
  
  
  public function getRules()
  {
      return [
        'integer' => [
            ['timeslot_id']
        ]
        ,'min' => [
           ['timeslot_id',1]
        ]
        ,'required' => [
            ['timeslot_id']     
        ]
      ];
  }
  
  
  public function getData()
  {
      return [
        'timeslot_id' => $this->iTeamTimeSlotId
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
    return BookMeEvents::TEAM_REGISTER;  
  }
  
  
}
/* End of Clas */