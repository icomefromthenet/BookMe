<?php
namespace IComeFromTheNet\BookMe\Tests;

use IComeFromTheNet\BookMe\BookMeService;
use IComeFromTheNet\BookMe\Tests\BasicTest;
use Doctrine\DBAL\DBALException;

class RulesOtherPackageTest extends BasicTest
{
    
    /**
    * @expectedException \Doctrine\DBAL\DBALException
    * @expectedExceptionMessage The date param must be before NOW
    */ 
    public function testScheduleAffectedByChangesQueryFailsBadDate()
    {
        
        $db        = $this->getDoctrineConnection();
        $afterDate = $db->fetchColumn('SELECT (NOW())',array(),0);
        
        $db->executeQuery('CALL bm_rules_find_affected_schedules(?)',array($afterDate));
        
    }
    
    public function testScheduleAffectedByChangesQuery()
    {
        
        $db        = $this->getDoctrineConnection();
        $afterDate = $db->fetchColumn('SELECT (NOW() - INTERVAL 1 DAY)',array(),0);
        
        $db->executeQuery('CALL bm_rules_find_affected_schedules(?)',array($afterDate));
        
        
        # every schedule in demo bundle should be included as they are all linked to a rule
        # rule that was created within the last 24 hours, be happy if the count
        
        
        # do we have any results in the table (We should)
        $resultSTH = $db->executeQuery('SELECT * FROM `schedules_affected_by_changes`');
        $count = 0;
        while($result = $resultSTH->fetch()) {
            $count++;
        }
        
        # last known count had 10 schedules in demo group, at least 10 affected schedules
        $this->assertGreaterThanOrEqual(10,$count);
        
    }
    
    /**
    * @expectedException \Doctrine\DBAL\DBALException
    * @expectedExceptionMessage Unable to relate rule to schedule group as rule may have a bad validity range
    */ 
    public function testRelateRuleToAnInactiveGroupFails()
    {
        
        $db              = $this->getDoctrineConnection();
        $demoRuleID      = 7;
        $inactiveGroupID = $db->fetchColumn('SELECT `group_id` FROM `schedule_groups` WHERE `valid_to` < NOW() LIMIT 1',array(),0);
        
        if($inactiveGroupID == false) {
            $this->assertTrue(false,'unable to find an inactive schedule group to use in this testcase');
        }
        
        $db->executeQuery('CALL bm_rules_relate_group(?,?)',array($demoRuleID,$inactiveGroupID));
        
        
    }
    
    

    
    
} 
/* End of File */
