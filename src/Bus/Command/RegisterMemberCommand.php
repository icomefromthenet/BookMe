<?php
namespace IComeFromTheNet\BookMe\Bus\Command;

use IComeFromTheNet\BookMe\Bus\Middleware\ValidationInterface;
use IComeFromTheNet\BookMe\Bus\Listener\HasEventInterface;
use IComeFromTheNet\BookMe\Bus\Listener\CommandEvent;
use IComeFromTheNet\BookMe\BookMeEvents;


/**
 * This command is used to add a new member so they can create schedules
 * 
 * This would be stored against a user who needs schedules.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class RegisterMemberCommand implements  HasEventInterface
{

 
  /**
   * @var integer the database id once the registration is complete
   */ 
  protected $iMemberDatabaseId;
  
    
  public function __construct()
  {
 
  }
  
  
  
  /**
   * Set the database id of this new member
   * 
   * @param integer     $iMemberDatabaseId    The database id
   */ 
  public function setMemberId($iMemberDatabaseId)
  {
      $this->iMemberDatabaseId = $iMemberDatabaseId;
  }
  
  /**
   * Fetch the database id of the new member
   * 
   * @access public
   */ 
  public function getMemberId()
  {
      return $this->iMemberDatabaseId;
  }
  
  
  //----------------------------------------------------------------
  # Has Event Interface
  
  public function getEvent()
  {
      return new CommandEvent($this);
  }
  
    
  public function getEventName()
  {
    return BookMeEvents::MEMBER_REGISTER;  
  }
  
  
}
/* End of Clas */