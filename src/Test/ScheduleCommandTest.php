<?php
namespace IComeFromTheNet\BookMe\Test;

use Doctrine\DBAL\Types\Type;
use IComeFromTheNet\BookMe\Test\Base\TestCalendarSlotsGroupBase;
use IComeFromTheNet\BookMe\Bus\Command\StartScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\StopScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\ResumeScheduleCommand;
use IComeFromTheNet\BookMe\BookMeService;
use IComeFromTheNet\BookMe\Bus\Exception\ScheduleException;



class ScheduleCommandTest extends TestCalendarSlotsGroupBase
{
    
    
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
    
      $iTeamOne     = $this->registerTeam($iFiveMinuteTimeslot);
      $iTeamTwo     = $this->registerTeam($iFifteenMinuteTimeslot);
    
   }  
   
   
    /**
    * @group CalendarSlots
    */ 
    public function testScheduleCommands()
    {
       // Test Add New Slot
       
       //$iSlotId = $this->AddNewSlotTest();
       
        /*   
       // Test on dupliate failure
       try {
           $this->AddFailsOnDuplicateTest();
           $this->assertFalse(true,'Exception on duplicate not raised');
       } catch(SlotFailedException $e) {
           $this->assertTrue(true);
       }
       
       // Test disabled toggle
       $this->ToggleDisabledTest($iSlotId);
     
       
       // Test Enabled Toggle
       $this->ToggleEnabledTest($iSlotId);
       
       */
       
    }
    
    
    public function AddNewSlotTest()
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new SlotAddCommand(12);
       
        $oCommandBus->handle($oCommand);
        
        $this->assertNotEmpty($oCommand->getTimeSlotId());
        
        $numberSlots = (int)((60*24) / 12);
        
        // Assert max date is equal
        
        $iDayCount = (int) $oContainer->getDatabaseAdapter()->fetchColumn("select count(open_minute) 
                                                                           from bm_timeslot_day 
                                                                           where timeslot_id = ? "
                                                                           ,[$oCommand->getTimeSlotId()],0,[]);
       
       
        $this->assertEquals($numberSlots,$iDayCount,'The Day slot are less than expected number'); 
        
        $iYearCount = (int) $oContainer->getDatabaseAdapter()->fetchColumn("select count(open_minute) 
                                                                            from bm_timeslot_year 
                                                                            where timeslot_id = ? "
                                                                            ,[$oCommand->getTimeSlotId()],0,[]);
        
        
        $this->assertGreaterThanOrEqual($iDayCount *365, $iYearCount,'The year slot count is less than expected' );
      
        
        
        return $oCommand->getTimeSlotId();
        
    }
    

    
    
}
/* end of file */
