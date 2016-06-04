<?php
namespace IComeFromTheNet\BookMe\Test;

use DateTime;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Valitron\Validator;

use IComeFromTheNet\BookMe\BookMeService;
use IComeFromTheNet\BookMe\Test\Base\TestBookingBase;
use IComeFromTheNet\BookMe\Bus\Middleware\ValidationException;
use IComeFromTheNet\BookMe\Bus\Command\TakeBookingCommand;
use IComeFromTheNet\BookMe\Bus\Exception\BookingException;
use IComeFromTheNet\BookMe\Bus\Command\LookBookingConflictsCommand;
use IComeFromTheNet\BookMe\Bus\Command\ClearBookingCommand;


class BookingTest extends TestBookingBase
{
    
    
   protected function handleEventPostFixtureRun()
   {
      // Create the Calendar 
      $oService = new BookMeService($this->getContainer());
      
      $oNow   = $this->getContainer()->getNow();
      $oStartYear = clone $oNow;
      $oStartYear->setDate($oNow->format('Y'),1,1);
      
      $oService->addCalenderYears(1,$oStartYear);
      
      // Create Timeslots
      
      $iFiveMinuteTimeslot    = $oService->addTimeslot(5,$oNow->format('Y'));
      $iTenMinuteTimeslot     = $oService->addTimeslot(10,$oNow->format('Y'));
      $iSevenMinuteTimeslot    = $oService->addTimeslot(7,$oNow->format('Y'));

      $oService->toggleSlotAvability($iTenMinuteTimeslot);    
  
      // Register new Members
  
      $iMemberOne   = $oService->registerMembership();
      $iMemberTwo   = $oService->registerMembership();
      $iMemberThree = $oService->registerMembership();
      $iMemberFour  = $oService->registerMembership();
    
      // Register new Teams    
    
      $iTeamOne     = $oService->registerTeam($iFiveMinuteTimeslot);
      $iTeamTwo     = $oService->registerTeam($iSevenMinuteTimeslot);
      
      
       // Schedules
      
      $iMemberOneSchedule   = $oService->startSchedule($iMemberOne,   $iFiveMinuteTimeslot, $oNow->format('Y'));
      $iMemberTwoSchedule   = $oService->startSchedule($iMemberTwo,   $iFiveMinuteTimeslot, $oNow->format('Y'));
      $iMemberThreeSchedule = $oService->startSchedule($iMemberThree, $iFiveMinuteTimeslot, $oNow->format('Y'));
      $iMemberFourSchedule  = $oService->startSchedule($iMemberFour,  $iFiveMinuteTimeslot, $oNow->format('Y'));
      
      // Stop a schedule
      
      $oService->stopSchedule($iMemberFourSchedule,$oNow->setDate($oNow->format('Y'),6,1));
      
      // Assign members to team one as their using $iFiveMinuteTimeslot
      
      $oService->assignTeamMember($iMemberOne,$iTeamOne,$iMemberOneSchedule);
      $oService->assignTeamMember($iMemberTwo,$iTeamOne,$iMemberTwoSchedule);
     
      $oService->assignTeamMember($iMemberThree,$iTeamOne,$iMemberThreeSchedule);
      $oService->assignTeamMember($iMemberFour,$iTeamOne,$iMemberFourSchedule);
      
      // Create some Rules 
      
      $oSingleDate = clone $oNow;
      $oSingleDate->setDate($oNow->format('Y'),1,14);
        
      $oDayWorkDayRuleStart = clone $oNow;
      $oDayWorkDayRuleStart->setDate($oNow->format('Y'),1,1);
      
      $oDayWorkDayRuleEnd = clone $oNow;
      $oDayWorkDayRuleEnd->setDate($oNow->format('Y'),12,31);
      
      $oHolidayStart = clone $oNow;
      $oHolidayStart->setDate($oNow->format('Y'),8,7);
      
      $oHolidayEnd   = clone $oNow; 
      $oHolidayEnd->setDate($oNow->format('Y'),8,14);
      
      
      $iNineAmSlot = (12*9) *5;
      $iFivePmSlot = (12*17)*5;
      $iTenPmSlot  = (12*20)*5;    
        
      $iRepeatWorkDayRule    = $oService->createRepeatingWorkDayRule($oDayWorkDayRuleStart,$oDayWorkDayRuleEnd,$iFiveMinuteTimeslot,$iNineAmSlot,$iFivePmSlot,'1-5','*','2-12');
      $iSingleWorkDayRule    = $oService->createSingleWorkDayRule($oSingleDate,$iFiveMinuteTimeslot,$iFivePmSlot,$iTenPmSlot); 
      
      $iMidaySlot = (12*12)*5;
      $iOnePmSlot = (12*13)*5;
      
      $iEightPmSlot  = (12*18)*5;
      $iEightThirtyPmSlot = ((12*18) + 6)*5;
      
      $iRepeatBreakRule      = $oService->createRepeatingBreakRule($oDayWorkDayRuleStart,$oDayWorkDayRuleEnd,$iFiveMinuteTimeslot,$iMidaySlot,$iOnePmSlot,'1-5','*','2-12');
      $iSingleBreakRule      = $oService->createSingleBreakRule($oSingleDate,$iFiveMinuteTimeslot,$iEightPmSlot,$iEightThirtyPmSlot); 
            
            
      $iRepeatHolidayRule    = $oService->createRepeatingHolidayRule($oDayWorkDayRuleStart,$oDayWorkDayRuleEnd,$iFiveMinuteTimeslot,$iNineAmSlot,$iFivePmSlot,'*','28-30','*');    
      $iSingleHolidayRule      = $oService->createSingleHolidayRule($oHolidayStart,$iFiveMinuteTimeslot,$iNineAmSlot,$iFivePmSlot);             
    
    
      $iRepeatOvertimeRule   = $oService->createRepeatingOvertimeRule($oDayWorkDayRuleStart,$oDayWorkDayRuleEnd,$iFiveMinuteTimeslot,$iNineAmSlot,$iFivePmSlot,'*','28-30','*');
      $iSingleOvertimeRule   = $oService->createSingleOvertmeRule($oHolidayStart,$iFiveMinuteTimeslot,$iNineAmSlot,$iFivePmSlot);
      
      
      // Link Rules to Schedule
      $oService->assignRuleToSchedule($iRepeatWorkDayRule,$iMemberOneSchedule,true);
      $oService->assignRuleToSchedule($iSingleWorkDayRule,$iMemberOneSchedule,false);
      $oService->assignRuleToSchedule($iRepeatBreakRule,$iMemberOneSchedule,true);
      $oService->assignRuleToSchedule($iSingleBreakRule,$iMemberOneSchedule,false);
      $oService->assignRuleToSchedule($iRepeatHolidayRule,$iMemberOneSchedule,true);
      $oService->assignRuleToSchedule($iSingleHolidayRule,$iMemberOneSchedule,false);
      $oService->assignRuleToSchedule($iRepeatOvertimeRule,$iMemberOneSchedule,true);
      $oService->assignRuleToSchedule($iSingleOvertimeRule,$iMemberOneSchedule,false);
    
      $oService->assignRuleToSchedule($iRepeatWorkDayRule,$iMemberTwoSchedule,true);
      $oService->assignRuleToSchedule($iSingleWorkDayRule,$iMemberTwoSchedule,false);
      $oService->assignRuleToSchedule($iRepeatBreakRule,$iMemberTwoSchedule,true);
      $oService->assignRuleToSchedule($iSingleBreakRule,$iMemberTwoSchedule,false);
      $oService->assignRuleToSchedule($iRepeatHolidayRule,$iMemberTwoSchedule,true);
      $oService->assignRuleToSchedule($iSingleHolidayRule,$iMemberTwoSchedule,false);
      $oService->assignRuleToSchedule($iRepeatOvertimeRule,$iMemberTwoSchedule,true);
      $oService->assignRuleToSchedule($iSingleOvertimeRule,$iMemberTwoSchedule,false);
      
      $oService->assignRuleToSchedule($iRepeatWorkDayRule,$iMemberThreeSchedule,true);
      $oService->assignRuleToSchedule($iSingleWorkDayRule,$iMemberThreeSchedule,false);
      $oService->assignRuleToSchedule($iRepeatBreakRule,$iMemberThreeSchedule,true);
      $oService->assignRuleToSchedule($iSingleBreakRule,$iMemberThreeSchedule,false);
      $oService->assignRuleToSchedule($iRepeatHolidayRule,$iMemberThreeSchedule,true);
      $oService->assignRuleToSchedule($iSingleHolidayRule,$iMemberThreeSchedule,false);
      $oService->assignRuleToSchedule($iRepeatOvertimeRule,$iMemberThreeSchedule,true);
      $oService->assignRuleToSchedule($iSingleOvertimeRule,$iMemberThreeSchedule,false);
      
      $oService->assignRuleToSchedule($iRepeatWorkDayRule,$iMemberFourSchedule,true);
      $oService->assignRuleToSchedule($iSingleWorkDayRule,$iMemberFourSchedule,false);
      $oService->assignRuleToSchedule($iRepeatBreakRule,$iMemberFourSchedule,true);
      $oService->assignRuleToSchedule($iSingleBreakRule,$iMemberFourSchedule,false);
      $oService->assignRuleToSchedule($iRepeatHolidayRule,$iMemberFourSchedule,true);
      $oService->assignRuleToSchedule($iSingleHolidayRule,$iMemberFourSchedule,false);
      $oService->assignRuleToSchedule($iRepeatOvertimeRule,$iMemberFourSchedule,true);
      $oService->assignRuleToSchedule($iSingleOvertimeRule,$iMemberFourSchedule,false);
      
      //  Refresh the Members Schedules
      
      $oService->resfreshSchedule($iMemberOneSchedule);
      $oService->resfreshSchedule($iMemberTwoSchedule);
      $oService->resfreshSchedule($iMemberThreeSchedule);
      $oService->resfreshSchedule($iMemberFourSchedule);
    
    
    
      // save identifiers for use below    
            
      $this->aDatabaseId = [
        'five_minute'            => $iFiveMinuteTimeslot,
        'ten_minute'             => $iTenMinuteTimeslot,
        'fifteen_minute'         => $iFifteenMinuteTimeslot,
        'member_one'             => $iMemberOne,
        'member_two'             => $iMemberTwo,
        'member_three'           => $iMemberThree,
        'member_four'            => $iMemberFour,
        'team_two'               => $iTeamTwo,
        'team_one'               => $iTeamOne,
        'work_repeat'            => $iRepeatWorkDayRule,
        'work_single'            => $iSingleWorkDayRule,
        'break_repeat'           => $iRepeatBreakRule,
        'break_single'           => $iSingleBreakRule,
        'holiday_repeat'         => $iRepeatHolidayRule,
        'holiday_single'         => $iSingleHolidayRule,
        'overtime_repeat'        => $iRepeatOvertimeRule,
        'overtime_single'        => $iSingleOvertimeRule,
        'schedule_member_one'    => $iMemberOneSchedule,
        'schedule_member_two'    => $iMemberTwoSchedule,
        'schedule_member_three'  => $iMemberThreeSchedule,
        'schedule_member_four'   => $iMemberFourSchedule,
        
      ];
      
    
      
      
   }  
   
