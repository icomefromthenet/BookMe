<?php
namespace IComeFromTheNet\BookMe\Test;

use DateTime;
use Doctrine\DBAL\Types\Type;
use IComeFromTheNet\BookMe\Test\Base\TestMgtBase;
use IComeFromTheNet\BookMe\Bus\Command\StartScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\StopScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\ResumeScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Command\ToggleScheduleCarryCommand;

use IComeFromTheNet\BookMe\BookMeService;
use IComeFromTheNet\BookMe\Bus\Exception\ScheduleException;



class ScheduleBasicTest extends TestMgtBase
{
    
    
    protected $aDatabaseId = [];
    
    
    
   protected function handleEventPostFixtureRun()
   {
      // Create the Calendar 
      $oService = new BookMeService($this->getContainer());
      
      $oService->addCalenderYears(5);
      
      $oNow     = $this->getContainer()->getNow();
      
      $iFiveMinuteTimeslot    = $oService->addTimeslot(5,$oNow->format('Y'));
      $iTenMinuteTimeslot     = $oService->addTimeslot(10,$oNow->format('Y'));
      $iFifteenMinuteTimeslot = $oService->addTimeslot(15,$oNow->format('Y'));

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
    * @group Management
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
       
        $this->StopScheduleTest($this->aDatabaseId['schedule_one'], $oNow, $iCalYear);
    
        $this->ResumeScheduleTest($this->aDatabaseId['schedule_one'], $oNow, $iCalYear);
    
        
        $this->ToggleScheduleCarry($this->aDatabaseId['schedule_one'], $oNow, $iCalYear);   
       
    }
    
    
    protected function StartScheduleTest($iMemberDatabaseId, $iTimeSlotDatabbaseId, $iCalendarYear)
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new StartScheduleCommand($iMemberDatabaseId,$iTimeSlotDatabbaseId,$iCalendarYear);
       
        $oCommandBus->handle($oCommand);
        
        # Assert the schedule was created
        
        $this->assertNotEmpty($oCommand->getScheduleId());
        $this->aDatabaseId['schedule_one'] = $oCommand->getScheduleId();
        
        # Verify have the expected slot boundries
        $oDateType = Type::getType(Type::DATETIME);
        $oDatabase = $this->getContainer()->getDatabaseAdapter();
        
        $oOpeningFirstSlot = $oDateType->convertToPHPValue($oDatabase->fetchColumn("SELECT min(slot_open) FROM bm_schedule_slot where schedule_id = ?",[$this->aDatabaseId['schedule_one']],0), $oDatabase->getDatabasePlatform());
        $oClosingLastSlot = $oDateType->convertToPHPValue($oDatabase->fetchColumn("SELECT max(slot_close) FROM bm_schedule_slot where schedule_id = ?",[$this->aDatabaseId['schedule_one']],0), $oDatabase->getDatabasePlatform());
       
        $this->assertEquals('01-01-'.$iCalendarYear,$oOpeningFirstSlot->format('d-m-Y'),'Opening slot has wrong date');
        $this->assertEquals('01-01-'.($iCalendarYear+1),$oClosingLastSlot->format('d-m-Y'),'Closing slot has wrong date');
        
