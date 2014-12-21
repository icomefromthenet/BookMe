<?php
namespace IComeFromTheNet\BookMe\Tests;

use IComeFromTheNet\BookMe\BookMeService;
use IComeFromTheNet\BookMe\Tests\BasicTest;
use Doctrine\DBAL\DBALException;

class RulesAdhocPackageTest extends BasicTest
{
    
    
    public function testDurationTestFunction()
    {
        $db = $this->getDoctrineConnection();
        
        
        $result = (boolean) $db->fetchColumn('SELECT bm_rules_valid_duration(?) as r' ,array(60),0);
        $resultB = (boolean) $db->fetchColumn('SELECT bm_rules_valid_duration(?) as r' ,array(1),0);
        $resultC = (boolean) $db->fetchColumn('SELECT bm_rules_valid_duration(?) as r' ,array(-1),0);
        $resultD = (boolean) $db->fetchColumn('SELECT bm_rules_valid_duration(?) as r' ,array((60*24*366)),0);
        $resultE = (boolean) $db->fetchColumn('SELECT bm_rules_valid_duration(?) as r' ,array((PHP_INT_MAX)),0);
        $resultF = (boolean) $db->fetchColumn('SELECT bm_rules_valid_duration(?) as r' ,array((0)),0);
        
        $this->assertTrue($result);
        $this->assertTrue($resultB);
        $this->assertfalse($resultC);
        $this->assertTrue($resultD);
        $this->assertFalse($resultE);
        $this->assertTrue($resultF);
        
        
    }
    
    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Validity period is and invalid range
    */
    public function testAdhocFailsOnBadDateRange()
    {
        $db = $this->getDoctrineConnection();
        
        $ruleName = 'adhoc1';
        $ruleType = 'inclusion';
        $validFrom = $db->fetchColumn("SELECT CAST(NOW() AS DATE)",array(),0);
        $validTo   = $db->fetchColumn("SELECT CAST((NOW() - INTERVAL 1 DAY) AS DATE)",array(),0);
        $ruleDuration = 5;
        $newRuleID = null;
        
        $db->executeQuery('CALL bm_rules_adhoc_add_rule(?,?,?,?,?,@newRuleID)',array($ruleName,$ruleType,$validFrom,$validTo,$ruleDuration));   
    }
    
    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Valid from date must be gte NOW
    */
    public function testAdhocFailsOnWhenValidToLessThanNow()
    {
        $db = $this->getDoctrineConnection();
        
        $ruleName = 'adhoc1';
        $ruleType = 'inclusion';
        $validFrom = $db->fetchColumn("SELECT CAST((NOW() - INTERVAL 1 DAY) AS DATE)",array(),0);
        $validTo   = $db->fetchColumn("SELECT CAST(NOW() AS DATE)",array(),0);
        $ruleDuration = 5;
        $newRuleID = null;
        
        $db->executeQuery('CALL bm_rules_adhoc_add_rule(?,?,?,?,?,@newRuleID)',array($ruleName,$ruleType,$validFrom,$validTo,$ruleDuration));   
    }
    
    /**
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Given ruleType is invalid
    */
    public function testAdhocFailsOnBadRuleType()
    {
        $db = $this->getDoctrineConnection();
        
        $ruleName = 'adhoc1';
        $ruleType = 'inclusionnnn';
        $validFrom = $db->fetchColumn("SELECT CAST(NOW() AS DATE)",array(),0);
        $validTo   = $db->fetchColumn("SELECT CAST((NOW()  + INTERVAL 1 DAY) AS DATE)",array(),0);
        $ruleDuration = 5;
        $newRuleID = null;
        
        $db->executeQuery('CALL bm_rules_adhoc_add_rule(?,?,?,?,?,@newRuleID)',array($ruleName,$ruleType,$validFrom,$validTo,$ruleDuration));   
    }


