<?php
namespace IComeFromTheNet\BookMe\Tests;

use IComeFromTheNet\BookMe\BookMeService;
use IComeFromTheNet\BookMe\Tests\BasicTest;
use Doctrine\DBAL\DBALException;

class RulesMaxBookPackageTest extends BasicTest
{
    
    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Validity period is and invalid range
    */
    public function testNewMaxBookingRuleFailsBadValidityRange()
    {
        
        $db = $this->getDoctrineConnection();
        
        $ruleName = 'maxbook1';
        $validFrom = $db->fetchColumn("SELECT CAST(NOW() AS DATE)",array(),0);
        $validTo   = $db->fetchColumn("SELECT CAST((NOW() - INTERVAL 1 WEEK) AS DATE)",array(),0);
        $calenderType = 'day';
        $maxBookNumber  = 5;
        
        $db->executeQuery('CALL bm_rules_maxbook_add_rule(?,?,?,?,?,@newRuleID)',array($ruleName,$validFrom,$validTo,$calenderType,$maxBookNumber)); 
        
    }
    
    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Calendar Type must be one of the following::day,week,month,year
    */
    public function testNewMaxBookingRuleFailsBadCalendarType()
    {
        $db = $this->getDoctrineConnection();
        
        $ruleName = 'maxbook1';
        $validFrom = $db->fetchColumn("SELECT CAST(NOW() AS DATE)",array(),0);
        $validTo   = $db->fetchColumn("SELECT CAST((NOW() + INTERVAL 1 WEEK) AS DATE)",array(),0);
        $calenderType = 'dddday';
        $maxBookNumber  = 5;
        
        $db->executeQuery('CALL bm_rules_maxbook_add_rule(?,?,?,?,?,@newRuleID)',array($ruleName,$validFrom,$validTo,$calenderType,$maxBookNumber)); 
       
    }
    
    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Max Booking Number must be gt 0
    */
    public function testNewMaxBookingRuleFailsInvalidMax()
    {
        $db = $this->getDoctrineConnection();
        
        $ruleName = 'maxbook1';
        $validFrom = $db->fetchColumn("SELECT CAST(NOW() AS DATE)",array(),0);
        $validTo   = $db->fetchColumn("SELECT CAST((NOW() + INTERVAL 1 WEEK) AS DATE)",array(),0);
        $calenderType = 'day';
        $maxBookNumber  = 0;
        
        $db->executeQuery('CALL bm_rules_maxbook_add_rule(?,?,?,?,?,@newRuleID)',array($ruleName,$validFrom,$validTo,$calenderType,$maxBookNumber)); 
       
    }
    