   /**
    * @group Booking
    */ 
   public function testBookingSteps()
   {
      $oNow       = $this->getContainer()->getNow();
      
      // Test a sucessful booking (No oveertime slots)
      
      $oOpen  =  clone $oNow;
      $oOpen->setDate($oNow->format('Y'),1,14);
      $oOpen->setTime(17,0,0);
      
      $oClose = clone $oNow;
      $oClose->setDate($oNow->format('Y'),1,14);
      $oClose->setTime(17,20,0);
      
      $this->SucessfulyTakeBooking($this->aDatabaseId['schedule_member_one'],$oOpen,$oClose,4);
      
      // Check for duplicate failure
      
      $this->FailOnDuplicateBooking($this->aDatabaseId['schedule_member_one'],$oOpen,$oClose);
      
      // Take a second booking so we can test if max check works
      $oOpen  =  clone $oNow;
      $oOpen->setDate($oNow->format('Y'),1,14);
      $oOpen->setTime(17,20,0);
      
      $oClose = clone $oNow;
      $oClose->setDate($oNow->format('Y'),1,14);
      $oClose->setTime(17,40,0);
      
      
      $this->SucessfulyTakeBooking($this->aDatabaseId['schedule_member_one'],$oOpen,$oClose,4);
      $this->FailMaxBooking($this->aDatabaseId['schedule_member_one'],$oOpen,$oClose);
      
      $oOpen  =  clone $oNow;
      $oOpen->setDate($oNow->format('Y'),1,28);
      $oOpen->setTime(9,0,0);
      
      $oClose = clone $oNow;
      $oClose->setDate($oNow->format('Y'),1,28);
      $oClose->setTime(9,45,0);
      
      $this->SucessfulyTakeOvertimeBooking($this->aDatabaseId['schedule_member_one'],$oOpen,$oClose,9);
      
      $oOpen  =  clone $oNow;
      $oOpen->setDate($oNow->format('Y'),1,14);
      $oOpen->setTime(18,0,0);
      
      $oClose = clone $oNow;
      $oClose->setDate($oNow->format('Y'),1,14);
      $oClose->setTime(18,45,0);
      
      $this->FailBreakBooking($this->aDatabaseId['schedule_member_one'],$oOpen,$oClose);
      
      // Test Conflict Checker
      
      $this->ConfictCheckerTest($this->aDatabaseId['schedule_member_one'],$oNow);
      
      // Clear a booking
      $this->BookingClearTest(1);
   }
   
   
   
