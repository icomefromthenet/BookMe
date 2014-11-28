<?php
namespace IComeFromTheNet\BookMe\Tests;

use IComeFromTheNet\BookMe\BookMeService;


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
        
        $this->assertEquals($result['range_open'],"1");
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
        $this->assertEquals($result['range_open'],"1");
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
        
          # Test format ##- ##/## e.g 6-59/3 
        $db->executeQuery("CALL bm_rules_parse('6/3','minute')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"6");
        $this->assertEquals($result['range_closed'],"59");
        $this->assertEquals($result['value_type'],"minute");
        $this->assertEquals($result['mod_value'],3);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
    }
    
    /**
    * @expectedException Doctrine\DBAL\DBALException
    * @expectedExceptionMessage 1644 not support cron minute format
    */ 
    public function testMinuteParseFailsOutRangeScalar()
    {
        $db = $this->getDoctrineConnection();
        $db->executeQuery("CALL bm_rules_parse('60','minute')");
        
    }
    
    /**
    * @expectedException Doctrine\DBAL\DBALException
    * @expectedExceptionMessage 1644 not support cron minute format
    */ 
    public function testMinuteParseFailsOnAlpha()
    {
        $db = $this->getDoctrineConnection();
        $db->executeQuery("CALL bm_rules_parse('a','minute')");
        
    }
    
    /**
    * @expectedException Doctrine\DBAL\DBALException
    * @expectedExceptionMessage 1644 not support cron minute format
    */
    public function testMinuteParseFailsNegativeScalar()
    {
        $db = $this->getDoctrineConnection();
        $db->executeQuery("CALL bm_rules_parse('-1','minute')");
    }
    
    /**
    * @expectedException Doctrine\DBAL\DBALException
    * @expectedExceptionMessage 1644 not support cron minute format
    */
    public function testMinuteParseFailsOutRangeRangeOne()
    {
        $db = $this->getDoctrineConnection();
        $db->executeQuery("CALL bm_rules_parse('60-59','minute')");
    }
    
    /**
    * @expectedException Doctrine\DBAL\DBALException
    * @expectedExceptionMessage 1644 not support cron minute format
    */
    public function testMinuteParseFailsOutRangeRangeTwo()
    {
        $db = $this->getDoctrineConnection();
        $db->executeQuery("CALL bm_rules_parse('6-60','minute')");
    }
    
    /**
    * @expectedException Doctrine\DBAL\DBALException
    * @expectedExceptionMessage 1644 not support cron minute format
    */
    public function testMinuteParseFailsOutRangeRangeThree()
    {
        $db = $this->getDoctrineConnection();
        $db->executeQuery("CALL bm_rules_parse('**/20','minute')");
    }
    
    /**
    * @expectedException Doctrine\DBAL\DBALException
    * @expectedExceptionMessage 1644 not support cron minute format
    */
    public function testMinuteParseFailsOutRangeRangeFour()
    {
        $db = $this->getDoctrineConnection();
        $db->executeQuery("CALL bm_rules_parse('60/3','minute')");
    }
    
    /**
    * @expectedException Doctrine\DBAL\DBALException
    * @expectedExceptionMessage 1644 not support cron minute format
    */
    public function testMinuteParseFailsOutRangeRangeFive()
    {
        $db = $this->getDoctrineConnection();
        $db->executeQuery("CALL bm_rules_parse('6/*','minute')");
    }
    
     /**
    * @expectedException Doctrine\DBAL\DBALException
    * @expectedExceptionMessage 1644 not support cron minute format
    */
    public function testMinuteParseFailsOutRangeRangeSix()
    {
        $db = $this->getDoctrineConnection();
        $db->executeQuery("CALL bm_rules_parse('6-60/3','minute')");
    }
    
    /**
    * @expectedException Doctrine\DBAL\DBALException
    * @expectedExceptionMessage 1644 not support cron minute format
    */
    public function testMinuteParseFailsOutRangeRangeSeven()
    {
        $db = $this->getDoctrineConnection();
        $db->executeQuery("CALL bm_rules_parse('6-*/3','minute')");
    }
    
    /**
    * @expectedException Doctrine\DBAL\DBALException
    * @expectedExceptionMessage 1644 not support cron minute format
    */
    public function testMinuteParseFailsOutRangeRangeEight()
    {
        $db = $this->getDoctrineConnection();
        $db->executeQuery("CALL bm_rules_parse('-1-59/3','minute')");
    }
    
    
    
    public function testHourValidCombinations() {
        
        
        
        
        
    }
    
    
}
/* End of Class */