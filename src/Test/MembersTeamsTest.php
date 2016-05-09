<?php
namespace IComeFromTheNet\BookMe\Test;

use Doctrine\DBAL\Types\Type;
use IComeFromTheNet\BookMe\Test\Base\TestSetupBase;
use IComeFromTheNet\BookMe\Bus\Command\RegisterMemberCommand;
use IComeFromTheNet\BookMe\Bus\Command\RegisterTeamCommand;
use IComeFromTheNet\BookMe\Bus\Command\WithdrawlTeamMemberCommand;
use IComeFromTheNet\BookMe\Bus\Command\AssignTeamMemberCommand;

use IComeFromTheNet\BookMe\BookMeService;
use IComeFromTheNet\BookMe\Bus\Exception\MembershipException;



class MembersTeamsTest extends TestSetupBase
{
    
    
   protected $aDatabaseId;    
    
    
   protected function handleEventPostFixtureRun()
   {
      // Create the Calendar 
      $oService = new BookMeService($this->getContainer());
      
      $oService->addCalenderYears(5);
      
      // Create some timeslots
      $iSixMinuteSlotId = $oService->addTimeslot(6);
      
      $this->aDatabaseId  = [
          'slot_six_minute' => $iSixMinuteSlotId,
          
       ];
      
   }  
   
   
    /**
    * @group Setup
    */ 
    public function testMembershipCommands()
    {
        $iSixMinuteSlotId = $this->aDatabaseId['slot_six_minute'];
        
        $iNewMemberId = $this->RegisterNewMember();
                      
        $iNewTeam     = $this->RegisterNewTeam($iSixMinuteSlotId);
       
       
    }
    
    
    protected function RegisterNewMember()
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new RegisterMemberCommand($iSixMinuteSlotId);
       
        $oCommandBus->handle($oCommand);
        
        $iNewMemberId = $oCommand->getMemberId();
        
        $this->assertNotEmpty($iNewMemberId,'The new member command failed to return new member database id');
        
        
        // Check if member exisys
        
        $bFound = (bool) $oContainer
                                 ->getDatabaseAdapter()
                                 ->fetchColumn("select 1
                                                from bm_schedule_membership 
                                                where membership_id = ? ",[$iNewMemberId],0,[]);
       
       
        $this->assertTrue($bFound,'New member could not be found in database'); 
        
        return $iNewMemberId;
        
    }
    
    
    protected function RegisterNewTeam($iSixMinuteSlotId)
    {
        
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new RegisterTeamCommand($iSixMinuteSlotId);
       
        $oCommandBus->handle($oCommand);
        
        $iNewTeamId = $oCommand->getTeamId();
        
        $this->assertNotEmpty($iNewTeamId,'The new team command failed to return new team database id');
        
        // Check if member exisys
        
        $bFound = (bool) $oContainer
                                 ->getDatabaseAdapter()
                                 ->fetchColumn("select 1
                                                from bm_schedule_team 
                                                where team_id = ? ",[$iNewTeamId],0,[]);
       
       
        $this->assertTrue($bFound,'New member could not be found in database'); 
        
        return $iNewMemberId;
        
        
        
        
    }
    
    
    
    
    /*
    public function RegisterTeamMember($iMemberId,$iTeamId,$iScheduleId)
    {
        
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
       
        $oCommand  = new AssignTeamMemberCommand($iMemberId,$iTeamId,$iScheduleId);
       
        $oCommandBus->handle($oCommand);
    
        
        
        
    }
    
    
    public function WithdrawlTeamMember($iMemberId)
    {
        
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new WithdrawlTeamMemberCommand($iMemberId);
       
        $oCommandBus->handle($oCommand);
    
        $bFound = (bool) $oContainer
                                 ->getDatabaseAdapter()
                                 ->fetchColumn("select 1
                                                from bm_schedule_membership 
                                                where membership_id = ? ",[$iMemberId],0,[]);
       
       
        $this->assertFalse($bFound,'Unable to withdrawal a member from team'); 
   
        
        
    }
    */
    
    
}
/* end of file */