   public function SucessfulyTakeBooking($iScheduleId, DateTime $oOpeningSlot, DateTime $oClosingSlot, $iExpectedSlotCount)
   {
        
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new TakeBookingCommand($iScheduleId, $oOpeningSlot, $oClosingSlot);
        
        $oCommandBus->handle($oCommand);
        
        // check if we have a booking saved
        $this->assertGreaterThanOrEqual(1,$oCommand->getBookingId());
        
        // verify the slots were reserved
        $iSlotCount = 0;
        
        $iSlotCount = (integer) $oContainer->getDatabase()->fetchColumn('SELECT count(*) 
                                                FROM bm_schedule_slot
                                                WHERE schedule_id = ? 
                                                and slot_open >= ?
                                                and slot_close <= ?
                                                and booking_id = ?'
                                                ,[$iScheduleId,$oOpeningSlot,$oClosingSlot,$oCommand->getBookingId()]
                                                ,0
                                                ,[Type::INTEGER, Type::DATETIME,Type::DATETIME,Type::INTEGER]);
        
        $this->assertEquals($iSlotCount,$iExpectedSlotCount,'The slots have not been reserved');
        
        
   }
   
   
   
   public function FailMaxBooking($iScheduleId, DateTime $oOpeningSlot, DateTime $oClosingSlot)
   {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new TakeBookingCommand($iScheduleId, $oOpeningSlot, $oClosingSlot,1);
       
        try {
        
            $oCommandBus->handle($oCommand);
            $this->assertFalse(true,'The Max Booking check should of failed');
            
        }
        catch(BookingException $e) {
           $this->assertEquals($e->getMessage(),'Max bookings taken for calendar day for schedule at id 1 time from '.$oOpeningSlot->format('Y-m-d H:i:s').' until '.$oClosingSlot->format('Y-m-d H:i:s'));
        }
    
   }
   
