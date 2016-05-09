<?php
namespace IComeFromTheNet\BookMe\Test;

use DateTime;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Valitron\Validator;

use IComeFromTheNet\BookMe\Test\Base\TestRulesGroupBase;
use IComeFromTheNet\BookMe\BookMeService;
use IComeFromTheNet\BookMe\CronToQuery;
use IComeFromTheNet\BookMe\Bus\Command\CreateRuleCommand;
use IComeFromTheNet\BookMe\Bus\Command\AssignRuleToScheduleCommand;

use IComeFromTheNet\BookMe\Cron\ParsedRange;
use IComeFromTheNet\BookMe\Bus\Middleware\ValidationException;


class RulesTestCommandTest extends TestRulesGroupBase
{
    
    
   protected function handleEventPostFixtureRun()
   {
      // Create the Calendar 
      $oService = new BookMeService($this->getContainer());
      
      $oService->addCalenderYears(5);
      
      $iFiveMinuteTimeslot    = $oService->addTimeslot(5);
      $iTenMinuteTimeslot     = $oService->addTimeslot(10);
      $iSevenMinuteTimeslot    = $oService->addTimeslot(7);

      $oService->toggleSlotAvability($iTenMinuteTimeslot);    
  
      $iMemberOne   = $oService->registerMembership();
      $iMemberTwo   = $oService->registerMembership();
      $iMemberThree = $oService->registerMembership();
      $iMemberFour  = $oService->registerMembership();
    
      $iTeamOne     = $oService->registerTeam($iFiveMinuteTimeslot);
      $iTeamTwo     = $oService->registerTeam($iSevenMinuteTimeslot);
      
           
            
      $this->aDatabaseId = [
        'five_minute'    => $iFiveMinuteTimeslot,
        'ten_minute'     => $iTenMinuteTimeslot,
        'seven_minute'   => $iSevenMinuteTimeslot,
        'member_one'     => $iMemberOne,
        'member_two'     => $iMemberTwo,
        'member_three'   => $iMemberThree,
        'member_four'    => $iMemberFour,
        'team_two'       => $iTeamTwo,
        'team_one'       => $iTeamOne,
      ];
    
      
      
   }  
   
    /**
    * @group Rule
    */ 
   public function testRule()
   {
       $this->ContainerBuildsClass();
       $this->SegmentParserEntity();
       $this->SegmentParserEntityPassValidate();
       $this->SegmentParserEntityFailsValidate();
       $this->SegmentParserMonthSegment();
       $this->SegmentParserDayMonthSegment();
       $this->SegmentParserDayWeekSegment();
       $this->AssignRuleToScheduleCommand();
   }
   
   
   
    protected function ContainerBuildsClass()
    {
       
       $oContainer = $this->getContainer();
       $oStartDate = new DateTime();
       $oStopDate  = new DateTime(); 
       $oStopDate->setDate($oStartDate->format('Y'),'12','31');
       
       $oSegmentParser   = $oContainer->getCronSegementParser();
       $oSlotFinder      = $oContainer->getSlotFinder();
       
       $this->assertInstanceOf('IComeFromTheNet\BookMe\Cron\SegmentParser',$oSegmentParser);
       $this->assertInstanceOf('IComeFromTheNet\BookMe\Cron\SlotFinder',$oSlotFinder);
       
       $oCronToQuery = $oContainer->getCronToQuery();
       
       $this->assertInstanceOf('IComeFromTheNet\BookMe\Cron\CronToQuery',$oCronToQuery);
       
    }
    
  
    protected function SegmentParserEntity()
    {
        $oContainer = $this->getContainer();
        
        $iSegmentOrder = 1;
        $iRangeOpen  = 1;
        $iRangeClose = 100;
        $iModVaue    = 1;
        $sRangeType = 'minute';
        
        
        # Test parsed range accessors
        $oRange = new ParsedRange($iSegmentOrder,$iRangeOpen,$iRangeClose,$iModVaue, $sRangeType); 
        
        $this->assertEquals($iSegmentOrder, $oRange->getSegmentOrder());
        $this->assertEquals($iRangeOpen,  $oRange->getRangeOpen());
        $this->assertEquals($iRangeClose, $oRange->getRangeClose() );
        $this->assertEquals($iModVaue, $oRange->getModValue() );
        $this->assertEquals($sRangeType,$oRange->getRangeType());
        
    }
    
   
    protected function SegmentParserEntityPassValidate()
    {
        $oContainer = $this->getContainer();
        $oSegmentParser   = $oContainer->getCronSegementParser();
        
        $iSegmentOrder = 1;
        $iRangeOpen  = 1;
        $iRangeClose = 100;
        $iModVaue    = 1;
        $sRangeType = 'minute';
        
        
        # Test parsed range accessors
        $oRange = new ParsedRange($iSegmentOrder,$iRangeOpen,$iRangeClose,$iModVaue, $sRangeType); 
        
        $this->assertEquals($iSegmentOrder, $oRange->getSegmentOrder());
        $this->assertEquals($iRangeOpen,  $oRange->getRangeOpen());
        $this->assertEquals($iRangeClose, $oRange->getRangeClose() );
        $this->assertEquals($iModVaue, $oRange->getModValue() );
        $this->assertEquals($sRangeType,$oRange->getRangeType());
        
        $this->assertTrue($oRange->validate());
        
    }
    
