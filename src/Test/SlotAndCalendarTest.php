<?php
namespace IComeFromTheNet\BookMe\Test;

use DateTime;
use Valitron\Validator;
use Doctrine\DBAL\Types\Type;
use IComeFromTheNet\BookMe\Test\Base\TestSetupBase;
use IComeFromTheNet\BookMe\BookMeService;
use IComeFromTheNet\BookMe\Bus\Exception\SlotFailedException;
use IComeFromTheNet\BookMe\Bus\Middleware\ValidationException;
use IComeFromTheNet\BookMe\Bus\Command\SlotToggleStatusCommand;
use IComeFromTheNet\BookMe\Bus\Command\SlotAddCommand;
use IComeFromTheNet\BookMe\Bus\Command\CalAddYearCommand;
use IComeFromTheNet\BookMe\Bus\Command\RolloverTimeslotCommand;


class SlotAndCalendarTest extends TestSetupBase
{
    
    
   protected function handleEventPostFixtureRun()
   {
      return false;
   }  
   
    
    
   
    /**
    * @group Setup
    */ 
    public function testCalendar()
    {
       $oStartYear = \DateTime::createFromFormat('Y-m-d','2015-01-01');
       
       // Test of new Calendar
       $this->AddYearTest($oStartYear);
       
      
      // Test validation  
      try {
           $this->AddYearValidationFailsTooLargeTest();
           $this->assertFalse(true,'Exception validation on max cal years failed');
       } catch(ValidationException $e) {
           $this->assertTrue(true);
       }
       
       // Test validation
       try {
           $this->AddYearValidationFailsTooSmallTest();
           $this->assertFalse(true,'Exception validation on min cal years failed');
       } catch(ValidationException $e) {
           $this->assertTrue(true);
       }
       
       
       
       // Test Add New Slot
       
       $iSlotId = $this->AddNewSlotTest($oStartYear->format('Y'));
       
       
       // Test on dupliate failure
       try {
           $this->AddFailsOnDuplicateTest($oStartYear->format('Y'));
           $this->assertFalse(true,'Exception validation on duplicate failed');
       } catch(SlotFailedException $e) {
           $this->assertTrue(true);
       }
       
       // Test disabled toggle
       $this->ToggleSlotDisabledTest($iSlotId);
     
       
       // Test Enabled Toggle
       $this->ToggleSlotEnabledTest($iSlotId);
       
       // Test custom validators
       $this->SameCalYearValidatorTest();
       
      
    }
    
    protected function AddYearTest($oStartYear)
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new CalAddYearCommand(1, $oStartYear);
       
        $oCommandBus->handle($oCommand);
        
        // Assert max date is equal
        
        $aDates = $oContainer->getDatabaseAdapter()->fetchArray("select date_format(max(calendar_date),'%Y-%m-%d') as max from bm_calendar");
        $oMaxDateTime = \DateTime::createFromFormat('Y-m-d',$aDates[0]);
       
        $this->assertEquals($oStartYear->format('Y').'-12-31', $oMaxDateTime->format('Y-m-d'));
    }
    
    protected function AddYearValidationFailsTooLargeTest()
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new CalAddYearCommand(100);
       
        $oCommandBus->handle($oCommand);
       
        
    }
    
 
    protected function AddYearValidationFailsTooSmallTest()
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new CalAddYearCommand(0);
       
        $oCommandBus->handle($oCommand);
       
        
    }
   

    
    
    protected function AddNewSlotTest($iCalYear)
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new SlotAddCommand(12,$iCalYear);
       
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
        $iDaysInYear = date("z", mktime(0,0,0,12,31,$iCalYear)) + 1;
        
        $this->assertGreaterThanOrEqual($iDayCount *$iDaysInYear, $iYearCount,'The year slot count is less than expected' );
      
        
        
        return $oCommand->getTimeSlotId();
        
    }
    

    protected function AddFailsOnDuplicateTest($iCalYear)
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new SlotAddCommand(12,$iCalYear);
       
        $oCommandBus->handle($oCommand);
        
        $oCommand  = new SlotAddCommand(12,$iCalYear);
       
        $oCommandBus->handle($oCommand);
        
    }
    
    
    
    protected function ToggleSlotEnabledTest($iSlotId)
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
    

    protected function ToggleSlotDisabledTest($iSlotId)
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
    
    
  
    protected function SameCalYearValidatorTest()
    {
        $oContainer  = $this->getContainer();
        
        $aLogic = array('date_before' => new DateTime(),'date_after' => new DateTime());
        
        $v = new Validator($aLogic);
            $v->rule('calendarSameYear', 'date_before','date_after');
        if($v->validate()) {
            $this->assertTrue(true);
        } else {
            $this->assertFalse(true,'calendarSameYear has failed validation when should not have');
        }
        
         $aLogic = array('date_before' => DateTime::createFromFormat('Y-m-d','2013-01-01'),'date_after' => new DateTime());
        
        $v = new Validator($aLogic);
            $v->rule('calendarSameYear', 'date_before','date_after');
        if($v->validate()) {
            $this->assertTrue(false,'calendarSameYear has passed validation when should not have');
        } else {
            $this->assertTrue(true);
        }
        
    }

   
    
    
}
/* end of file */