   public function FailOnDuplicateBooking($iScheduleId, DateTime $oOpeningSlot, DateTime $oClosingSlot)
   {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new TakeBookingCommand($iScheduleId, $oOpeningSlot, $oClosingSlot);
        
        try {
        
            $oCommandBus->handle($oCommand);
            $this->assertFalse(true,'A Duplicate Booking was allowed');
            
        }
        catch(BookingException $e) {
           
           $this->assertEquals($e->getMessage(),'Unable to reserve schedule slots for schedule at id 1 time from '.$oOpeningSlot->format('Y-m-d H:i:s').' until '.$oClosingSlot->format('Y-m-d H:i:s'));
           
        }
    
   }
  
  
   public function SucessfulyTakeOvertimeBooking($iScheduleId, DateTime $oOpeningSlot, DateTime $oClosingSlot, $iExpectedSlotCount)
   {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new TakeBookingCommand($iScheduleId, $oOpeningSlot, $oClosingSlot);
        
        $oCommandBus->handle($oCommand);
        
        // check if we have a booking saved
        $this->assertGreaterThanOrEqual(1,$oCommand->getBookingId());
        
        // verify the slots were reserved
        $iSlotCount = 0;
        
        $iSlotCount = (integer) $oContainer->getDatabase()->fetchColumn('SELECT count(*) 
                                                FROM bm_schedule_slot
                                                WHERE schedule_id = ? 
                                                and slot_open >= ?
                                                and slot_close <= ?
                                                and booking_id = ?'
                                                ,[$iScheduleId,$oOpeningSlot,$oClosingSlot,$oCommand->getBookingId()]
                                                ,0
                                                ,[Type::INTEGER, Type::DATETIME,Type::DATETIME,Type::INTEGER]);
        
        $this->assertEquals($iSlotCount,$iExpectedSlotCount,'The slots have not been reserved');
    
   }
  
  
   public function FailBreakBooking($iScheduleId, DateTime $oOpeningSlot, DateTime $oClosingSlot)
   {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        $oCommand  = new TakeBookingCommand($iScheduleId, $oOpeningSlot, $oClosingSlot);
        
        try {
        
            $oCommandBus->handle($oCommand);
            $this->assertFalse(true,'A booking on break was allowed');
            
        }
        catch(BookingException $e) {
           
           $this->assertEquals($e->getMessage(),'Unable to reserve schedule slots for schedule at id 1 time from '.$oOpeningSlot->format('Y-m-d H:i:s').' until '.$oClosingSlot->format('Y-m-d H:i:s'));
           
        }
    
   }
   
   
 
