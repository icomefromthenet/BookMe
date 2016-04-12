<?php
namespace IComeFromTheNet\BookMe\Test;

use Doctrine\DBAL\Types\Type;
use IComeFromTheNet\BookMe\Test\Base\TestCalendarSlotsGroupBase;
use IComeFromTheNet\BookMe\Bus\Command\SlotToggleStatusCommand;
use IComeFromTheNet\BookMe\Bus\Command\SlotAddCommand;
use IComeFromTheNet\BookMe\BookMeService;
use IComeFromTheNet\BookMe\Bus\Exception\SlotFailedException;



class SlotCommandsTest extends TestCalendarSlotsGroupBase
{
    
    
   protected function handleEventPostFixtureRun()
   {
      // Create the Calendar 
      $oService = new BookMeService($this->getContainer());
      
      $oService->addCalenderYears(5);
      
      
   }  
   
   
    /**
    * @group CalendarSlots
    */ 
    public function testSlotsCommands()
    {
       // Test Add New Slot
       
       $iSlotId = $this->AddNewSlotTest();
       
       
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
        
        $iCount = (int) $oContainer->getDatabaseAdapter()->fetchColumn("select count(open_minute) from bm_timeslot_day where timeslot_id = ? ",[$oCommand->getTimeSlotId()],0,[]);
       
       
        $this->assertEquals($numberSlots,$iCount); 
        
        return $oCommand->getTimeSlotId();
        
    }
    

    public function AddFailsOnDuplicateTest()
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new SlotAddCommand(12);
       
        $oCommandBus->handle($oCommand);
        
        $oCommand  = new SlotAddCommand(12);
       
        $oCommandBus->handle($oCommand);
        
    }
    
    
    
    public function ToggleEnabledTest($iSlotId)
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand = new SlotToggleStatusCommand($iSlotId);
       
        $oCommandBus->handle($oCommand);
       
        $oBooleanType = Type::getType(TYPE::BOOLEAN);
        $oIntergerType = Type::getType(TYPE::INTEGER);
    
        $mResult =  $oContainer->getDatabaseAdapter()->fetchColumn('SELECT is_active_slot FROM bm_timeslot where timeslot_id = ?',[$iSlotId],0,[$oIntergerType]);
        $mResult = $oBooleanType->convertToPHPValue($mResult,$oContainer->getDatabaseAdapter()->getDatabasePlatform());
       
        $this->assertTrue($mResult);  
        
        return $iSlotId;
    }
    

    public function ToggleDisabledTest($iSlotId)
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand = new SlotToggleStatusCommand($iSlotId);
       
       
        $oCommandBus->handle($oCommand);
       
        $oBooleanType = Type::getType(TYPE::BOOLEAN);
        $oIntergerType = Type::getType(TYPE::INTEGER);
    
        $mResult =  $oContainer->getDatabaseAdapter()->fetchColumn('SELECT is_active_slot FROM bm_timeslot where timeslot_id = ?',[$iSlotId],0,[$oIntergerType]);
        $mResult = $oBooleanType->convertToPHPValue($mResult,$oContainer->getDatabaseAdapter()->getDatabasePlatform());
       
        $this->assertFalse($mResult);  
     
        
    }

    
    
}
/* end of file */
