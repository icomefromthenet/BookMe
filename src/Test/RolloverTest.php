<?php
namespace IComeFromTheNet\BookMe\Test;

use DateTime;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Valitron\Validator;

use IComeFromTheNet\BookMe\BookMeService;
use IComeFromTheNet\BookMe\Test\Base\TestRolloverBase;
use IComeFromTheNet\BookMe\Bus\Middleware\ValidationException;

use IComeFromTheNet\BookMe\Bus\Command\RolloverSchedulesCommand;
use IComeFromTheNet\BookMe\Bus\Command\RolloverRulesCommand;
use IComeFromTheNet\BookMe\Bus\Command\RolloverTeamsCommand;
use IComeFromTheNet\BookMe\Bus\Command\RolloverTimeslotCommand;
use IComeFromTheNet\BookMe\Bus\Command\CalAddYearCommand;


class RolloverTestTest extends TestRolloverBase
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
      
      // Assign members to teams
      $oService->assignTeamMember($iMemberOne,$iTeamOne,$iMemberOneSchedule);
      $oService->assignTeamMember($iMemberTwo,$iTeamOne,$iMemberTwoSchedule);
     
      $oService->assignTeamMember($iMemberThree,$iTeamTwo,$iMemberThreeSchedule);
      $oService->assignTeamMember($iMemberFour,$iTeamTwo,$iMemberFourSchedule);
      
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
      
      $oService->assignRuleToSchedule($iRepeatBreakRule,$iMemberOneSchedule,true);
      $oService->assignRuleToSchedule($iSingleBreakRule,$iMemberOneSchedule,false);
      $oService->assignRuleToSchedule($iRepeatHolidayRule,$iMemberOneSchedule,true);
      $oService->assignRuleToSchedule($iSingleHolidayRule,$iMemberOneSchedule,false);
      $oService->assignRuleToSchedule($iRepeatOvertimeRule,$iMemberOneSchedule,true);
      $oService->assignRuleToSchedule($iSingleOvertimeRule,$iMemberOneSchedule,false);
    
      $oService->assignRuleToSchedule($iRepeatBreakRule,$iMemberTwoSchedule,true);
      $oService->assignRuleToSchedule($iSingleBreakRule,$iMemberTwoSchedule,false);
      $oService->assignRuleToSchedule($iRepeatHolidayRule,$iMemberTwoSchedule,true);
      $oService->assignRuleToSchedule($iSingleHolidayRule,$iMemberTwoSchedule,false);
      $oService->assignRuleToSchedule($iRepeatOvertimeRule,$iMemberTwoSchedule,true);
      $oService->assignRuleToSchedule($iSingleOvertimeRule,$iMemberTwoSchedule,false);
      
      $oService->assignRuleToSchedule($iRepeatBreakRule,$iMemberThreeSchedule,true);
      $oService->assignRuleToSchedule($iSingleBreakRule,$iMemberThreeSchedule,false);
      $oService->assignRuleToSchedule($iRepeatHolidayRule,$iMemberThreeSchedule,true);
      $oService->assignRuleToSchedule($iSingleHolidayRule,$iMemberThreeSchedule,false);
      $oService->assignRuleToSchedule($iRepeatOvertimeRule,$iMemberThreeSchedule,true);
      $oService->assignRuleToSchedule($iSingleOvertimeRule,$iMemberThreeSchedule,false);
      
      
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
        'schedule_member_single' => $iMemberOneSchedule,
        'schedule_member_two'    => $iMemberTwoSchedule,
        'schedule_member_three'  => $iMemberThreeSchedule,
        'schedule_member_four'   => $iMemberFourSchedule,
        
      ];
      
    
      
      
   }  
   
   /**
    * @group Rollover
    */ 
   public function testRollverSteps()
   {
      $oNow       = $this->getContainer()->getNow();
      
      $iNewCalYear = $oNow->format('Y')+1;
      
      // Test Slot Rollover
      $this->RolloverCalendarAndSlots($oNow,$iNewCalYear,$this->aDatabaseId['five_minute'],$this->aDatabaseId['ten_minute']);
       
      // Test Schedule Rollover assumes that done slots and calendar already
      $this->RolloverSchedules($iNewCalYear,$this->aDatabaseId['member_two'], $this->aDatabaseId['member_four']); 
      
      $this->RolloverRules($iNewCalYear,3);
      
      $this->RolloverTeams($iNewCalYear,2);
      
   }
   
   
   
   public function RolloverCalendarAndSlots($oStartYear,$iNewCalYear,$iTestSlotId, $iDepSlotId)
   {
        
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
        # Add New Year
       
        $oCommand  = new CalAddYearCommand(1, $oStartYear->setDate($iNewCalYear,1,1));
       
        $oCommandBus->handle($oCommand);
       
        # Rollover timeslots
       
        $oCommand = new RolloverTimeslotCommand($iNewCalYear);
       
       
        $oCommandBus->handle($oCommand);
        
        // Test we have non empty affected count
        $this->assertGreaterThanOrEqual(0,$oCommand->getRollOverNumber());  
       
       // Assert that one slot has correct number of new slots assume the test slot is 12
        
        $numberSlots = (int)((60*24) / 5);
        
        // Assert max date is equal
        
        $iDayCount = (int) $oContainer->getDatabaseAdapter()->fetchColumn("select count(open_minute) 
                                                                           from bm_timeslot_day 
                                                                           where timeslot_id = ? "
                                                                           ,[$iTestSlotId],0,[]);
       
       
        $this->assertEquals($numberSlots,$iDayCount,'The Day slot are less than expected number'); 
        
        $iYearCount = (int) $oContainer->getDatabaseAdapter()->fetchColumn("select count(open_minute) 
                                                                            from bm_timeslot_year 
                                                                            where timeslot_id = ? 
                                                                            and Y = ?"
                                                                            ,[$iTestSlotId,$iNewCalYear],0,[]);
        $iDaysInYear = date("z", mktime(0,0,0,12,31,$iNewCalYear)) + 1;
        
        $this->assertGreaterThanOrEqual($iDayCount *$iDaysInYear, $iYearCount,'The year slot count is less than expected after a rollover' );
       
        // Make sure in active slots are ignored    
       
         $iYearCount = (int) $oContainer->getDatabaseAdapter()->fetchColumn("select count(open_minute) 
                                                                            from bm_timeslot_year 
                                                                            where timeslot_id = ? 
                                                                            and Y = ?"
                                                                            ,[$iDepSlotId,$iNewCalYear],0,[]);
        
        $this->assertEquals(0,$iYearCount,'The in active slot was rolled over when not of been');
   }
  
  
  
   public function RolloverSchedules($iNewCalYear, $iTestMemberId, $iMemberWithStoppedScheduleId)
   {
        
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
            
      
        $oCommand = new RolloverSchedulesCommand($iNewCalYear);
      
        $oCommandBus->handle($oCommand);
        
        // Test we have non empty affected count
        $this->assertGreaterThanOrEqual(0,$oCommand->getRollOverNumber());  
        
        // Test we have a new schedule for the member
        
        $iNewScheduleId = (int) $oContainer->getDatabaseAdapter()->fetchColumn("SELECT schedule_id FROM bm_schedule WHERE membership_id = ? AND calendar_year = ?",[$iTestMemberId,$iNewCalYear],0,[]);
        
        $this->assertGreaterThanOrEqual(0,$iNewScheduleId,"We do not have a new schedule for member $iTestMemberId when should have");
        
        // Test if we have slots for this schedule
        
        $iScheduleSlotCount = (int) $oContainer->getDatabaseAdapter()->fetchColumn("SELECT count(slot_open) FROM bm_schedule_slot WHERE schedule_id = ? ",[$iNewScheduleId],0,[]);
        
        $iNumberSlots = (int)((60*24) / 5);
        
        $this->assertGreaterThanOrEqual($iNumberSlots,$iScheduleSlotCount,"Not have enought slots for new calendar year schedule for member $iTestMemberId");
        
        // make sure stopped schedules are not rolled over
        $iNewScheduleId = (int) $oContainer->getDatabaseAdapter()->fetchColumn("SELECT schedule_id FROM bm_schedule WHERE membership_id = ? AND calendar_year = ?",[$iMemberWithStoppedScheduleId,$iNewCalYear],0,[]);
       
        
        $this->assertEquals(0,$iNewScheduleId,'A schedule was rollover for a stopped schedule when not have been');
               
   }
   
   
   
   public function RolloverRules($iNewCalYear,$iExpectedRollovers)
   {
       
       $oContainer  = $this->getContainer();
        
       $oCommandBus = $oContainer->getCommandBus(); 
            
      
       $oCommand = new RolloverRulesCommand($iNewCalYear);
      
       $oCommandBus->handle($oCommand);
       
       # Count see have expected number of rollovers
        
       $iNewRulesCount = (int) $oContainer->getDatabaseAdapter()->fetchColumn("SELECT count(rule_id) FROM bm_rule WHERE cal_year = ?",[$iNewCalYear],0,[]);
        
       $this->assertGreaterThanOrEqual($iExpectedRollovers,$iNewRulesCount,'The wrong number of rules have been rolled over into new calendar year');    
       
       # Do we have activity for these new rules
       $iNewRulesCount = (int) $oContainer->getDatabaseAdapter()->fetchColumn("SELECT count(rule_id) FROM bm_rule_series WHERE rule_id IN (SELECT rule_id FROM bm_rule WHERE cal_year = ?)",[$iNewCalYear],0,[]);
        
       $this->assertGreaterThanOrEqual($iExpectedRollovers,$iNewRulesCount,'The is wrong number of new rule series data after rollover');      
       
       
   }
   
   
   
   public function RolloverTeams($iNewCalYear, $iExpectedNewRelations)
   {
       $oContainer  = $this->getContainer();
        
       $oCommandBus = $oContainer->getCommandBus(); 
            
      
       $oCommand = new RolloverTeamsCommand($iNewCalYear);
      
       $oCommandBus->handle($oCommand);
       
         // Test we have non empty affected count
        $this->assertGreaterThan(0,$oCommand->getRollOverNumber());  
      
       
       $iNewRelationsCount = (int) $oContainer->getDatabaseAdapter()->fetchColumn("SELECT count(sm.schedule_id) 
                                                                                  FROM bm_schedule_team_members sm
                                                                                  JOIN bm_schedule s on sm.schedule_id = s.schedule_id
                                                                                  WHERE s.calendar_year = ?",[$iNewCalYear],0,[]);
     
       $this->assertGreaterThanOrEqual($iExpectedNewRelations,$iNewRelationsCount,'The number of new relations does not match');      
   
   }
   
    
}
/* end of file */