   public function ConfictCheckerTest($iScheduleId,$oNow)
   {
       $oContainer  = $this->getContainer();
        
       $oCommandBus = $oContainer->getCommandBus(); 
       
       
       // Conflict 1 Booking Exclusion Rule now exists or override removed
       $sSql  ="";
       $sSql .=" UPDATE bm_schedule_slot SET is_override = false, is_available = true, is_excluded = true, booking_id = 1, is_closed = false " ;
       $sSql .=" WHERE schedule_id = ?  AND slot_open >= '2016-08-01 12:00:00' AND slot_close <= '2016-08-01 12:45:00'";
       
       $oContainer->getDatabase()->executeUpdate($sSql,[$iScheduleId],[Type::INTEGER]);
       
       
       // Conflict 2 Booking Schedule has been closed
       $sSql  ="";
       $sSql .=" UPDATE bm_schedule_slot SET is_override = false, is_available = true, is_excluded = false, booking_id = 1, is_closed = true " ;
       $sSql .=" WHERE schedule_id = ?  AND slot_open >= '2016-08-01 15:00:00' AND slot_close <= '2016-08-01 15:45:00'";
       
       $oContainer->getDatabase()->executeUpdate($sSql,[$iScheduleId],[Type::INTEGER]);
       
       $oStartYear = new DateTime();
       $oStartYear->setDate($oNow->format('Y'),1,1);
       $oStartYear->setTime(0,0,0);
       
       $oCommand = new LookBookingConflictsCommand($oStartYear);
       
       $oCommandBus->handle($oCommand);
       
       $this->assertEquals(1,$oCommand->getNumberConflictsFound());
       
   }
  
   
   public function BookingClearTest($iBookingId)
   {
       $oContainer  = $this->getContainer();
        
       $oCommandBus = $oContainer->getCommandBus(); 
      
       $oCommand = new ClearBookingCommand($iBookingId);
       
       $oCommandBus->handle($oCommand);
       
        $iBookCount = (integer) $oContainer->getDatabase()->fetchColumn('SELECT 1 
                                                FROM bm_booking
                                                WHERE booking_id = ?'
                                                ,[$iBookCount]
                                                ,0
                                                ,[Type::INTEGER]);
        
        $this->assertEquals(0,$iBookCount,'The booking was not removed');
        
         $iBookCount = (integer) $oContainer->getDatabase()->fetchColumn('SELECT 1 
                                                FROM bm_booking_conflict
                                                WHERE booking_id = ?'
                                                ,[$iBookCount]
                                                ,0
                                                ,[Type::INTEGER]);
        
        $this->assertEquals(0,$iBookCount,'The booking conflict was not removed');
       
   }
   
}
/* end of file */