    protected function SegmentParserEntityFailsValidate()
    {
        $oContainer = $this->getContainer();
        $oSegmentParser   = $oContainer->getCronSegementParser();
        
        $iSegmentOrder = -1;
        $iRangeOpen  = 1;
        $iRangeClose = 100;
        $iModVaue    = 1;
        $sRangeType = 'minute';
        
        
        # Test parsed range accessors
        $oRange = new ParsedRange($iSegmentOrder,$iRangeOpen,$iRangeClose,$iModVaue, $sRangeType); 
        
        $this->assertEquals($iSegmentOrder, $oRange->getSegmentOrder());
        $this->assertEquals($iRangeOpen,  $oRange->getRangeOpen());
        $this->assertEquals($iRangeClose, $oRange->getRangeClose() );
        $this->assertEquals($iModVaue, $oRange->getModValue() );
        $this->assertEquals($sRangeType,$oRange->getRangeType());
        
        try {
            $oRange->validate();
            $this->assertTrue(false,'ParsedRange shoud not passed validation');
            
        } catch(ValidationException $e) {
            $this->assertTrue(true);
        }
        
        
    }
    
   
    protected function SegmentParserMonthSegment()
    {
        $oContainer = $this->getContainer();
        $oSegmentParser   = $oContainer->getCronSegementParser();
        
        $sCronType  = ParsedRange::TYPE_MONTH;
        $sCronExpr = '*';
        $sCronExprA = '1-12/2';
        $sCronExprB = '2/2';
        $sCronExprC = '10-12,7-12';
        $sCronExprD = '3';
        $sCronExprE = '*/3';
        
        $aRange = $oSegmentParser->parseSegment($sCronType,$sCronExpr);    
        
        $this->assertCount(1,$aRange);
        $this->assertEquals(1,$aRange[0]->getRangeOpen());
        $this->assertEquals(12,$aRange[0]->getRangeClose());
        $this->assertEquals(1,$aRange[0]->getModValue());
       
        
        $aRange = $oSegmentParser->parseSegment($sCronType,$sCronExprA);    
        
        $this->assertCount(1,$aRange);
        $this->assertEquals(1,$aRange[0]->getRangeOpen());
        $this->assertEquals(12,$aRange[0]->getRangeClose());
        $this->assertEquals(2,$aRange[0]->getModValue());
        
        $aRange = $oSegmentParser->parseSegment($sCronType,$sCronExprB);    
        
        $this->assertCount(1,$aRange);
        $this->assertEquals(2,$aRange[0]->getRangeOpen());
        $this->assertEquals(12,$aRange[0]->getRangeClose());
        $this->assertEquals(2,$aRange[0]->getModValue());
        
        $aRange = $oSegmentParser->parseSegment($sCronType,$sCronExprC);    
        
        $this->assertCount(2,$aRange);
        $this->assertEquals(10,$aRange[0]->getRangeOpen());
        $this->assertEquals(12,$aRange[0]->getRangeClose());
        $this->assertEquals(1,$aRange[0]->getModValue());
        $this->assertEquals(7,$aRange[1]->getRangeOpen());
        $this->assertEquals(12,$aRange[1]->getRangeClose());
        $this->assertEquals(1,$aRange[1]->getModValue());
    
        
         
        $aRange = $oSegmentParser->parseSegment($sCronType,$sCronExprD);    
        
        $this->assertCount(1,$aRange);
        $this->assertEquals(3,$aRange[0]->getRangeOpen());
        $this->assertEquals(3,$aRange[0]->getRangeClose());
        $this->assertEquals(1,$aRange[0]->getModValue());
        
        
        
        $aRange = $oSegmentParser->parseSegment($sCronType,$sCronExprE);    
        
        $this->assertCount(1,$aRange);
        $this->assertEquals(1,$aRange[0]->getRangeOpen());
        $this->assertEquals(12,$aRange[0]->getRangeClose());
        $this->assertEquals(3,$aRange[0]->getModValue());
       
      
      
    }
    
   
    protected function SegmentParserDayMonthSegment()
    {
        $oContainer = $this->getContainer();
        $oSegmentParser   = $oContainer->getCronSegementParser();
        
        $sCronType  = ParsedRange::TYPE_DAYOFMONTH;
        $sCronExpr = '*';
        $sCronExprA = '1-20/2';
        $sCronExprB = '2/2';
        $sCronExprC = '1-6,7-12';
        $sCronExprD = '3';
        $sCronExprE = '*/3';
        
        $aRange = $oSegmentParser->parseSegment($sCronType,$sCronExpr);    
        
        $this->assertCount(1,$aRange);
        $this->assertEquals(1,$aRange[0]->getRangeOpen());
        $this->assertEquals(31,$aRange[0]->getRangeClose());
        $this->assertEquals(1,$aRange[0]->getModValue());
       
        
        $aRange = $oSegmentParser->parseSegment($sCronType,$sCronExprA);    
        
        $this->assertCount(1,$aRange);
        $this->assertEquals(1,$aRange[0]->getRangeOpen());
        $this->assertEquals(20,$aRange[0]->getRangeClose());
        $this->assertEquals(2,$aRange[0]->getModValue());
        
        $aRange = $oSegmentParser->parseSegment($sCronType,$sCronExprB);    
        
        $this->assertCount(1,$aRange);
        $this->assertEquals(2,$aRange[0]->getRangeOpen());
        $this->assertEquals(31,$aRange[0]->getRangeClose());
        $this->assertEquals(2,$aRange[0]->getModValue());
        
        $aRange = $oSegmentParser->parseSegment($sCronType,$sCronExprC);    
        
        $this->assertCount(2,$aRange);
        $this->assertEquals(1,$aRange[0]->getRangeOpen());
        $this->assertEquals(6,$aRange[0]->getRangeClose());
        $this->assertEquals(1,$aRange[0]->getModValue());
        $this->assertEquals(7,$aRange[1]->getRangeOpen());
        $this->assertEquals(12,$aRange[1]->getRangeClose());
        $this->assertEquals(1,$aRange[1]->getModValue());
    
        
         
        $aRange = $oSegmentParser->parseSegment($sCronType,$sCronExprD);    
        
        $this->assertCount(1,$aRange);
        $this->assertEquals(3,$aRange[0]->getRangeOpen());
        $this->assertEquals(3,$aRange[0]->getRangeClose());
        $this->assertEquals(1,$aRange[0]->getModValue());
        
        
        
        $aRange = $oSegmentParser->parseSegment($sCronType,$sCronExprE);    
        
        $this->assertCount(1,$aRange);
        $this->assertEquals(1,$aRange[0]->getRangeOpen());
        $this->assertEquals(31,$aRange[0]->getRangeClose());
        $this->assertEquals(3,$aRange[0]->getModValue());
       
      
      
    }
    
    
    protected function SegmentParserDayWeekSegment()
    {
        $oContainer = $this->getContainer();
        $oSegmentParser   = $oContainer->getCronSegementParser();
        
        $sCronType  = ParsedRange::TYPE_DAYOFWEEK;
        $sCronExpr = '*';
        $sCronExprA = '0-6/2';
        $sCronExprB = '6/2';
        $sCronExprC = '1-6,0-5';
        $sCronExprD = '3';
        $sCronExprE = '*/3';
        
        $aRange = $oSegmentParser->parseSegment($sCronType,$sCronExpr);    
        
        $this->assertCount(1,$aRange);
        $this->assertEquals(0,$aRange[0]->getRangeOpen());
        $this->assertEquals(6,$aRange[0]->getRangeClose());
        $this->assertEquals(1,$aRange[0]->getModValue());
       
        
        $aRange = $oSegmentParser->parseSegment($sCronType,$sCronExprA);    
        
        $this->assertCount(1,$aRange);
        $this->assertEquals(0,$aRange[0]->getRangeOpen());
        $this->assertEquals(6,$aRange[0]->getRangeClose());
        $this->assertEquals(2,$aRange[0]->getModValue());
        
        $aRange = $oSegmentParser->parseSegment($sCronType,$sCronExprB);    
        
        $this->assertCount(1,$aRange);
        $this->assertEquals(6,$aRange[0]->getRangeOpen());
        $this->assertEquals(6,$aRange[0]->getRangeClose());
        $this->assertEquals(2,$aRange[0]->getModValue());
        
        $aRange = $oSegmentParser->parseSegment($sCronType,$sCronExprC);    
        
        $this->assertCount(2,$aRange);
        $this->assertEquals(1,$aRange[0]->getRangeOpen());
        $this->assertEquals(6,$aRange[0]->getRangeClose());
        $this->assertEquals(1,$aRange[0]->getModValue());
        $this->assertEquals(0,$aRange[1]->getRangeOpen());
        $this->assertEquals(5,$aRange[1]->getRangeClose());
        $this->assertEquals(1,$aRange[1]->getModValue());
    
        
         
        $aRange = $oSegmentParser->parseSegment($sCronType,$sCronExprD);    
        
        $this->assertCount(1,$aRange);
        $this->assertEquals(3,$aRange[0]->getRangeOpen());
        $this->assertEquals(3,$aRange[0]->getRangeClose());
        $this->assertEquals(1,$aRange[0]->getModValue());
        
        
        
        $aRange = $oSegmentParser->parseSegment($sCronType,$sCronExprE);    
        
        $this->assertCount(1,$aRange);
        $this->assertEquals(0,$aRange[0]->getRangeOpen());
        $this->assertEquals(6,$aRange[0]->getRangeClose());
        $this->assertEquals(3,$aRange[0]->getModValue());
       
      
      
    }
    
    
    protected function AssignRuleToScheduleCommand()
    {
        
        $iRuleDatabaseId        = 1;
        $iScheduleDatabaseId    = 2;
        $bRolloverFlag          = true;
        
        $oCommand = new AssignRuleToScheduleCommand($iScheduleDatabaseId, $iRuleDatabaseId, $bRolloverFlag);
        
        
        $this->assertEquals($iRuleDatabaseId, $oCommand->getRuleId());
        $this->assertEquals($iScheduleDatabaseId, $oCommand->getScheduleId());
        $this->assertEquals($bRolloverFlag, $oCommand->getRolloverFlag());
        
        $aRules     = $oCommand->getRules();
        $aData      = $oCommand->getData();
        $oValidator = new Validator($aData);
            
        
        $oValidator->rules($aRules);
        
        $this->assertTrue($oValidator->validate(),'AssignRuleToScheduleCommand is invalid when should be valid');
        
    }
    
