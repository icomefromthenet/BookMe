<?php
namespace IComeFromTheNet\BookMe\Test;

use DateTime;
use IComeFromTheNet\BookMe\Test\Base\TestCalendarSlotsGroupBase;
use Valitron\Validator;


class UtilityTest extends TestCalendarSlotsGroupBase
{
    
    
   protected function handleEventPostFixtureRun()
   {
      // calender if first object to install so nothing need to do yet
   }   
    
    /**
     * @group CalendarSlots
     */ 
    public function testSameCalYearValidator()
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