    public function testNewAdhocRule()
    {
        $db = $this->getDoctrineConnection();
        
        $ruleName = 'adhoc1';
        $ruleType = 'inclusion';
        $validFrom = $db->fetchColumn("SELECT CAST(NOW() AS DATE)",array(),0);
        $validTo   = $db->fetchColumn("SELECT DATE_FORMAT(NOW() ,'%Y-12-31')",array(),0);
        $ruleDuration = 5;
        $newRuleID = null;
        
        
        try { 
             $db->exec('START TRANSACTION');								
                
            # execute the rule
            $db->executeQuery('CALL bm_rules_adhoc_add_rule(?,?,?,?,?,@newRuleID)',array($ruleName,$ruleType,$validFrom,$validTo,$ruleDuration));   
            $newRuleID = $db->fetchColumn('SELECT @newRuleID',array(),0);
            $this->assertNotEmpty($newRuleID);
            
            
            $commonTable = array(
                'rule_id' => $newRuleID
                ,'rule_name' => $ruleName
                ,"rule_type"  => $ruleType
                ,"rule_repeat" => 'adhoc'
                ,"valid_from" => $validFrom
                ,"valid_to" => $validTo
                ,"rule_duration" => $ruleDuration
            );
            
            # test that rule data has been insert into common table
            $ruleSTH = $db->executeQuery('SELECT * FROM `rules` where `rule_id` = ?',array($newRuleID));
        
            $ruleResult = $ruleSTH->fetch();
            
            
            if(empty($ruleResult)) {
                $this->assertFalse(true,'The new rule not found');
            }
            
            foreach($ruleResult as $key => $result) {
                $this->assertEquals($commonTable[$key],$result,'column '.$key.' is wrong');
            }
            
            
            # test that data been insert into concrete table
            
            $concreteTable = $commonTable;
            
            $ruleSTH = $db->executeQuery('SELECT * FROM `rules_adhoc` where `rule_id` = ?',array($newRuleID));
        
            $ruleResult = $ruleSTH->fetch();
            
            
            if(empty($ruleResult)) {
                $this->assertFalse(true,'The new rule not found');
            }
            
            foreach($ruleResult as $key => $result) {
               $this->assertEquals($concreteTable[$key],$result,'column '.$key.' is wrong');
            }
            
            
            
            # test that audit table been updated with correct values from concrent table
            
            $auditTable                 = $commonTable;
            $auditTable['action']       = 'I';
            $auditTable['change_time']  = $db->fetchColumn('SELECT CAST(NOW() AS DATETIME)',array(),0);
            $auditTable['changed_by']   = $db->fetchColumn('SELECT USER()',array(),0);
            
            $ruleSTH = $db->executeQuery('SELECT * FROM `audit_rules_adhoc` where `rule_id` = ?',array($newRuleID));
        
            $ruleResult = $ruleSTH->fetch();
            
            
            if(empty($ruleResult)) {
                $this->assertFalse(true,'The new rule not found');
            }
            
            foreach($ruleResult as $key => $result) {
                if($key !== 'change_seq') {
                    $this->assertEquals($auditTable[$key],$result,'column '.$key.' is wrong');
                }
            }
            
            $db->exec('COMMIT');
            
        
       } catch(\Doctrine\DBAL\DBALException $e) {
            $db->exec('ROLLBACK');
            $this->assertFalse(true,$e->getMessage());
        }
        
        
        
        return $newRuleID;
        
    }
    