    /**
    * @group Rule
    */
    public function testSlotFinder()
    {
        $oContainer  = $this->getContainer();
        $oDatabase   = $oContainer->getDatabaseAdapter();
        $oSlotFinder = $oContainer->getSlotFinder();
        $oNow        = $oContainer->getNow();
        $oRangeMonth      = new ParsedRange(1,10,12,1,ParsedRange::TYPE_MONTH);
        $oRangeDayOfMonth = new ParsedRange(2,1,14,1,ParsedRange::TYPE_DAYOFMONTH);
        $oRangeDayOfWeek = new ParsedRange(3,0,6,1,ParsedRange::TYPE_DAYOFWEEK);
     
        // from june to the end of the year
        $oStartDate  = clone $oNow;
        $oEndDate    = clone $oNow;
        $oStartDate->setDate($oStartDate->format('Y'),'06','1');
        $oEndDate->setDate($oStartDate->format('Y'),'12','31');
        $iOpeningTimeslot = 1000;
        $iClosingTimeslot = 1440;
        $iTimeslotId = $this->aDatabaseId['ten_minute'];
        
        $oCommand = new CreateRuleCommand($oStartDate, $oEndDate, 1, $iTimeslotId, $iOpeningTimeslot, $iClosingTimeslot,'*', '1-14','10-12');
        
        $oSlotFinder->findSlots($oCommand,array($oRangeMonth, $oRangeDayOfMonth, $oRangeDayOfWeek));
        
        $oDateType = Type::getType(Type::DATETIME);
        
        $oOpeningFirstSlot = $oDateType->convertToPHPValue($oDatabase->fetchColumn("SELECT min(opening_slot) FROM bm_tmp_rule_series",[],0), $oDatabase->getDatabasePlatform());
        $oClosingLastSlot = $oDateType->convertToPHPValue($oDatabase->fetchColumn("SELECT max(closing_slot) FROM bm_tmp_rule_series",[],0), $oDatabase->getDatabasePlatform());
        
        $this->assertEquals('01-10-2016',$oOpeningFirstSlot->format('d-m-Y'),'Opening slot has wrong date');
        $this->assertEquals('15-11-2016',$oClosingLastSlot->format('d-m-Y'),'Closing slot has wrong date');
        
        $this->assertEquals('16:40',$oOpeningFirstSlot->format('H:i'),'Opening minute has wrong date');
        $this->assertEquals('00:00',$oClosingLastSlot->format('H:i'),'Closing minute has wrong date');
        
    }
    
    

