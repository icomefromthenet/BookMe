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
    
}
/* End of Class */