    /**
     * @depends testNewAdhocRule
     */
    public function testAddSlotsToRule($newRuleID)
    {
         $db = $this->getDoctrineConnection();
         
         // Step 1 call method verify returned number
         
         $openingslotID = 1;  // since this repeat rule add a range not included in original rule if that value is changed this range might need to change too.
         $closingslotID = 500;  //Give 500 records
         
         $rowsAffected  = 0;
         
        $db->executeQuery('CALL bm_rules_slots_add(?,?,?,@myRowsAffected)',array($newRuleID,$openingslotID,$closingslotID),array());
        $rowsAffected = $db->fetchColumn('SELECT @myRowsAffected',array(),0);
        
        # as where using periods in our rules this should be 1 row inserted with length of 1-500
        $this->assertEquals(1,$rowsAffected);
        
        // Step 2 verify log was recorded and open/closing slot set correctly
        
        $ruleOperationSTH = $db->executeQuery('select * 
                                              from rule_slots_operations 
                                              where `rule_id` = ? and `operation` = ? 
                                              order by change_seq DESC
                                              limit 1',array($newRuleID,'addition'));
                                              
        $auditResult = $ruleOperationSTH ->fetch();
        
        $this->assertNotEmpty($auditResult,'No rule Audit Record Found for slot addition');                                      
        
        $this->assertEquals($auditResult['opening_slot_id'],$openingslotID);
        $this->assertEquals($auditResult['closing_slot_id'],$closingslotID);
        
        
        return $newRuleID;
    }
    
    /**
     * @depends testNewAdhocRule
     */
    public function testAddSlotsFailsOnDuplicate($newRuleID)
    {
         $db = $this->getDoctrineConnection();
         
        
        $openingslotID = 1;  // since this repeat rule add a range not included in original rule if that value is changed this range might need to change too.
        $closingslotID = 500;  //Give 500 records
        
        try { 
            $db->exec('START TRANSACTION');
            $db->exec('TRUNCATE rule_slots');
            
            $db->executeQuery('CALL bm_rules_slots_add(?,?,?,@myRowsAffected)',array($newRuleID,$openingslotID,$closingslotID),array());
            $db->executeQuery('CALL bm_rules_slots_add(?,?,?,@myRowsAffected)',array($newRuleID,$openingslotID,$closingslotID),array());
            
            $db->exec('ROLLBACK');
            $this->assertTrue(false,'faild to error out on sequence duplicate');
        
        } catch(\Doctrine\DBAL\DBALException $e) {
            $this->assertTrue(true);
            $db->exec('ROLLBACK');
        }
        
        return $newRuleID;
    }
    
     /**
     * @depends testAddSlotsToRule
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Cannot add or update a child row: a foreign key constraint fails (`bookme`.`rule_slots_operations`, CONSTRAINT `fk_slot_op_slots_a`
     */
    public function testRemoveSlotsFailesBadOpenSlot($newRuleID)
    {
        $db = $this->getDoctrineConnection();
         
        // Step 1 call method verify returned number
         
        $openingslotID = -1;  
        $closingslotID = 500;  
        $rowsAffected  = 0;
         
        $db->executeQuery('CALL bm_rules_slots_remove(?,?,?,@myRowsAffected)',array($newRuleID,$openingslotID,$closingslotID),array());
        $rowsAffected = $db->fetchColumn('SELECT @myRowsAffected',array(),0);
        
        
    }
    
     /**
     * @depends testAddSlotsToRule
     * @expectedException \Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Cannot add or update a child row: a foreign key constraint fails (`bookme`.`rule_slots_operations`, CONSTRAINT `fk_slot_op_slots_b`
     */
    public function testRemoveSlotsFailesBadClosingSlot($newRuleID)
    {
        $db = $this->getDoctrineConnection();
         
        // Step 1 call method verify returned number
         
        $openingslotID = 1;  
        $closingslotID = -500;  
        $rowsAffected  = 0;
         
        $db->executeQuery('CALL bm_rules_slots_remove(?,?,?,@myRowsAffected)',array($newRuleID,$openingslotID,$closingslotID),array());
        $rowsAffected = $db->fetchColumn('SELECT @myRowsAffected',array(),0);
        
    }
    
    /**
     * @depends testAddSlotsToRule
     */
    public function testRemoveSlotsWhereEqual($newRuleID)
    {
        // in this breakdown where testing the remove a period that are equal.
        
        $db = $this->getDoctrineConnection();
         
        // Step 1 call method verify 1 period removed,
         
        $openingslotID = 1;  // since this repeat rule add a range not included in original rule if that value is changed this range might need to change too.
        $closingslotID = 500;  // The method uses a inclusive between which expression (min <= expr AND expr <= max) give 201 records
        $rowsAffected  = 0;
        
        // truncate and add the expected range
        $db->executeQuery('TRUNCATE rule_slots');
        $db->executeQuery('CALL bm_rules_slots_add(?,?,?,@myRowsAffected)',array($newRuleID,$openingslotID,$closingslotID),array());
         
        // remove the period 
        $db->executeQuery('CALL bm_rules_slots_remove(?,?,?,@myRowsAffected)',array($newRuleID,$openingslotID,$closingslotID),array());
        
        // Step 1 verify 1 procedure told us its remove the period.    
        $rowsAffected = $db->fetchColumn('SELECT @myRowsAffected',array(),0);
        $this->assertEquals(1,$rowsAffected);
        
        // Step 2 verify the period has actually been removed
        $this->assertEmpty($db->fetchColumn('SELECT 1 FROM rule_slots 
                                            WHERE open_slot_id = ? 
                                            AND close_slot_id = ? 
                                            AND rule_id = ?'
                            ,array($openingslotID,$closingslotID,$newRuleID),0));
        
        
        // Step 3 verify log was recorded and open/closing slot set correctly
        $ruleOperationSTH = $db->executeQuery('select * 
                                              from rule_slots_operations 
                                              where `rule_id` = ? and `operation` = ? 
                                              order by change_seq DESC
                                              limit 1',array($newRuleID,'subtraction'));
                                              
        $auditResult = $ruleOperationSTH ->fetch();
        
        $this->assertNotEmpty($auditResult,'No rule Audit Record Found for slot addition');                                      
        
        $this->assertEquals($auditResult['opening_slot_id'],$openingslotID);
        $this->assertEquals($auditResult['closing_slot_id'],$closingslotID);
        
        return $newRuleID;
        
    }
    
    /**
     * @depends testRemoveSlotsWhereEqual
     */
    public function testRemoveSlotsWhereDuring($newRuleID)
    {
        // in this breakdown where testing the remove periods that occur during the delete interval
        
        $db = $this->getDoctrineConnection();
         
        // Step 1 call method verify 1 period slot removed,
         
        $openingslotID = 100;  // since this repeat rule add a range not included in original rule if that value is changed this range might need to change too.
        $closingslotID = 500;  // The method uses a inclusive between which expression (min <= expr AND expr <= max) give 201 records
        $rowsAffected  = 0;
        
        // truncate and add the expected range
        $db->executeQuery('TRUNCATE rule_slots');
        $db->executeQuery('CALL bm_rules_slots_add(?,?,?,@myRowsAffected)',array($newRuleID,$openingslotID,$closingslotID),array());
         
        // remove the period 
        $db->executeQuery('CALL bm_rules_slots_remove(?,?,?,@myRowsAffected)',array($newRuleID,1,500),array());
        
        // Step 1 verify 1 procedure told us its remove the period.    
        $rowsAffected = $db->fetchColumn('SELECT @myRowsAffected',array(),0);
        $this->assertEquals(1,$rowsAffected);
        
        // Step 2 verify the period has actually been removed
        $this->assertEmpty($db->fetchColumn('SELECT 1 FROM rule_slots 
                                            WHERE open_slot_id = ? 
                                            AND close_slot_id = ? 
                                            AND rule_id = ?'
                            ,array($openingslotID,$closingslotID,$newRuleID),0));
        
        
        return $newRuleID;
    }


    /**
     * @depends testRemoveSlotsWhereEqual
     */
    public function testRemoveSlotsWhereSplitOverlap($newRuleID)
    {
        // in this breakdown where testing the remove periods and slot a slot where delete occured
        
        $db = $this->getDoctrineConnection();
         
        // Step 1 call method verify 1 period slot removed,
         
        $openingslotID = 1;  // since this repeat rule add a range not included in original rule if that value is changed this range might need to change too.
        $closingslotID = 500;  // The method uses a inclusive between which expression (min <= expr AND expr <= max) give 201 records
        $rowsAffected  = 0;
        
        // truncate and add the expected range
        $db->executeQuery('TRUNCATE rule_slots');
        $db->executeQuery('CALL bm_rules_slots_add(?,?,?,@myRowsAffected)',array($newRuleID,$openingslotID,$closingslotID),array());
         
        // remove the period 
        $db->executeQuery('CALL bm_rules_slots_remove(?,?,?,@myRowsAffected)',array($newRuleID,250,260),array());
        
        // Step 1 verify 1 procedure told us that slot been split a new period insert and old updated    
        $rowsAffected = $db->fetchColumn('SELECT @myRowsAffected',array(),0);
        $this->assertEquals(2,$rowsAffected);
        
        // Step 2 verify the period has actually been removed
        $this->assertEmpty($db->fetchColumn('SELECT 1 FROM rule_slots 
                                            WHERE open_slot_id = ? 
                                            AND close_slot_id = ? 
                                            AND rule_id = ?'
                            ,array($openingslotID,$closingslotID,$newRuleID),0));
        
        // step 3 assert dates are valid
        $resultSTH = $db->executeQuery('SELECT * FROM rule_slots WHERE rule_id = ? ORDER BY rule_slot_id ASC',array($newRuleID));
        
        // Assert the original period closing slot set to delete period opending slot
        $period = $resultSTH->fetch();
        
        $this->assertEquals($period['open_slot_id'],1);
        $this->assertEquals($period['close_slot_id'],250);
        
        
        // Assert the new period has opening slot of the delete periods closing slot. 
        $period = $resultSTH->fetch();
        $this->assertEquals($period['open_slot_id'],260);
        $this->assertEquals($period['close_slot_id'],500);
        
        
        return $newRuleID;
    }
    
    /**
     * @depends testRemoveSlotsWhereEqual
     */
    public function testRemoveSlotsWhereShortenedHead($newRuleID)
    {
        // in this breakdown where testing where shorten periods that end in the deleteion period
        
        $db = $this->getDoctrineConnection();
         
        // Step 1 call method verify 1 period slot removed,
         
        $openingslotID = 1;  // since this repeat rule add a range not included in original rule if that value is changed this range might need to change too.
        $closingslotID = 500;  // The method uses a inclusive between which expression (min <= expr AND expr <= max) give 201 records
        $rowsAffected  = 0;
        
        // truncate and add the expected range
        $db->executeQuery('TRUNCATE rule_slots');
        $db->executeQuery('CALL bm_rules_slots_add(?,?,?,@myRowsAffected)',array($newRuleID,$openingslotID,$closingslotID),array());
         
        // remove the period 
        $db->executeQuery('CALL bm_rules_slots_remove(?,?,?,@myRowsAffected)',array($newRuleID,250,500),array());
        
        // Step 1 verify 1 procedure told us that slot been split a new period insert and old updated    
        $rowsAffected = $db->fetchColumn('SELECT @myRowsAffected',array(),0);
        $this->assertEquals(1,$rowsAffected);
        
        // Step 2 verify the period has actually been removed
        $this->assertEmpty($db->fetchColumn('SELECT 1 FROM rule_slots 
                                            WHERE open_slot_id = ? 
                                            AND close_slot_id = ? 
                                            AND rule_id = ?'
                            ,array($openingslotID,$closingslotID,$newRuleID),0));
        
        // step 3 assert dates are valid
        $resultSTH = $db->executeQuery('SELECT * FROM rule_slots WHERE rule_id = ? ORDER BY rule_slot_id ASC',array($newRuleID));
        $period = $resultSTH->fetch();
        $this->assertEquals($period['open_slot_id'],1);
        $this->assertEquals($period['close_slot_id'],250);
        
        return $newRuleID;
    }
    
     /**
     * @depends testRemoveSlotsWhereEqual
     */
    public function testRemoveSlotsWhereShortenedTail($newRuleID)
    {
        // in this breakdown where testing where shorten periods that begin in deleteion period
        
        $db = $this->getDoctrineConnection();
         
        // Step 1 call method verify 1 period slot removed,
         
        $openingslotID = 1;  // since this repeat rule add a range not included in original rule if that value is changed this range might need to change too.
        $closingslotID = 500;  // The method uses a inclusive between which expression (min <= expr AND expr <= max) give 201 records
        $rowsAffected  = 0;
        
        // truncate and add the expected range
        $db->executeQuery('TRUNCATE rule_slots');
        $db->executeQuery('CALL bm_rules_slots_add(?,?,?,@myRowsAffected)',array($newRuleID,$openingslotID,$closingslotID),array());
         
        // remove the period 
        $db->executeQuery('CALL bm_rules_slots_remove(?,?,?,@myRowsAffected)',array($newRuleID,1,250),array());
        
        // Step 1 verify 1 procedure told us that slot been split a new period insert and old updated    
        $rowsAffected = $db->fetchColumn('SELECT @myRowsAffected',array(),0);
        $this->assertEquals(1,$rowsAffected);
        
        // Step 2 verify the period has actually been removed
        $this->assertEmpty($db->fetchColumn('SELECT 1 FROM rule_slots 
                                            WHERE open_slot_id = ? 
                                            AND close_slot_id = ? 
                                            AND rule_id = ?'
                            ,array($openingslotID,$closingslotID,$newRuleID),0));
        
        // step 3 assert dates are valid
        $resultSTH = $db->executeQuery('SELECT * FROM rule_slots WHERE rule_id = ? ORDER BY rule_slot_id ASC',array($newRuleID));
        $period = $resultSTH->fetch();
        $this->assertEquals($period['open_slot_id'],250);
        $this->assertEquals($period['close_slot_id'],500);
        
        return $newRuleID;
    }
    
    /**
     * @depends testNewAdhocRule
     */
    public function testNewRepeatRuleCleanupSuccessfully($newRuleID)
    {
        
        // Step 1 . Test cleanup method
        $rowsAffected = null;
        $db = $this->getDoctrineConnection();

        $db->executeQuery('CALL bm_rules_slots_cleanup(?,@changedRows)',array($newRuleID));
        
        $this->assertGreaterThan(0,$db->fetchColumn('SELECT @changedRows',array(),0));
        
        // Step 4. Test the slot operation log was updated
        $ruleOperationSTH = $db->executeQuery('select * from rule_slots_operations where `rule_id` = ? and `operation` = ?',array($newRuleID,'clean'));
        
        $ruleOperation = $ruleOperationSTH->fetch();
        
        $this->assertNotEmpty($ruleOperation);
        
    }
    
     /**
     * @depends testNewAdhocRule
     */
    public function testNewRepeatRuleDepreciateAdhocRule($newRuleID)
    {
        
        $db = $this->getDoctrineConnection();
        
        $dte = $db->fetchColumn('SELECT CAST((NOW() + INTERVAL 5 WEEK) as DATE)',array(),0);

        $db->executeQuery('CALL bm_rules_depreciate_rule(?,?)',array($newRuleID,$dte));
        
        # verify in common table
        $ruleSTH = $db->executeQuery('SELECT * FROM `rules` where `rule_id` = ?',array($newRuleID));
        $ruleResult = $ruleSTH->fetch();
        $this->assertEquals($dte,$ruleResult['valid_to']);
        
        
        # verify in the concrent table the new date been set        
        $ruleSTH = $db->executeQuery('SELECT * FROM `rules_adhoc` where `rule_id` = ?',array($newRuleID));
        $ruleResult = $ruleSTH->fetch();
        $this->assertEquals($dte,$ruleResult['valid_to']);
        
        return $newRuleID;
    }
    
    
     /**
     * @depends testNewRepeatRuleDepreciateAdhocRule
     */
    public function testRuleDeleteAudiTrigger($newRuleID)
    {
        
        // Step 1 . Test cleanup method
        $rowsAffected = null;
        $db = $this->getDoctrineConnection();
        
        $db->executeQuery('TRUNCATE rule_slots');
        $db->executeQuery('DELETE FROM `rules_adhoc` WHERE `rule_id` = ?',array($newRuleID));
        $db->executeQuery('DELETE FROM `rules` WHERE `rule_id` = ?',array($newRuleID));
        
        
        // Step 3. Test trigger work, this not expected operation it should be recorded
        $auditSTH = $db->executeQuery('SELECT * 
                                       FROM audit_rules_adhoc 
                                       WHERE rule_id = ? 
                                       AND action = ?',array($newRuleID,'D'));
        
       
        $auditResult = $auditSTH->fetch();
        
        $this->assertNotEmpty($auditResult,'No rule Audit Record Found for delete');
    }
    

}
/* End of Class */