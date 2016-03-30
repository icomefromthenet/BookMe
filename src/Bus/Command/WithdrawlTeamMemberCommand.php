<?php
namespace IComeFromTheNet\BookMe\Bus\Command;

use IComeFromTheNet\BookMe\Bus\Middleware\ValidationInterface;
use IComeFromTheNet\BookMe\Bus\Listener\HasEventInterface;
use IComeFromTheNet\BookMe\Bus\Listener\CommandEvent;
use IComeFromTheNet\BookMe\BookMeEvents;
use IComeFromTheNet\BookMe\Bus\Command\AssignTeamMemberCommand;


/**
 * This command is used to remove a member from a team
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class WithdrawlTeamMemberCommand extends AssignTeamMemberCommand
{

    
    //----------------------------------------------------------------
    # Has Event Interface
    
    public function getEvent()
    {
      return new CommandEvent($this);
    }
    
    
    public function getEventName()
    {
        return BookMeEvents::TEAM_MEMBER_WITHDRAWL;  
    }
    

}
/* End of Clas */