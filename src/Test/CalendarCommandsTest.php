<?php
namespace IComeFromTheNet\BookMe\Test;

use IComeFromTheNet\BookMe\Test\Base\TestCalendarSlotsGroupBase;
use IComeFromTheNet\BookMe\Bus\Command\CalAddYearCommand;


class CalendarCommandsTest extends TestCalendarSlotsGroupBase
{
    
    
   protected function handleEventPostFixtureRun()
   {
      // calender if first object to install so nothing need to do yet
   }   
    
    /**
     * @group CalendarSlots
     */ 
    public function testAddYear()
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new CalAddYearCommand(10);
       
        $oCommandBus->handle($oCommand);
        
        // Assert max date is equal
        
        $aDates = $oContainer->getDatabaseAdapter()->fetchArray("select date_format(max(calendar_date),'%Y-%m-%d') as max, 
                                                             date_format(now(),'%Y-%m-%d') as now
                                                       from bm_calendar");
        $oMaxDateTime = \DateTime::createFromFormat('Y-m-d',$aDates[0]);
        $oNowDateTime = \DateTime::createFromFormat('Y-m-d',$aDates[1]);
       
       // adding 10 year but year 1 is current calender year really only
       // adding 9 years to the current date
        $sNowAdd10Years = $oNowDateTime->modify('+ 9 years')->format('Y-12-31');
        $sMaxDate       = $oMaxDateTime->format('Y-m-d');
       
        $this->assertEquals($sNowAdd10Years,$sMaxDate);
    }
    
    
    /**
     * @group CalendarSlots
     * @expectedException IComeFromTheNet\BookMe\Bus\Middleware\ValidationException
     * @expectedExceptionMessage Validation has failed for commandIComeFromTheNet\BookMe\Bus\Command\CalAddYearCommand
     */ 
    public function testAddYearValidationFailsTooLarge()
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new CalAddYearCommand(100);
       
        $oCommandBus->handle($oCommand);
       
        
    }
    
    /**
     * @group CalendarSlots
     * @expectedException IComeFromTheNet\BookMe\Bus\Middleware\ValidationException
     * @expectedExceptionMessage Validation has failed for commandIComeFromTheNet\BookMe\Bus\Command\CalAddYearCommand
     */ 
    public function testAddYearValidationFailsTooSmall()
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new CalAddYearCommand(0);
       
        $oCommandBus->handle($oCommand);
       
        
    }
    
    
    
    
}
/* end of file */