    public function testNewMaxBookSuccess()
    {
        $db = $this->getDoctrineConnection();
        
        $ruleName = 'maxbook1';
        $validFrom = $db->fetchColumn("SELECT CAST(NOW() AS DATE)",array(),0);
        $validTo   = $db->fetchColumn("SELECT CAST((NOW() + INTERVAL 1 WEEK) AS DATE)",array(),0);
        $validToInternal   = $db->fetchColumn("SELECT CAST((NOW() + INTERVAL 1 WEEK + INTERVAL 1 DAY) AS DATE)",array(),0);
        $calenderType = 'day';
        $maxBookNumber  = 5;
        $changeTime = $db->fetchColumn('SELECT CAST(NOW() AS DATETIME)',array(),0);
     
        
        $db->executeQuery('CALL bm_rules_maxbook_add_rule(?,?,?,?,?,@newRuleID)',array($ruleName,$validFrom,$validTo,$calenderType,$maxBookNumber)); 
      
        $newRuleID = $db->fetchColumn("SELECT @newRuleID",array(),0);
        
        $this->assertNotEmpty($newRuleID);
        
        # verify the values in common table
        $map = array(
             'rule_id'       => $newRuleID
            ,'rule_name'     => $ruleName
            ,'rule_type'     => 'maxbook'
            ,'rule_repeat'   => 'runtime'
            ,'valid_from'    => $validFrom
            ,'valid_to'      => $validToInternal
            ,'rule_duration' => 0
        );
        
        $ruleSTH = $db->executeQuery('SELECT * FROM `rules` WHERE `rule_id` = ?',array($newRuleID),array());
        $ruleResult = $ruleSTH->fetch();
        $this->assertNotEmpty($ruleResult);
        
        foreach($ruleResult as $key => $value) {
            $this->assertEquals($map[$key],$value);
        }
        
        
        # verify the values in concrete table
        $map['calendar_period'] = $calenderType;
        $map['max_bookings']  = $maxBookNumber;
        
        $ruleSTH = $db->executeQuery('SELECT * FROM `rules_maxbook` WHERE `rule_id` = ?',array($newRuleID),array());
        $ruleResult = $ruleSTH->fetch();
        $this->assertNotEmpty($ruleResult);
        
        foreach($ruleResult as $key => $value) {
            $this->assertEquals($map[$key],$value);
        }
        
        
        # verify the audit insert trigger has correct values
        $map['changed_by'] = $db->fetchColumn('SELECT USER()',array(),0);
        $map['action'] = 'I';
        $map['change_time'] = $changeTime;
          
        $ruleSTH = $db->executeQuery("SELECT * FROM `audit_rules_maxbook` WHERE `rule_id` = ? and action = 'I'",array($newRuleID),array());
        $ruleResult = $ruleSTH->fetch();
        $this->assertNotEmpty($ruleResult);
        
        foreach($ruleResult as $key => $value) {
            if($key !== 'change_seq') {
                $this->assertEquals($map[$key],$value);
            }
        }
        
        # vefify the update trigger 
        $newRuleName = 'bookv2';
        $map['rule_name'] = $newRuleName;
        $map['action'] = 'U';
        $db->executeQuery('UPDATE `rules_maxbook` SET rule_name = ? WHERE `rule_id` = ?',array($newRuleName,$newRuleID),array());
        
        
        $ruleSTH = $db->executeQuery("SELECT * FROM `audit_rules_maxbook` 
                                      WHERE `rule_id` = ? 
                                      AND action = 'U' 
                                      ORDER BY change_seq DESC 
                                      LIMIT 1",array($newRuleID),array());
        $ruleResult = $ruleSTH->fetch();
        $this->assertNotEmpty($ruleResult);
        $this->assertEquals($newRuleName,$ruleResult['rule_name']);                              
            
        foreach($ruleResult as $key => $value) {
            if($key !== 'change_seq') {
                $this->assertEquals($map[$key],$value);
            }
        }                          
        
        #verify the delete trigger
        $map['action'] = 'D';
        $db->executeQuery('DELETE FROM `rules_maxbook` WHERE `rule_id` = ?',array($newRuleID),array());
        $ruleSTH = $db->executeQuery("SELECT valid_to FROM `audit_rules_maxbook` 
                                      WHERE `rule_id` = ? 
                                      AND action = 'D' 
                                      ORDER BY change_seq DESC 
                                      LIMIT 1",array($newRuleID),array());
        $ruleResult = $ruleSTH->fetch();
        $this->assertNotEmpty($ruleResult);
        foreach($ruleResult as $key => $value) {
            if($key !== 'change_seq') {
                $this->assertEquals($map[$key],$value);
            }
        } 
        
    }
    
    public function testDepreciateMaxbookRule()
    {
        $db = $this->getDoctrineConnection();
        
        $ruleName = 'maxbook1';
        $validFrom = $db->fetchColumn("SELECT CAST(NOW() AS DATE)",array(),0);
        $validTo   = $db->fetchColumn("SELECT CAST((NOW() + INTERVAL 1 WEEK) AS DATE)",array(),0);
        $validToInternal   = $db->fetchColumn("SELECT CAST((NOW() + INTERVAL 1 WEEK + INTERVAL 1 DAY) AS DATE)",array(),0);
        $calenderType = 'day';
        $maxBookNumber  = 5;
        $changeTime = $db->fetchColumn('SELECT CAST(NOW() AS DATETIME)',array(),0);
     
        
        $db->executeQuery('CALL bm_rules_maxbook_add_rule(?,?,?,?,?,@newRuleID)',array($ruleName,$validFrom,$validTo,$calenderType,$maxBookNumber)); 
      
        $newRuleID = $db->fetchColumn("SELECT @newRuleID",array(),0);
     
        
        $newValidTo   = $db->fetchColumn("SELECT CAST((NOW() + INTERVAL 3 DAY) AS DATE)",array(),0);
        $newValidToInternal   = $db->fetchColumn("SELECT CAST((NOW() + INTERVAL 4 DAY) AS DATE)",array(),0);
        
        
        $db->executeQuery('CALL bm_rules_depreciate_rule(?,?)',array($newRuleID,$newValidTo),array());
        
        # vefiy the common table updated
        $ruleSTH = $db->executeQuery('SELECT valid_to FROM `rules` WHERE `rule_id` = ?',array($newRuleID),array());
        $ruleResult = $ruleSTH->fetch();
        $this->assertNotEmpty($ruleResult);
        $this->assertEquals($newValidToInternal,$ruleResult['valid_to']);
        
        
        # verify the concrete table updated
        $ruleSTH = $db->executeQuery('SELECT valid_to FROM `rules_maxbook` WHERE `rule_id` = ?',array($newRuleID),array());
        $ruleResult = $ruleSTH->fetch();
        $this->assertNotEmpty($ruleResult);
        $this->assertEquals($newValidToInternal,$ruleResult['valid_to']);
        
        # verify the audit update update trigger correct
        $ruleSTH = $db->executeQuery("SELECT valid_to FROM `audit_rules_maxbook` 
                                      WHERE `rule_id` = ? 
                                      AND action = 'U' 
                                      ORDER BY change_seq DESC 
                                      LIMIT 1",array($newRuleID),array());
        
        $ruleResult = $ruleSTH->fetch();
        $this->assertNotEmpty($ruleResult);
        $this->assertEquals($newValidToInternal,$ruleResult['valid_to']);
        
        
    }
    
    
    public function testMaxBookCreateScheduleTableQuery()
    {
        $db = $this->getDoctrineConnection();
        
        $db->executeQuery(' CALL bm_rules_maxbook_create_tmp_table()');
        
        $this->assertTrue(true);
        
    }
    
    
    
}
/* End of File */