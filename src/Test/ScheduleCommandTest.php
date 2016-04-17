<?php
namespace IComeFromTheNet\BookMe\Test;

use Doctrine\DBAL\Types\Type;
use IComeFromTheNet\BookMe\Test\Base\TestCalendarSlotsGroupBase;
use IComeFromTheNet\BookMe\Bus\Command\StartScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\StopScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\ResumeScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\ToggleScheduleCarryCommand;

use IComeFromTheNet\BookMe\BookMeService;
use IComeFromTheNet\BookMe\Bus\Exception\ScheduleException;



class ScheduleCommandTest extends TestCalendarSlotsGroupBase
{
    
    
    protected $aDatabaseId = [];
    
    
    
   protected function handleEventPostFixtureRun()
   {
      // Create the Calendar 
      $oService = new BookMeService($this->getContainer());
      
      $oService->addCalenderYears(5);
      
      $iFiveMinuteTimeslot    = $oService->addTimeslot(5);
      $iTenMinuteTimeslot     = $oService->addTimeslot(10);
      $iFifteenMinuteTimeslot = $oService->addTimeslot(15);

      $oService->toggleSlotAvability($iTenMinuteTimeslot);    
  
      $iMemberOne   = $oService->registerMembership();
      $iMemberTwo   = $oService->registerMembership();
      $iMemberThree = $oService->registerMembership();
      $iMemberFour  = $oService->registerMembership();
    
      $iTeamOne     = $oService->registerTeam($iFiveMinuteTimeslot);
      $iTeamTwo     = $oService->registerTeam($iFifteenMinuteTimeslot);
            
            
      $this->aDatabaseId = [
        'five_minute'    => $iFiveMinuteTimeslot,
        'ten_minute'     => $iTenMinuteTimeslot,
        'fifteen_minute' => $iFifteenMinuteTimeslot,
        'member_one'     => $iMemberOne,
        'member_two'     => $iMemberTwo,
        'member_three'   => $iMemberThree,
        'member_four'    => $iMemberFour,
        'team_two'       => $iTeamTwo,
        'team_one'       => $iTeamOne,
      ];
      
      
   }  
   
   
    /**
    * @group CalendarSlots
    */ 
    public function testScheduleCommands()
    {
       // Test Add New Slot
       $iCalYear =  (int) $this->getContainer()
                                 ->getDatabaseAdapter()
                                 ->fetchColumn("select year(NOW()) 
                                                from bm_schedule_membership 
                                                ",[],0,[]);
      
        $oNow = $this->getContainer()->getNow();  
       
        $this->StartScheduleTest($this->aDatabaseId['member_one'], $this->aDatabaseId['five_minute'], $iCalYear);
       
        $this->StopScheduleTest($this->aDatabaseId['schedule_one'], $oNow);
    
        $this->ResumeScheduleTest($this->aDatabaseId['schedule_one'], $oNow);
    
        
        $this->ToggleScheduleCarry($this->aDatabaseId['schedule_one']);   
       
    }
    
    
    public function StartScheduleTest($iMemberDatabaseId, $iTimeSlotDatabbaseId, $iCalendarYear)
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new StartScheduleCommand($iMemberDatabaseId,$iTimeSlotDatabbaseId,$iCalendarYear);
       
        $oCommandBus->handle($oCommand);
        
        $this->assertNotEmpty($oCommand->getScheduleId());
        
        $this->aDatabaseId['schedule_one'] = $oCommand->getScheduleId();
        
        
    }
    
    public function StopScheduleTest($iScheduleId, $oNow)
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        
        if(true == empty($iScheduleId)) {
            $this->assertTrue(false,'Unable to find schedule database id to run stop test on');
        }
       
        $oCommand  = new StopScheduleCommand($iScheduleId,$this->getContainer()->getNow());
       
        $oCommandBus->handle($oCommand);
      
        
    }
    
    
    public function ResumeScheduleTest($iScheduleId, $oNow)
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand    = new ResumeScheduleCommand($iScheduleId);
        
        $oCommandBus->handle($oCommand);
     
        
    }
    
    public function ToggleScheduleCarry($iScheduleId)
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand    = new ToggleScheduleCarryCommand($iScheduleId);
        
        $oCommandBus->handle($oCommand);
      
        
    }

    
    
}
/* end of file */
