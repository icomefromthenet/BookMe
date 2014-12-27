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
}
/* End of class */