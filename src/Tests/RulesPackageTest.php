<?php
namespace IComeFromTheNet\BookMe\Tests;

use IComeFromTheNet\BookMe\BookMeService;
use Doctrine\DBAL\DBALException;

class RulesPackageTest extends BasicTest
{
    
    
    public function testMinuteParserGoodFormats()
    {
        $db = $this->getDoctrineConnection();
        
        # Test if valid formats create expected result set in 
        # the result tmp table;
        
        # Test for the default '*'
        $db->executeQuery("CALL bm_rules_parse('*','minute')");
        
        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals($result['range_open'],"0");
        $this->assertEquals($result['range_closed'],"59");
        $this->assertEquals($result['value_type'],"minute");
        $this->assertEquals($result['mod_value'],1);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ## e.g scalar value range 1 to 59
        $db->executeQuery("CALL bm_rules_parse('56','minute')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"56");
        $this->assertEquals($result['range_closed'],"56");
        $this->assertEquals($result['value_type'],"minute");
        $this->assertEquals($result['mod_value'],1);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_parse('34-59','minute')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"34");
        $this->assertEquals($result['range_closed'],"59");
        $this->assertEquals($result['value_type'],"minute");
        $this->assertEquals($result['mod_value'],1);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_parse('*/20','minute')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"0");
        $this->assertEquals($result['range_closed'],"59");
        $this->assertEquals($result['value_type'],"minute");
        $this->assertEquals($result['mod_value'],20);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##/## e.g 6/3 short for 6-59/3
        $db->executeQuery("CALL bm_rules_parse('6/3','minute')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"6");
        $this->assertEquals($result['range_closed'],"59");
        $this->assertEquals($result['value_type'],"minute");
        $this->assertEquals($result['mod_value'],3);
        
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
                $db->executeQuery("CALL bm_rules_parse('?','minute')",array($pattern));
                
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
        $db->executeQuery("CALL bm_rules_parse('*','hour')");
        
        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals($result['range_open'],"0");
        $this->assertEquals($result['range_closed'],"23");
        $this->assertEquals($result['value_type'],"hour");
        $this->assertEquals($result['mod_value'],1);    
        
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ## e.g scalar value range 0 to 23
        $db->executeQuery("CALL bm_rules_parse('23','hour')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"23");
        $this->assertEquals($result['range_closed'],"23");
        $this->assertEquals($result['value_type'],"hour");
        $this->assertEquals($result['mod_value'],1);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_parse('5-9','hour')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"5");
        $this->assertEquals($result['range_closed'],"9");
        $this->assertEquals($result['value_type'],"hour");
        $this->assertEquals($result['mod_value'],1);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_parse('*/20','hour')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals($result['range_open'],"0");
        $this->assertEquals($result['range_closed'],"23");
        $this->assertEquals($result['value_type'],"hour");
        $this->assertEquals($result['mod_value'],20);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##/## e.g 6/3 short for 6-23/3
        $db->executeQuery("CALL bm_rules_parse('6/3','hour')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"6");
        $this->assertEquals($result['range_closed'],"23");
        $this->assertEquals($result['value_type'],"hour");
        $this->assertEquals($result['mod_value'],3);
        
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
                $db->executeQuery("CALL bm_rules_parse('?','hour')",array($pattern));
                
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
        $db->executeQuery("CALL bm_rules_parse('*','dayofmonth')");
        
        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals($result['range_open'],"1");
        $this->assertEquals($result['range_closed'],"31");
        $this->assertEquals($result['value_type'],"dayofmonth");
        $this->assertEquals($result['mod_value'],1);    
        
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ## e.g scalar value range 1 to 31
        $db->executeQuery("CALL bm_rules_parse('1','dayofmonth')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"1");
        $this->assertEquals($result['range_closed'],"1");
        $this->assertEquals($result['value_type'],"dayofmonth");
        $this->assertEquals($result['mod_value'],1);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_parse('5-31','dayofmonth')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"5");
        $this->assertEquals($result['range_closed'],31);
        $this->assertEquals($result['value_type'],"dayofmonth");
        $this->assertEquals($result['mod_value'],1);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_parse('*/20','dayofmonth')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals($result['range_open'],"1");
        $this->assertEquals($result['range_closed'],"31");
        $this->assertEquals($result['value_type'],"dayofmonth");
        $this->assertEquals($result['mod_value'],20);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##/## e.g 6/3 short for 6-23/3
        $db->executeQuery("CALL bm_rules_parse('6/3','dayofmonth')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"6");
        $this->assertEquals($result['range_closed'],"31");
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
                $db->executeQuery("CALL bm_rules_parse('?','dayofmonth')",array($pattern));
                
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
        $db->executeQuery("CALL bm_rules_parse('*','dayofweek')");
        
        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals($result['range_open'],"0");
        $this->assertEquals($result['range_closed'],"6");
        $this->assertEquals($result['value_type'],"dayofweek");
        $this->assertEquals($result['mod_value'],1);    
        
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ## e.g scalar value range 0 to 6
        $db->executeQuery("CALL bm_rules_parse('0','dayofweek')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"0");
        $this->assertEquals($result['range_closed'],"0");
        $this->assertEquals($result['value_type'],"dayofweek");
        $this->assertEquals($result['mod_value'],1);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_parse('3-6','dayofweek')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"3");
        $this->assertEquals($result['range_closed'],6);
        $this->assertEquals($result['value_type'],"dayofweek");
        $this->assertEquals($result['mod_value'],1);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_parse('*/20','dayofweek')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals($result['range_open'],"0");
        $this->assertEquals($result['range_closed'],"6");
        $this->assertEquals($result['value_type'],"dayofweek");
        $this->assertEquals($result['mod_value'],20);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##/## e.g 4/3 short for 4-6/3
        $db->executeQuery("CALL bm_rules_parse('4/3','dayofweek')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"4");
        $this->assertEquals($result['range_closed'],"6");
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
                $db->executeQuery("CALL bm_rules_parse('?','dayofweek')",array($pattern));
                
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
        $db->executeQuery("CALL bm_rules_parse('*','month')");
        
        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals($result['range_open'],"1");
        $this->assertEquals($result['range_closed'],"12");
        $this->assertEquals($result['value_type'],"month");
        $this->assertEquals($result['mod_value'],1);    
        
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ## e.g scalar value range 1 to 12
        $db->executeQuery("CALL bm_rules_parse('1','month')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"1");
        $this->assertEquals($result['range_closed'],"1");
        $this->assertEquals($result['value_type'],"month");
        $this->assertEquals($result['mod_value'],1);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_parse('3-6','month')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"3");
        $this->assertEquals($result['range_closed'],6);
        $this->assertEquals($result['value_type'],"month");
        $this->assertEquals($result['mod_value'],1);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_parse('*/20','month')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals($result['range_open'],"1");
        $this->assertEquals($result['range_closed'],"12");
        $this->assertEquals($result['value_type'],"month");
        $this->assertEquals($result['mod_value'],20);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##/## e.g 4/3 short for 4-12/3
        $db->executeQuery("CALL bm_rules_parse('4/3','month')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"4");
        $this->assertEquals($result['range_closed'],"12");
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
                $db->executeQuery("CALL bm_rules_parse('?','month')",array($pattern));
                
                $this->assertTrue(false,'Test for minute parse fails has failed to cause an exception');
            }
            catch(DBALException $e) {
                $this->assertContains('1644 not support cron format',$e->getMessage());
            }
            
        }
    
        
    }
    
    public function testYearValidCombinations() 
    {
        $db = $this->getDoctrineConnection();
        
        # Test if valid formats create expected result set in 
        # the result tmp table;
        
        # Test for the default '*'
        $db->executeQuery("CALL bm_rules_parse('*','year')");
        
        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals($result['range_open'],"2000");
        $this->assertEquals($result['range_closed'],"2199");
        $this->assertEquals($result['value_type'],"year");
        $this->assertEquals($result['mod_value'],1);    
        
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ## e.g scalar value range 2000 to 2199
        $db->executeQuery("CALL bm_rules_parse('2000','year')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"2000");
        $this->assertEquals($result['range_closed'],"2000");
        $this->assertEquals($result['value_type'],"year");
        $this->assertEquals($result['mod_value'],1);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_parse('2001-2005','year')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"2001");
        $this->assertEquals($result['range_closed'],2005);
        $this->assertEquals($result['value_type'],"year");
        $this->assertEquals($result['mod_value'],1);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_parse('*/20','year')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals($result['range_open'],"2000");
        $this->assertEquals($result['range_closed'],"2199");
        $this->assertEquals($result['value_type'],"year");
        $this->assertEquals($result['mod_value'],20);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##/## e.g 2014/3 short for 2014-2199/3
        $db->executeQuery("CALL bm_rules_parse('2014/3','year')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"2014");
        $this->assertEquals($result['range_closed'],"2199");
        $this->assertEquals($result['value_type'],"year");
        $this->assertEquals($result['mod_value'],3);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        
        
    }
    
    public function testYearParseFailures()
    {
        $db = $this->getDoctrineConnection();
        $patterns = array(
            'one'    => '2599'
            ,'two'   => 'a'
            ,'three' => '-2000'
            ,'four'  =>'5-2000'
            ,'five' => '4-32'
            ,'six' => '**/20'
            ,'seven' => '2500/3'
            ,'eight'   => '6/*'
            ,'nine'  => '2014-3000/3'
            ,'ten' => '2014-*/3'
            ,'eleven' => '-2000-2199/3'
            
            
        );
        
        
        foreach($patterns as $key => $pattern) {
        
            try {
                $db->executeQuery("CALL bm_rules_parse('?','year')",array($pattern));
                
                $this->assertTrue(false,'Test for parse fails has failed to cause an exception');
            }
            catch(DBALException $e) {
                $this->assertContains('1644 not support cron format',$e->getMessage());
            }
            
        }
    
        
    }
    
    
    public function testAddRepeatRuleFailsBadFormat() 
    {
        $db = $this->getDoctrineConnection();
        
        $ruleName           = 'testRuleA';
        $ruleType           = 'inclusion';
        $ruleMinute         = 0;
        $ruleHour           = 0;
        $ruleDayofweek      = 0;
        $ruleDayofmonth     = 0;
        $ruleMonth          = 1;
		$ruleYear           = 2014;
		$scheduleGroupID    = 2;
		$memberID           = NULL;
		
        try {
            
            $db->exec('START TRANSACTION');								
            
            $db->executeQuery("CALL bm_rules_add_repeat_rule(?,?,?,?,?,?,?,?,?,?,@newRuleID)"
                ,array($ruleName,$ruleType,$ruleMinute,$ruleHour,$ruleDayofweek,$ruleDayofmonth,$ruleMonth,$ruleYear,$scheduleGroupID,$memberID)
            );
            $db->exec('ROLLBACK');
            
            $this->assertFalse(true);
        
        } catch(\Doctrine\DBAL\DBALException $e) {
            $db->exec('ROLLBACK');
            $this->assertContains('not support cron format',$e->getMessage());
        }
        
        

    }
    
    public function testAddRepatRuleFailesWithNoSlotError()
    {
        $db = $this->getDoctrineConnection();
        
        $ruleName           = 'ruleA';
        $ruleType           = 'inclusion';
        $ruleMinute         = 0;
        $ruleHour           = 0;
        $ruleDayofweek      = 0;
        $ruleDayofmonth     = 1;
        $ruleMonth          = 1;
		$ruleYear           = 2014;
		$scheduleGroupID    = 2;
		$memberID           = NULL;
										
       try { 
             $db->exec('START TRANSACTION');								
            
           
            $db->executeQuery("CALL bm_rules_add_repeat_rule(?,?,?,?,?,?,?,?,?,?,@newRuleID)"
                ,array($ruleName,$ruleType,$ruleMinute,$ruleHour,$ruleDayofweek,$ruleDayofmonth,$ruleMonth,$ruleYear,$scheduleGroupID,$memberID)
            );
            
            $db->exec('ROLLBACK');
            
            $this->assertFalse(true);
        
       } catch(\Doctrine\DBAL\DBALException $e) {
            $db->exec('ROLLBACK');
            $this->assertContains('The new Rule did not have any slots to insert',$e->getMessage());
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
		$ruleYear           = 2014;
		$scheduleGroupID    = 2;
		$memberID           = NULL;
										
       try { 
             $db->exec('START TRANSACTION');								
            
           
            $db->executeQuery("CALL bm_rules_add_repeat_rule(?,?,?,?,?,?,?,?,?,?,@newRuleID)"
                ,array($ruleName,$ruleType,$ruleMinute,$ruleHour,$ruleDayofweek,$ruleDayofmonth,$ruleMonth,$ruleYear,$scheduleGroupID,$memberID)
            );
            
            $db->exec('ROLLBACK');
            
            $this->assertFalse(true);
        
       } catch(\Doctrine\DBAL\DBALException $e) {
            $db->exec('ROLLBACK');
            $this->assertContains('Given ruleType is invalid must be inclusion or adhoc',$e->getMessage());
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
        $ruleMonth          = 1;
		$ruleYear           = 2014;
		$scheduleGroupID    = 2;
		$memberID           = NULL;
		
		
		$now = $db->fetchColumn('SELECT CAST(NOW() AS DATETIME)',array(),0);
		$changedBy = $db->fetchColumn('SELECT USER()',array(),0);
        
        $columnMap = array(
             'rule_name'        => $ruleName
            ,'rule_type'        => $ruleType  
            ,'rule_repeat'      => 'repeat'
            ,'repeat_minute'    => $ruleMinute
            ,'repeat_hour'      => $ruleHour  
            ,'repeat_dayofweek' => $ruleDayofweek
            ,'repeat_dayofmonth'=> $ruleDayofmonth
            ,'repeat_month'     => $ruleMonth     
    		,'repeat_year'      => $ruleYear      
    		,'schedule_group_id'=> $scheduleGroupID
    		,'membership_id'    => $memberID
    		,'opening_slot_id'  => null
    		,'closing_slot_id'  => null
    		,'created_date'     => $now
    		,'updated_date'     => $now
        );
        
        $db->executeQuery("CALL bm_rules_add_repeat_rule(?,?,?,?,?,?,?,?,?,?,@newRuleID)"
            ,array($ruleName,$ruleType,$ruleMinute,$ruleHour,$ruleDayofweek,$ruleDayofmonth,$ruleMonth,$ruleYear,$scheduleGroupID,$memberID)
        );
        
        // ensure that we got a good id back for the new rule
        $newRuleID = $db->fetchColumn('SELECT @newRuleID',array(),0);
        
        // rule exists in rule table
        $ruleSTH = $db->executeQuery('SELECT * FROM rules where rule_id = ?',array($newRuleID));
        
        $ruleResult = $ruleSTH->fetch();
        
        
        if(empty($ruleResult)) {
            $this->assertFalse(true,'The new rule not found');
        }
        
        foreach($ruleResult as $key => $result) {
            if($key !== 'rule_id') {
                $this->assertEquals($columnMap[$key],$result);
            }
        }
        
        
        // is insert recorded in the audit table
        $auditSTH = $db->executeQuery('SELECT * 
                                       FROM audit_rules 
                                       WHERE rule_id = ? 
                                       AND action = ?',array($newRuleID,'I'));
        
        $auditResult = $auditSTH->fetch();
        
        if(empty($auditResult)) {
            $this->assertFalse(true,'No Audit Record Found');
        }
        
         $columnMap['change_time'] = $now;
         $columnMap['rule_id'] = $newRuleID;
         $columnMap['changed_by'] = $changedBy;
         $columnMap['action'] = 'I';
         
        
        // is their and opeation
        foreach($ruleResult as $key => $result) {
            if($key !== 'change_seq') {
                $this->assertEquals($columnMap[$key],$result);
            }
        }
        
        
        //is their slots
        
        
        // Update the rule 
        
        
        // Test audit has been amended too.
        
        
        // Delete rule 
        
        // Step 1 . Test cleanup method
        
        // Step 2. Remove the rule
        
        // Step 3. Test trigger
        
    }


    

    
    
}
/* End of Class */