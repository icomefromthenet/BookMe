<?php
namespace IComeFromTheNet\BookMe\Bus\Command;

use IComeFromTheNet\BookMe\Bus\Middleware\ValidationInterface;
use IComeFromTheNet\BookMe\Bus\Listener\HasEventInterface;
use IComeFromTheNet\BookMe\Bus\Listener\CommandEvent;
use IComeFromTheNet\BookMe\BookMeEvents;


/**
 * This command is used to add a new member to a team
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class AssignTeamMemberCommand implements  HasEventInterface, ValidationInterface
{

 
    /**
    * @var integer the database id once the registration is complete
    */ 
    protected $iMemberDatabaseId;
    
    /**
     * @var integer the database id of the team
     */ 
    protected $iTeamDatabaseId;
    
    /**
     * @var integer is the schedule instance to join
     */ 
    protected $iScheduleId;
    
    
    public function __construct($iMemberDatabaseId, $iTeamDatabaseId, $iScheduleId)
    {
        $this->iMemberDatabaseId = $iMemberDatabaseId;
        $this->iTeamDatabaseId   = $iTeamDatabaseId;
        $this->iScheduleId       = $iScheduleId;
    }
    
    
    
    
    /**
    * Fetch the database id of the member to assign
    * 
    * @access public
    */ 
    public function getMemberId()
    {
      return $this->iMemberDatabaseId;
    }
    
    /**
    * Fetch the database id of the team to assign
    * 
    * @access public
    */ 
    public function getTeamId()
    {
      return $this->iTeamDatabaseId;
    }
    
    /**
     * Fetches the database id of the schedule to use
     * 
     * @access public
     */ 
    public function getScheduleId()
    {
        return $this->iScheduleId;
    }
    
    //---------------------------------------------------------
    # validation interface
    
    
    public function getRules()
    {
        return [
            'integer' => [
                ['team_id'], ['member_id'], ['schedule_id']
            ]
            ,'min' => [
                ['team_id',1], ['member_id',1], ['schedule_id',1]
            ]
            ,'required' => [
                ['team_id'], ['member_id'], ['schedule_id']
            ]
        ];
    }
    
    
    public function getData()
    {
        return [
            'team_id' => $this->iTeamDatabaseId,
            'member_id' => $this->iMemberDatabaseId,
            'schedule_id' => $this->iScheduleId,
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
        return BookMeEvents::TEAM_MEMBER_ASSIGN;  
    }
    

}
/* End of Clas */