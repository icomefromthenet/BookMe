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



class ScheduleAdvanceTest extends TestMgtBase
{
    
    
    protected $aDatabaseId = [];
    
    
    
   protected function handleEventPostFixtureRun()
   {
      // Create the Calendar 
      $oService = new BookMeService($this->getContainer());
      
      $oNow     = $this->getContainer()->getNow();
      
      
      $oStart = clone $oNow;
      $oStart->setDate($oNow->format('Y'),1,1);
      
      $oService->addCalenderYears(5,$oStart);
      
      // Timeslots
      
      $iFiveMinuteTimeslot    = $oService->addTimeslot(5);
      $iTenMinuteTimeslot     = $oService->addTimeslot(10);
      $iFifteenMinuteTimeslot = $oService->addTimeslot(15);

      $oService->toggleSlotAvability($iTenMinuteTimeslot);    
  
     // Teams
  
      $iMemberOne   = $oService->registerMembership();
      $iMemberTwo   = $oService->registerMembership();
      $iMemberThree = $oService->registerMembership();
      $iMemberFour  = $oService->registerMembership();
    
      $iTeamOne     = $oService->registerTeam($iFiveMinuteTimeslot);
      $iTeamTwo     = $oService->registerTeam($iFifteenMinuteTimeslot);
         
         
      // Schedules
      $iMemberOneSchedule   = $oService->startSchedule($iMemberOne,   $iFifteenMinuteTimeslot, $oNow->format('Y'));
      $iMemberTwoSchedule   = $oService->startSchedule($iMemberTwo,   $iFifteenMinuteTimeslot, $oNow->format('Y'));
      $iMemberThreeSchedule = $oService->startSchedule($iMemberThree, $iFifteenMinuteTimeslot, $oNow->format('Y'));
      $iMemberFourSchedule  = $oService->startSchedule($iMemberFour,  $iFifteenMinuteTimeslot, $oNow->format('Y'));
      
      // Rules Single
      $oSingleDate = clone $oNow;
      $oSingleDate->setDate($oNow->format('Y'),1,14);
        
      $oDayWorkDayRuleStart = clone $oNow;
      $oDayWorkDayRuleStart->setDate($oNow->format('Y'),1,1);
      
      $oDayWorkDayRuleEnd = clone $oNow;
      $oDayWorkDayRuleEnd->setDate($oNow->format('Y'),12,31);
      
        
      $iRepeatWorkDayRule    = $oService->createRepeatingWorkDayRule($oDayWorkDayRuleStart,$oDayWorkDayRuleEnd,$iFiveMinuteTimeslot,108,204,'1-5','*','2-12');
      $iSingleWorkDayRule    = $oService->createSingleWorkDayRule($oSingleDate,$iFiveMinuteTimeslot,540,1020); 
      
              
            
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
       $this->assertTrue(true);
       
       
    }
    
    
    protected function ApplyRuleTest($iRuleId, $iScheduleId)
    {
        $oContainer  = $this->getContainer();
        
        $oCommandBus = $oContainer->getCommandBus(); 
       
       
       $this->assertFalse(true);
        
    }
    
   
    
    
}
/* end of file */
