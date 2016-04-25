<?php
namespace IComeFromTheNet\BookMe\Test;

use DateTime;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

use IComeFromTheNet\BookMe\Test\Base\TestRulesGroupBase;
use IComeFromTheNet\BookMe\BookMeService;
use IComeFromTheNet\BookMe\CronToQuery;
use IComeFromTheNet\BookMe\Bus\Command\CreateRuleCommand;
use IComeFromTheNet\BookMe\Cron\ParsedRange;
use IComeFromTheNet\BookMe\Bus\Middleware\ValidationException;


class CronToQueryCommandTest extends TestRulesGroupBase
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
    * @group Rule
    */ 
    public function testContainerBuildsClass()
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
    
    /**
    * @group Rule
    */
    public function testSegmentParserEntity()
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
    
     /**
    * @group Rule
    */
    public function testSegmentParserEntityPassValidate()
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
    
    /**
    * @group Rule
    */
    public function testSegmentParserEntityFailsValidate()
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
    
   
    /**
    * @group Rule
    */
    public function testSegmentParserMonthSegment()
    {
        $oContainer = $this->getContainer();
        $oSegmentParser   = $oContainer->getCronSegementParser();
        
        $sCronType  = ParsedRange::TYPE_MONTH;
        $sCronExprA = '*';
        $sCronExprB = '1-6,7-12';
        
        $aRange = $oSegmentParser->parseSegment($sCronType,$sCronExprA);    
        
        $this->assertCount(1,$aRange);
        $this->assertEquals(1,$aRange[0]->getRangeOpen());
        $this->assertEquals(12,$aRange[0]->getRangeClose());
        $this->assertEquals(1,$aRange[0]->getModValue());
        
        
        $aRange = $oSegmentParser->parseSegment($sCronType,$sCronExprB);    
        
        $this->assertCount(2,$aRange);
        $this->assertEquals(1,$aRange[0]->getRangeOpen());
        $this->assertEquals(6,$aRange[0]->getRangeClose());
        $this->assertEquals(1,$aRange[0]->getModValue());
        $this->assertEquals(7,$aRange[1]->getRangeOpen());
        $this->assertEquals(12,$aRange[1]->getRangeClose());
        $this->assertEquals(1,$aRange[1]->getModValue());
    }
    
    // /**
    // * @group Rule
    // */ 
    // public function testValidCommand()
    // {
       
    //   $oContainer = $this->getContainer();
    //   $oStartDate = new DateTime();
    //   $oStopDate  = new DateTime(); 
    //   $oStopDate->setDate($oStartDate->format('Y'),'12','31');
       
    //   $oCronToQuery = $oContainer->getCronToQuery();
      
    //   $iOpeningSlot = $oContainer->getDatabase()
    //                                 ->fetchAll('SELECT min(open_minute) as open_minute, null
    //                                               FROM bm_timeslot_day 
    //                                               WHERE timeslot_id= ?
    //                                               AND open_minute >= (60*13)
    //                                               AND close_minute <= (60*16)
    //                                               group by timeslot_id, open_minute
    //                                             UNION
    //                                             SELECT null, max(close_minute) as close_minute 
    //                                               FROM bm_timeslot_day 
    //                                               WHERE timeslot_id= ?
    //                                               AND open_minute >= (60*13)
    //                                               AND close_minute <= (60*16)
    //                                               group by timeslot_id, close_minute
                                                
    //                                               '
    //                                                 ,[$this->aDatabaseId['five_minute']]);   
      
      
      
    //   $oCommand = new CreateRuleCommand($oStartDate, $oStopDate, 2, $iOpeningSlot ); 
       
    // }
    
    
}
/* end of file */
