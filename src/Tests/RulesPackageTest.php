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
        $db->executeQuery("CALL bm_rules_parse_minute('*')");
        
        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        
        $this->assertEquals($result['range_open'],"1");
        $this->assertEquals($result['range_closed'],"59");
        $this->assertEquals($result['value_type'],"minute");
        $this->assertEquals($result['mod_value'],0);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ## e.g scalar value range 1 to 59
        $db->executeQuery("CALL bm_rules_parse_minute('56')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"56");
        $this->assertEquals($result['range_closed'],"56");
        $this->assertEquals($result['value_type'],"minute");
        $this->assertEquals($result['mod_value'],0);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_parse_minute('34-59')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"34");
        $this->assertEquals($result['range_closed'],"59");
        $this->assertEquals($result['value_type'],"minute");
        $this->assertEquals($result['mod_value'],0);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
        # Test format ##-## e.g range scalar values
        $db->executeQuery("CALL bm_rules_parse_minute('*/20')");

        $result = $db->fetchAssoc('SELECT * FROM bm_parsed_ranges');
        $this->assertEquals($result['range_open'],"1");
        $this->assertEquals($result['range_closed'],"59");
        $this->assertEquals($result['value_type'],"minute");
        $this->assertEquals($result['mod_value'],20);
        
        $db->executeQuery("TRUNCATE bm_parsed_ranges");
        
    }
    
    /**
    * @expectedException Doctrine\DBAL\DBALException
    * @expectedExceptionMessage 1644 not support cron minute format
    */ 
    public function testMinuteParseFailsOutRangeScalar()
    {
        $db = $this->getDoctrineConnection();
        $db->executeQuery("CALL bm_rules_parse_minute('60')");
        
    }
    
    /**
    * @expectedException Doctrine\DBAL\DBALException
    * @expectedExceptionMessage 1644 not support cron minute format
    */ 
    public function testMinuteParseFailsOnAlpha()
    {
        $db = $this->getDoctrineConnection();
        $db->executeQuery("CALL bm_rules_parse_minute('a')");
        
    }
    
    /**
    * @expectedException Doctrine\DBAL\DBALException
    * @expectedExceptionMessage 1644 not support cron minute format
    */
    public function testMinuteParseFailsNegativeScalar()
    {
        $db = $this->getDoctrineConnection();
        $db->executeQuery("CALL bm_rules_parse_minute('-1')");
    }
    
    /**
    * @expectedException Doctrine\DBAL\DBALException
    * @expectedExceptionMessage 1644 not support cron minute format
    */
    public function testMinuteParseFailsOutRangeRangeOne()
    {
        $db = $this->getDoctrineConnection();
        $db->executeQuery("CALL bm_rules_parse_minute('60-59')");
    }
    
    /**
    * @expectedException Doctrine\DBAL\DBALException
    * @expectedExceptionMessage 1644 not support cron minute format
    */
    public function testMinuteParseFailsOutRangeRangeTwo()
    {
        $db = $this->getDoctrineConnection();
        $db->executeQuery("CALL bm_rules_parse_minute('6-60')");
    }
    
    /**
    * @expectedException Doctrine\DBAL\DBALException
    * @expectedExceptionMessage 1644 not support cron minute format
    */
    public function testMinuteParseFailsOutRangeRangeThree()
    {
        $db = $this->getDoctrineConnection();
        $db->executeQuery("CALL bm_rules_parse_minute('**/20')");
    }
}
/* End of Class */