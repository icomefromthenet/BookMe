<?php
namespace IComeFromTheNet\BookMe\Tests;

use IComeFromTheNet\BookMe\BookMeService;
use IComeFromTheNet\BookMe\Tests\BasicTest;
use Doctrine\DBAL\DBALException;

class RulesRepeatPackageTest extends BasicTest
{
    
    
    public function testMinuteParserGoodFormats()
    {
        $db = $this->getDoctrineConnection();
        
        # Test if valid formats create expected result set in 
        # the result tmp table;
        
        # Test for the default '*'
        $db->executeQuery("CALL bm_rules_repeat_parse('*','minute')");
        
        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals("0",$result['range_open']);
        $this->assertEquals("60",$result['range_closed']);
        $this->assertEquals("minute",$result['value_type']);
        $this->assertEquals(1,$result['mod_value']);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ## e.g scalar value range 1 to 59
        $db->executeQuery("CALL bm_rules_repeat_parse('56','minute')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals("56",$result['range_open']);
        $this->assertEquals("57",$result['range_closed']);
        $this->assertEquals("minute",$result['value_type']);
        $this->assertEquals(1,$result['mod_value']);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_repeat_parse('34-59','minute')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals("34",$result['range_open']);
        $this->assertEquals("60",$result['range_closed']);
        $this->assertEquals("minute",$result['value_type']);
        $this->assertEquals(1,$result['mod_value']);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_repeat_parse('*/20','minute')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals("0",$result['range_open']);
        $this->assertEquals("60",$result['range_closed']);
        $this->assertEquals("minute",$result['value_type']);
        $this->assertEquals(20,$result['mod_value']);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##/## e.g 6/3 short for 6-59/3
        $db->executeQuery("CALL bm_rules_repeat_parse('6/3','minute')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals("6",$result['range_open']);
        $this->assertEquals("60",$result['range_closed']);
        $this->assertEquals("minute",$result['value_type']);
        $this->assertEquals(3,$result['mod_value']);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
     
        
    }
    

    
    public function testMinuteParseFailures()
    {
        $db = $this->getDoctrineConnection();
        $patterns = array(
            'one'    => '60'
            ,'two'   => 'a'
            ,'three' => '-1'
            ,'four'  =>'60-59'
            ,'five' => '6-60'
            ,'six' => '**/20'
            ,'seven' => '60/3'
            ,'eight'   => '6/*'
            ,'nine'  => '6-60/3'
            ,'ten' => '6-*/3'
            ,'eleven' => '-1-59/3'
            
            
        );
        
        
        foreach($patterns as $key => $pattern) {
        
            try {
                $db->executeQuery("CALL bm_rules_repeat_parse('?','minute')",array($pattern));
                
                $this->assertTrue(false,'Test for minute parse fails has failed to cause an exception');
            }
            catch(DBALException $e) {
                $this->assertContains('1644 not support cron format',$e->getMessage());
            }
            
        }
    
        
    }
    
    
     public function testHourValidCombinations() 
    {
        $db = $this->getDoctrineConnection();
        
        # Test if valid formats create expected result set in 
        # the result tmp table;
        
        # Test for the default '*'
        $db->executeQuery("CALL bm_rules_repeat_parse('*','hour')");
        
        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals("0",$result['range_open']);
        $this->assertEquals("24",$result['range_closed']);
        $this->assertEquals("hour",$result['value_type']);
        $this->assertEquals(1,$result['mod_value']);    
        
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ## e.g scalar value range 0 to 23
        $db->executeQuery("CALL bm_rules_repeat_parse('23','hour')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals("23",$result['range_open']);
        $this->assertEquals("24",$result['range_closed']);
        $this->assertEquals("hour",$result['value_type']);
        $this->assertEquals(1,$result['mod_value']);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_repeat_parse('5-9','hour')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals("5",$result['range_open']);
        $this->assertEquals("10",$result['range_closed']);
        $this->assertEquals("hour",$result['value_type']);
        $this->assertEquals(1,$result['mod_value']);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_repeat_parse('*/20','hour')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals("0",$result['range_open']);
        $this->assertEquals("24",$result['range_closed']);
        $this->assertEquals("hour",$result['value_type']);
        $this->assertEquals(20,$result['mod_value']);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##/## e.g 6/3 short for 6-23/3
        $db->executeQuery("CALL bm_rules_repeat_parse('6/3','hour')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals("6",$result['range_open']);
        $this->assertEquals("24",$result['range_closed']);
        $this->assertEquals("hour",$result['value_type']);
        $this->assertEquals(3,$result['mod_value']);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        
        
    }
    
     public function testHourParseFailures()
    {
        $db = $this->getDoctrineConnection();
        $patterns = array(
            'one'    => '24'
            ,'two'   => 'a'
            ,'three' => '-1'
            ,'four'  =>'24-1'
            ,'five' => '4-24'
            ,'six' => '**/20'
            ,'seven' => '30/3'
            ,'eight'   => '6/*'
            ,'nine'  => '6-25/3'
            ,'ten' => '6-*/3'
            ,'eleven' => '-1-23/3'
            
            
        );
        
        
        foreach($patterns as $key => $pattern) {
        
            try {
                $db->executeQuery("CALL bm_rules_repeat_parse('?','hour')",array($pattern));
                
                $this->assertTrue(false,'Test for minute parse fails has failed to cause an exception');
            }
            catch(DBALException $e) {
                $this->assertContains('1644 not support cron format',$e->getMessage());
            }
            
        }
    
        
    }
    
    public function testDayMonthValidCombinations() 
    {
        $db = $this->getDoctrineConnection();
        
        # Test if valid formats create expected result set in 
        # the result tmp table;
        
        # Test for the default '*'
        $db->executeQuery("CALL bm_rules_repeat_parse('*','dayofmonth')");
        
        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals($result['range_open'],"1");
        $this->assertEquals($result['range_closed'],"32");
        $this->assertEquals($result['value_type'],"dayofmonth");
        $this->assertEquals($result['mod_value'],1);    
        
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ## e.g scalar value range 1 to 31
        $db->executeQuery("CALL bm_rules_repeat_parse('1','dayofmonth')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"1");
        $this->assertEquals($result['range_closed'],"2");
        $this->assertEquals($result['value_type'],"dayofmonth");
        $this->assertEquals($result['mod_value'],1);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_repeat_parse('5-31','dayofmonth')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],5);
        $this->assertEquals($result['range_closed'],32);
        $this->assertEquals($result['value_type'],"dayofmonth");
        $this->assertEquals($result['mod_value'],1);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_repeat_parse('*/20','dayofmonth')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals($result['range_open'],1);
        $this->assertEquals($result['range_closed'],32);
        $this->assertEquals($result['value_type'],"dayofmonth");
        $this->assertEquals($result['mod_value'],20);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##/## e.g 6/3 short for 6-23/3
        $db->executeQuery("CALL bm_rules_repeat_parse('6/3','dayofmonth')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],6);
        $this->assertEquals($result['range_closed'],32);
        $this->assertEquals($result['value_type'],"dayofmonth");
        $this->assertEquals($result['mod_value'],3);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
    }
    
     public function testDayMonthParseFailures()
    {
        $db = $this->getDoctrineConnection();
        $patterns = array(
            'one'    => '32'
            ,'two'   => 'a'
            ,'three' => '-4'
            ,'four'  =>'31-1'
            ,'five' => '4-32'
            ,'six' => '**/20'
            ,'seven' => '100/3'
            ,'eight'   => '6/*'
            ,'nine'  => '6-56/3'
            ,'ten' => '6-*/3'
            ,'eleven' => '-1-23/3'
            
            
        );
        
        
        foreach($patterns as $key => $pattern) {
        
            try {
                $db->executeQuery("CALL bm_rules_repeat_parse('?','dayofmonth')",array($pattern));
                
                $this->assertTrue(false,'Test for minute parse fails has failed to cause an exception');
            }
            catch(DBALException $e) {
                $this->assertContains('1644 not support cron format',$e->getMessage());
            }
            
        }
    
        
    }
    
    public function testDayWeekValidCombinations() 
    {
        $db = $this->getDoctrineConnection();
        
        # Test if valid formats create expected result set in 
        # the result tmp table;
        
        # Test for the default '*'
        $db->executeQuery("CALL bm_rules_repeat_parse('*','dayofweek')");
        
        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals($result['range_open'],0);
        $this->assertEquals($result['range_closed'],7);
        $this->assertEquals($result['value_type'],"dayofweek");
        $this->assertEquals($result['mod_value'],1);    
        
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ## e.g scalar value range 0 to 6
        $db->executeQuery("CALL bm_rules_repeat_parse('0','dayofweek')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],0);
        $this->assertEquals($result['range_closed'],1);
        $this->assertEquals($result['value_type'],"dayofweek");
        $this->assertEquals($result['mod_value'],1);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_repeat_parse('3-6','dayofweek')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],3);
        $this->assertEquals($result['range_closed'],7);
        $this->assertEquals($result['value_type'],"dayofweek");
        $this->assertEquals($result['mod_value'],1);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_repeat_parse('*/20','dayofweek')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals($result['range_open'],0);
        $this->assertEquals($result['range_closed'],7);
        $this->assertEquals($result['value_type'],"dayofweek");
        $this->assertEquals($result['mod_value'],20);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##/## e.g 4/3 short for 4-6/3
        $db->executeQuery("CALL bm_rules_repeat_parse('4/3','dayofweek')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],4);
        $this->assertEquals($result['range_closed'],7);
        $this->assertEquals($result['value_type'],"dayofweek");
        $this->assertEquals($result['mod_value'],3);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        
        
    }
    
    
    
    public function testDayWeekParseFailures()
    {
        $db = $this->getDoctrineConnection();
        $patterns = array(
            'one'    => '7'
            ,'two'   => 'a'
            ,'three' => '-4'
            ,'four'  =>'31-1'
            ,'five' => '4-32'
            ,'six' => '**/20'
            ,'seven' => '100/3'
            ,'eight'   => '6/*'
            ,'nine'  => '6-56/3'
            ,'ten' => '6-*/3'
            ,'eleven' => '-1-23/3'
            
            
        );
        
        
        foreach($patterns as $key => $pattern) {
        
            try {
                $db->executeQuery("CALL bm_rules_repeat_parse('?','dayofweek')",array($pattern));
                
                $this->assertTrue(false,'Test for minute parse fails has failed to cause an exception');
            }
            catch(DBALException $e) {
                $this->assertContains('1644 not support cron format',$e->getMessage());
            }
            
        }
    
        
    }
    
    public function testMonthValidCombinations() 
    {
        $db = $this->getDoctrineConnection();
        
        # Test if valid formats create expected result set in 
        # the result tmp table;
        
        # Test for the default '*'
        $db->executeQuery("CALL bm_rules_repeat_parse('*','month')");
        
        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals($result['range_open'],1);
        $this->assertEquals($result['range_closed'],13);
        $this->assertEquals($result['value_type'],"month");
        $this->assertEquals($result['mod_value'],1);    
        
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ## e.g scalar value range 1 to 12
        $db->executeQuery("CALL bm_rules_repeat_parse('1','month')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],1);
        $this->assertEquals($result['range_closed'],2);
        $this->assertEquals($result['value_type'],"month");
        $this->assertEquals($result['mod_value'],1);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_repeat_parse('3-6','month')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],3);
        $this->assertEquals($result['range_closed'],7);
        $this->assertEquals($result['value_type'],"month");
        $this->assertEquals($result['mod_value'],1);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_repeat_parse('*/20','month')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals($result['range_open'],1);
        $this->assertEquals($result['range_closed'],13);
        $this->assertEquals($result['value_type'],"month");
        $this->assertEquals($result['mod_value'],20);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##/## e.g 4/3 short for 4-12/3
        $db->executeQuery("CALL bm_rules_repeat_parse('4/3','month')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],4);
        $this->assertEquals($result['range_closed'],13);
        $this->assertEquals($result['value_type'],"month");
        $this->assertEquals($result['mod_value'],3);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        
        
    }
    
    public function testMonthParseFailures()
    {
        $db = $this->getDoctrineConnection();
        $patterns = array(
            'one'    => '7'
            ,'two'   => 'a'
            ,'three' => '-4'
            ,'four'  =>'31-1'
            ,'five' => '4-32'
            ,'six' => '**/20'
            ,'seven' => '100/3'
            ,'eight'   => '6/*'
            ,'nine'  => '6-56/3'
            ,'ten' => '6-*/3'
            ,'eleven' => '-1-23/3'
            
            
        );
        
        
        foreach($patterns as $key => $pattern) {
        
            try {
                $db->executeQuery("CALL bm_rules_repeat_parse('?','month')",array($pattern));
                
                $this->assertTrue(false,'Test for minute parse fails has failed to cause an exception');
            }
            catch(DBALException $e) {
                $this->assertContains('1644 not support cron format',$e->getMessage());
            }
            
        }
    
         
    }
    
    public function testAddRepatRuleFailesWithBadType()
    {
        $db = $this->getDoctrineConnection();
        
        $ruleName           = 'ruleA';
        $ruleType           = 'inclusionnnnnnn';
        $ruleMinute         = 0;
        $ruleHour           = 0;
        $ruleDayofweek      = 0;
        $ruleDayofmonth     = 1;
        $ruleMonth          = 1;
		
		$scheduleGroupID    = 2;
		$memberID           = NULL;
		$ruleDuration       = 60;
	    $ruleStartFrom      = $db->fetchColumn("SELECT CAST(NOW() AS DATE)",array(),0);
		$ruleEndAt          = $db->fetchColumn("SELECT CAST((NOW() + INTERVAL 1 DAY) AS DATE)",array(),0);
		
										
       try { 
             $db->exec('START TRANSACTION');								
            
           
            $db->executeQuery("CALL bm_rules_repeat_add_rule(?,?,?,?,?,?,?,?,?,?,@newRuleID)"
                ,array($ruleName,$ruleType,$ruleMinute,$ruleHour,$ruleDayofweek,$ruleDayofmonth,$ruleMonth,$ruleStartFrom,$ruleEndAt,$ruleDuration)
            );
            
            $db->exec('ROLLBACK');
            
            $this->assertFalse(true);
        
       } catch(\Doctrine\DBAL\DBALException $e) {
            $db->exec('ROLLBACK');
            $this->assertContains('Given ruleType is invalid must be inclusion or exclusion or priority',$e->getMessage());
        }
        
    }
    
    
    public function testAddRepatRuleFailesWithBadDuration()
    {
        $db = $this->getDoctrineConnection();
        
        $ruleName           = 'ruleA';
        $ruleType           = 'inclusion';
        $ruleMinute         = 0;
        $ruleHour           = 0;
        $ruleDayofweek      = 0;
        $ruleDayofmonth     = 1;
        $ruleMonth          = 1;
		$ruleDuration       = -1;
		$ruleStartFrom      = $db->fetchColumn("SELECT CAST(NOW() AS DATE)",array(),0);
		$ruleEndAt          = $db->fetchColumn("SELECT CAST((NOW() + INTERVAL 1 DAY) AS DATE)",array(),0);
		
										
       try { 
             $db->exec('START TRANSACTION');								
            
           
            $db->executeQuery("CALL bm_rules_repeat_add_rule(?,?,?,?,?,?,?,?,?,?,@newRuleID)"
                ,array($ruleName,$ruleType,$ruleMinute,$ruleHour,$ruleDayofweek,$ruleDayofmonth,$ruleMonth,$ruleStartFrom,$ruleEndAt,$ruleDuration)
            );
            
            $db->exec('ROLLBACK');
            
            $this->assertFalse(true);
        
       } catch(\Doctrine\DBAL\DBALException $e) {
            $db->exec('ROLLBACK');
            $this->assertContains('The rule duration is not in valid range between 1 minute and 1 year',$e->getMessage());
        }
        
    }
    
    
    public function testAddGoodRepeatRule()
    {
        $db = $this->getDoctrineConnection();
        
        $ruleName           = 'ruleA';
        $ruleType           = 'inclusion';
        $ruleMinute         = 0;
        $ruleHour           = 0;
        $ruleDayofweek      = '*';
        $ruleDayofmonth     = 1;
        $ruleMonth          = '*';
		$ruleDuration       = 60;
		$ruleStartFrom      = $db->fetchColumn("SELECT CAST(NOW() AS DATE)",array(),0);
		$ruleEndAt          = $db->fetchColumn("SELECT CAST((NOW() + INTERVAL 1 YEAR) AS DATE)",array(),0);
	
	
		$changedBy = $db->fetchColumn('SELECT USER()',array(),0);
        
        
        $db->executeQuery("CALL bm_rules_repeat_add_rule(?,?,?,?,?,?,?,?,?,?,@newRuleID)"
            ,array($ruleName,$ruleType,$ruleMinute,$ruleHour,$ruleDayofweek,$ruleDayofmonth,$ruleMonth,$ruleStartFrom,$ruleEndAt,$ruleDuration)
        );
        
        // ensure that we got a good id back for the new rule
        $newRuleID = $db->fetchColumn('SELECT @newRuleID',array(),0);
        
        // rule exists in rule table
        $ruleSTH = $db->executeQuery('SELECT * FROM rules where rule_id = ?',array($newRuleID));
        
        $ruleResult = $ruleSTH->fetch();
        
        
        
        if(empty($ruleResult)) {
            $this->assertFalse(true,'The new rule not found');
        }
        
        
        $columnMap = array(
             'rule_id'          => $newRuleID
            ,'rule_name'        => $ruleName
            ,'rule_type'        => $ruleType  
            ,'rule_repeat'      => 'repeat'
            ,'repeat_minute'    => $ruleMinute
            ,'repeat_hour'      => $ruleHour  
            ,'repeat_dayofweek' => $ruleDayofweek
            ,'repeat_dayofmonth'=> $ruleDayofmonth
            ,'repeat_month'     => $ruleMonth     
    		,'opening_slot_id'  => null
    		,'closing_slot_id'  => null
    		,'valid_from'       => $ruleStartFrom
    		,'valid_to'         => $ruleEndAt
    		,'rule_duration'    => $ruleDuration
    		,'start_from'       => $ruleStartFrom
    		,'end_at'           => $ruleEndAt 
    		
        );
        
        
        foreach($ruleResult as $key => $result) {
            $this->assertEquals($columnMap[$key],$result,'column '.$key.' is wrong');
            
        }
        
        // check if record is in concrete table
        // rule exists in rule table
        $ruleSTH = $db->executeQuery('SELECT * FROM rules_repeat where rule_id = ?',array($newRuleID));
        
        $ruleResult = $ruleSTH->fetch();
        
        
        if(empty($ruleResult)) {
            $this->assertFalse(true,'The new rule not found');
        }
        
        foreach($ruleResult as $key => $result) {
            $this->assertEquals($columnMap[$key],$result,'column '.$key.' is wrong');
            
        }
        
        // Verify if recorded in the audit table
        $auditSTH = $db->executeQuery('SELECT * 
                                       FROM audit_rules_repeat 
                                       WHERE rule_id = ? 
                                       AND action = ?',array($newRuleID,'I'));
        
        $auditResult = $auditSTH->fetch();
        
        if(empty($auditResult)) {
            $this->assertFalse(true,'No Audit Record Found');
        }
        
         $columnMap['change_time']  =  $db->fetchColumn("SELECT NOW()",array(),0);
         $columnMap['rule_id']      = $newRuleID;
         $columnMap['changed_by']   = $changedBy;
         $columnMap['action']       = 'I';
         
        foreach($ruleResult as $key => $result) {
            if($key !== 'change_seq') {
                $this->assertEquals($columnMap[$key],$result);
            }
        }
        
            
        return $newRuleID;
        
    }
    
     /**
     * @depends testAddGoodRepeatRule
     */
    public function testNewRepeatRuleHasCorrectSlots($newRuleID)
    {
        $db = $this->getDoctrineConnection();
        $currentYear = (int) $db->fetchColumn("SELECT EXTRACT(YEAR FROM NOW())",array(),0);

        //is their slots and are they what we expect
        // Rule::'0 0 * 1 * 2014' with duration of 60
		
		// Give us 60 slots at midnight on any day of the week but only in the first day of each month 
		// for every month in 2014 ie 12 months
        
        // Execute the build method and very we had some slots
        $ruleSTH = $db->executeQuery('SELECT * FROM rules_repeat WHERE rule_id =?',array($newRuleID));
        $ruleData  = $ruleSTH->fetch();
    
        $this->assertNotEmpty($ruleData);
        
        $db->executeQuery('CALL bm_rules_repeat_save_slots(?,@numberOfSlots,?,?,?,?,?,?,?,?)'
            ,array($newRuleID
                    ,$ruleData['repeat_minute']
                    ,$ruleData['repeat_hour']
                    ,$ruleData['repeat_dayofweek']
                    ,$ruleData['repeat_dayofmonth']
                    ,$ruleData['repeat_month']
                    ,$ruleData['rule_duration']
                    ,$ruleData['start_from']
                    ,$ruleData['end_at']));
        
        $slotsCount = $db->fetchColumn('SELECT @numberOfSlots',array(),0);
        
        $this->assertNotEmpty($slotsCount);
        
        
        // verify that have required total number of slots
        // above give should repeat 12 times 1st day of each month in 2014 
        $this->assertEquals(12
                            ,$db->fetchColumn('SELECT count(rule_slot_id) 
                                              FROM rule_slots 
                                              WHERE rule_id = ?'
                                              ,array($newRuleID)
                                              ,0)
                                             );
            
                                      
        // Assert that the months are expected value                                     
                                             
        $monthsSTH = $db->executeQuery('SELECT c.m as month
                                       FROM rule_slots rs
                                       JOIN slots s ON s.slot_id = rs.open_slot_id
                                       JOIN calendar c ON c.calendar_date = s.cal_date
                                       WHERE rs.rule_id = ?
                                       GROUP BY c.m
                                       ORDER BY c.m'
					                    ,array((int)$newRuleID),array(\PDO::PARAM_INT));
					                    
        
        $i =1;
        while(($value = $monthsSTH->fetch()) !== null && $i <= 12) {
            $this->assertEquals($i,$value['month']);
            $i++;
        }
        
        
        // make sure all months covered
        $this->assertEquals(12,$i-1);
        
        
        // Assert the month day is same for all slots
        $monthsDayFound   = $db->fetchColumn('SELECT c.d as dte
                                       FROM rule_slots rs
                                       JOIN slots s ON s.slot_id = rs.open_slot_id
                                       JOIN calendar c ON c.calendar_date = s.cal_date
                                       WHERE rs.rule_id = ?
                                       GROUP BY c.d
                                       ORDER BY c.d'
					                   ,array($newRuleID),0);

        $this->assertEquals(1,$monthsDayFound);


        //assert the minute range is between 0-60
        $dayRangeSTH   = $db->executeQuery('SELECT EXTRACT(MINUTE FROM `s`.`slot_open`) as min
                                                    , EXTRACT(MINUTE FROM `s2`.`slot_open` - interval 1 minute) as max
                                        FROM rule_slots rs
                                        JOIN slots s ON s.slot_id = rs.open_slot_id
                                        JOIN slots s2 on s2.slot_id = rs.close_slot_id
                                        JOIN calendar c ON c.calendar_date = s.cal_date
                                        WHERE rs.rule_id = ?'
					                   ,array((int)$newRuleID),array(\PDO::PARAM_INT));
            
            
        while(($value = $dayRangeSTH->fetch())) {
            $this->assertEquals(0,(int)$value['min']);
            $this->assertEquals(59,(int)$value['max']);
        }
        
        
        // assert that all slots from year 2014
        $yearRangeSTH   = $db->executeQuery('SELECT c.y as year
                                       FROM rule_slots rs
                                       JOIN slots s ON s.slot_id = rs.open_slot_id
                                       JOIN calendar c ON c.calendar_date = s.cal_date
                                       WHERE rs.rule_id = ?
                                       GROUP BY c.y
                                       ORDER BY c.y DESC'
					                   ,array((int)$newRuleID),array(\PDO::PARAM_INT));
            
        
        // max rule duration is 1 year and rule requiers only 1st each month   
        $value = $yearRangeSTH->fetch();
        $this->assertGreaterThanOrEqual($currentYear,(int)$value['year']);
        $this->assertLessThanOrEqual($currentYear+1,(int)$value['year']);
        
        
        return $newRuleID;
        
    }
    
    
     /**
     * @depends testNewRepeatRuleHasCorrectSlots
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
        $ruleSTH = $db->executeQuery('SELECT * FROM `rules_repeat` where `rule_id` = ?',array($newRuleID));
        $ruleResult = $ruleSTH->fetch();
        $this->assertEquals($dte,$ruleResult['valid_to']);
        
        return $newRuleID;
    }
  
   
    

}
/* End of Class */