    /**
    * @group Rule
    */ 
    public function testNewRule()
    {
       
        $oContainer = $this->getContainer();
        $oDatabase   = $oContainer->getDatabaseAdapter();
        $oNow        = $oContainer->getNow();
     
        $oStartDate  = clone $oNow;
        $oEndDate    = clone $oNow;
        $oStartDate->setDate($oStartDate->format('Y'),'06','1');
        $oEndDate->setDate($oStartDate->format('Y'),'12','31');
        $iOpeningTimeslot = 1000;
        $iClosingTimeslot = 1440;
        $iTimeslotId = $this->aDatabaseId['ten_minute'];
 
        
        $oCommand = new CreateRuleCommand($oStartDate, $oEndDate, 1, $iTimeslotId, $iOpeningTimeslot, $iClosingTimeslot,'*', '1-14','10-12');
        
        $oContainer->getCommandBus()->handle($oCommand);
        
        $oDateType = Type::getType(Type::DATETIME);
        
        $oOpeningFirstSlot = $oDateType->convertToPHPValue($oDatabase->fetchColumn("SELECT min(opening_slot) FROM bm_tmp_rule_series",[],0), $oDatabase->getDatabasePlatform());
        $oClosingLastSlot = $oDateType->convertToPHPValue($oDatabase->fetchColumn("SELECT max(closing_slot) FROM bm_tmp_rule_series",[],0), $oDatabase->getDatabasePlatform());
        
        $this->assertEquals('01-10-2016',$oOpeningFirstSlot->format('d-m-Y'),'Opening slot has wrong date');
        $this->assertEquals('15-11-2016',$oClosingLastSlot->format('d-m-Y'),'Closing slot has wrong date');
        
        $this->assertEquals('16:40',$oOpeningFirstSlot->format('H:i'),'Opening minute has wrong date');
        $this->assertEquals('00:00',$oClosingLastSlot->format('H:i'),'Closing minute has wrong date');
       
    }
    
    
    /**
    * @group Rule
    */ 
    public function testNewSingleDayRule()
    {
       
        $oContainer = $this->getContainer();
        $oDatabase   = $oContainer->getDatabaseAdapter();
        $oNow        = $oContainer->getNow();
     
        $oStartDate  = clone $oNow;
        $oEndDate    = clone $oNow;
        $oStartDate->setDate($oStartDate->format('Y'),'06','1');
        $oEndDate->setDate($oStartDate->format('Y'),'06','1');
        $iOpeningTimeslot = 1000;
        $iClosingTimeslot = 1440;
        $iTimeslotId = $this->aDatabaseId['ten_minute'];
 
        
        $oCommand = new CreateRuleCommand($oStartDate, $oEndDate, 1, $iTimeslotId, $iOpeningTimeslot, $iClosingTimeslot,'*', '*','*',true);
        
        $oContainer->getCommandBus()->handle($oCommand);
        
        $oDateType = Type::getType(Type::DATETIME);
        
        $oOpeningFirstSlot = $oDateType->convertToPHPValue($oDatabase->fetchColumn("SELECT min(opening_slot) FROM bm_tmp_rule_series",[],0), $oDatabase->getDatabasePlatform());
        $oClosingLastSlot = $oDateType->convertToPHPValue($oDatabase->fetchColumn("SELECT max(closing_slot) FROM bm_tmp_rule_series",[],0), $oDatabase->getDatabasePlatform());
        
        $this->assertEquals('01-06-2016',$oOpeningFirstSlot->format('d-m-Y'),'Opening slot has wrong date');
        $this->assertEquals('02-06-2016',$oClosingLastSlot->format('d-m-Y'),'Closing slot has wrong date');
        
        $this->assertEquals('16:40',$oOpeningFirstSlot->format('H:i'),'Opening minute has wrong date');
        $this->assertEquals('00:00',$oClosingLastSlot->format('H:i'),'Closing minute has wrong date');
       
    }
    
    
  
    
}
/* end of file */