        $this->assertEquals('00:00',$oOpeningFirstSlot->format('H:i'),'Opening minute has wrong date');
        $this->assertEquals('00:00',$oClosingLastSlot->format('H:i'),'Closing minute has wrong date');
       
        
    }
    
    protected function StopScheduleTest($iScheduleId, $oNow, $iCalYear)
    {
        $oContainer  = $this->getContainer();
        $oDatabase   = $this->getContainer()->getDatabaseAdapter();
        $oCommandBus = $oContainer->getCommandBus(); 
    
        $oStartDate = new DateTime();
        $oStartDate->setDate($oNow->format('Y'),6,1);
        
        $oCommand  = new StopScheduleCommand($iScheduleId,$oStartDate);
       
        $oCommandBus->handle($oCommand);
      
        
        $sFristClosedSlot = $oDatabase->fetchColumn("SELECT min(slot_open) 
                                                     FROM bm_schedule_slot 
                                                     WHERE schedule_id = ?
                                                     and is_closed = true",[$iScheduleId],0);
        
        
        $sLastClosedSlot = $oDatabase->fetchColumn("SELECT max(slot_close) 
                                                     FROM bm_schedule_slot 
                                                     WHERE schedule_id = ?
                                                     and is_closed = true",[$iScheduleId],0);
        
        if($sFristClosedSlot == null || $sLastClosedSlot == null) {
            $this->assertFalse(true,'Unable to find max and min closed schedule slots');
        }
        
        $oDateType = Type::getType(Type::DATETIME);
        
        $oFristClosedSlot = $oDateType->convertToPHPValue($sFristClosedSlot, $oDatabase->getDatabasePlatform());
        $oLastClosedSlot = $oDateType->convertToPHPValue($sLastClosedSlot, $oDatabase->getDatabasePlatform());
     
        
        $this->assertEquals('01-06-'.$iCalYear,$oFristClosedSlot->format('d-m-Y'),'Closed Schedule opening slot has wrong date');
        $this->assertEquals('01-01-'.($iCalYear+1),$oLastClosedSlot->format('d-m-Y'),'closed Schedule closing slot has wrong date');
        
        $this->assertEquals('00:00',$oFristClosedSlot->format('H:i'),'Closed Schedule opening minute has wrong date');
        $this->assertEquals('00:00',$oLastClosedSlot->format('H:i'),'Closed Schedule closing minute has wrong date');
        
    }
    
    
    protected function ResumeScheduleTest($iScheduleId, $oNow, $iCalYear)
    {
        $oContainer  = $this->getContainer();
        $oDatabase   = $this->getContainer()->getDatabaseAdapter();
        $oCommandBus = $oContainer->getCommandBus(); 
        
        $oResumeDate = new DateTime();
        $oResumeDate->setDate($oNow->format('Y'),7,1);
       
        $oCommand    = new ResumeScheduleCommand($iScheduleId,$oResumeDate);
        
        $oCommandBus->handle($oCommand);
     
        $sFristClosedSlot = $oDatabase->fetchColumn("SELECT min(slot_open) 
                                                     FROM bm_schedule_slot 
                                                     WHERE schedule_id = ?
                                                     and is_closed = true",[$iScheduleId],0);
        
        
        $sLastClosedSlot = $oDatabase->fetchColumn("SELECT max(slot_close) 
                                                     FROM bm_schedule_slot 
                                                     WHERE schedule_id = ?
                                                     and is_closed = true",[$iScheduleId],0);
        
        if($sFristClosedSlot == null || $sLastClosedSlot == null) {
            $this->assertFalse(true,'Unable to find max and min closed schedule slots');
        }
        
        $oDateType = Type::getType(Type::DATETIME);
        
        $oFristClosedSlot = $oDateType->convertToPHPValue($sFristClosedSlot, $oDatabase->getDatabasePlatform());
        $oLastClosedSlot = $oDateType->convertToPHPValue($sLastClosedSlot, $oDatabase->getDatabasePlatform());
     
        
        
        $this->assertEquals('01-06-'.$iCalYear,$oFristClosedSlot->format('d-m-Y'),'Closed Schedule opening slot has wrong date');
        $this->assertEquals('01-07-'.$iCalYear,$oLastClosedSlot->format('d-m-Y'),'closed Schedule closing slot has wrong date');
        
        $this->assertEquals('00:00',$oFristClosedSlot->format('H:i'),'Closed Schedule opening minute has wrong date');
        $this->assertEquals('00:00',$oLastClosedSlot->format('H:i'),'Closed Schedule closing minute has wrong date');
     
     
        
        
    }
    
    protected function ToggleScheduleCarry($iScheduleId,$oNow, $iCalYear)
    {
        $oContainer  = $this->getContainer();
        $oDatabase   = $this->getContainer()->getDatabaseAdapter();
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand    = new ToggleScheduleCarryCommand($iScheduleId);
        
        $oCommandBus->handle($oCommand);
      
        # First toggle should disable
      
        $oBoolType = Type::getType(Type::BOOLEAN);
        $sIsClosed = $oDatabase->fetchColumn("SELECT is_carryover FROM bm_schedule WHERE schedule_id = ?",[$iScheduleId],0);
        $bIsClosed = $oBoolType->convertToPHPValue($sIsClosed, $oDatabase->getDatabasePlatform());
        
      
        $this->assertFalse($bIsClosed);
        
        
        # Try and reverse the toggle
        
        $oCommandBus->handle($oCommand);
      
        $oBoolType = Type::getType(Type::BOOLEAN);
        $sIsClosed = $oDatabase->fetchColumn("SELECT is_carryover FROM bm_schedule WHERE schedule_id = ?",[$iScheduleId],0);
        $bIsClosed = $oBoolType->convertToPHPValue($sIsClosed, $oDatabase->getDatabasePlatform());
        
      
        $this->assertTrue($bIsClosed);
    }

    
    
}
/* end of file */
