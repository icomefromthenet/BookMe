<?php
namespace IComeFromTheNet\BookMe\Tests;

use IComeFromTheNet\BookMe\BookMeService;
use IComeFromTheNet\BookMe\Tests\BasicTest;
use Doctrine\DBAL\DBALException;

class RulesTimeslotTest extends BasicTest
{
    
    public function testTimeslotDetailsQuery()
    {
        $db         = $this->getDoctrineConnection();
        $timeSlotID = 148376;
        $groupID    = 11;
        $foundRules = array();    
        $rules      = array('6','7');
        
        $resultSTH = $db->executeQuery('CALL bm_rules_timeslot_details(?,NULL,?,NULL)',array($timeSlotID,$groupID));
        
        while($result = $resultSTH->fetch()) {
            $foundRules[] = $result['rule_id'];    
        }
        
        $this->assertEmpty(array_diff($foundRules,$rules));
    }
    
    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Either a member or a schedule group must be supplied
    */ 
    public function testTimeslotDetailsQueryReqiuresEitherMemberOrGroup()
    {
        $db         = $this->getDoctrineConnection(); 
        $timeSlotID = 148376;
        $resultSTH = $db->executeQuery('CALL bm_rules_timeslot_details(?,NULL,?,NULL)',array($timeSlotID,NULL));
    }
        
    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Either a member or a schedule group must be supplied
    */ 
    public function testTimeslotDetailsQueryReqiuresEitherGroupOrMember()
    {
        $db         = $this->getDoctrineConnection(); 
        $timeSlotID = 148376;
        $resultSTH = $db->executeQuery('CALL bm_rules_timeslot_details(?,NULL,NULL,NULL)',array($timeSlotID));
    }
    
    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Either a valid rule type or none must be supplied
    */ 
    public function  testTimeslotDetailsQueryFailsInvalidRuleType() 
    {
        $db         = $this->getDoctrineConnection();
        $timeSlotID = 148376;
        $groupID    = 11;
        $foundRules = array();    
        $rules      = array('6','7');
        $ruleType   = 'randomRule';
        $resultSTH = $db->executeQuery('CALL bm_rules_timeslot_details(?,NULL,?,?)',array($timeSlotID,$groupID,$ruleType));
        
    }
    
    
    public function testTimeslotDetailsQueryWithRuleTypeLimit()
    {
        $db         = $this->getDoctrineConnection();
        $timeSlotID = 148376;
        $groupID    = 11;
        $foundRules = array();    
        $rules      = array('6');
        $ruleType   = 'inclusion';
        $resultSTH = $db->executeQuery('CALL bm_rules_timeslot_details(?,NULL,?,?)',array($timeSlotID,$groupID,$ruleType));
        
        while($result = $resultSTH->fetch()) {
            $foundRules[] = $result['rule_id'];    
        }
        
        $this->assertEmpty(array_diff($foundRules,$rules));
    }
    
    
    public function testTimeslotRuleSummaryQuery()
    {
        $db         = $this->getDoctrineConnection();
        
        $openingTimeSlotID  = 246786;
        $closingTimeSlotID  = 246786;
        $timeslotID         = 4; // hour slot
        $results            = array();
        
        $resultSTH = $db->executeQuery('CALL bm_rules_timeslot_summary(?,?,?,NULL,NULL)',array($openingTimeSlotID,$closingTimeSlotID,$timeslotID));
        
        $result = $resultSTH->fetch();
        
        # have expected result set
        $this->assertEquals("1",$result['has_rule']);
        $this->assertEquals("1",$result['has_exclusion']);
        $this->assertEquals("1",$result['has_inclusion']);
        $this->assertEquals("0",$result['has_priority']);
        $this->assertEquals("4",$result['timeslot_id']);
        $this->assertEquals("246786",$result['timeslot_slot_id']);
        
        # only got single slot with the above range
        $result = $resultSTH->fetch();
        $this->assertEmpty($result);
        
    }
    
    
}
/* End of class */