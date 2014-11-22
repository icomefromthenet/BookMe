<?php
namespace IComeFromTheNet\BookMe\Tests;

use IComeFromTheNet\BookMe\BookMeService;


class SchedulePackageTest extends BasicTest
{
    
    
    public function testNewScheduleProcdure()
    {
        
        $db = $this->getDoctrineConnection();
        
        $db->executeQuery('call bm_add_membership(@membershipID)',array(),array());
            
        $memberID = (int) $db->fetchColumn('SELECT @membershipID',array(),0);
      
        # assert out param set
        $this->assertNotEmpty($memberID);
        
        # assert that registered date set 
        $result = $db->fetchAssoc('SELECT * FROM schedule_membership WHERE membership_id = ?',array($memberID));
        $this->assertNotEmpty($result['registered_date']);
        $this->assertInstanceOf('\DateTime',\DateTime::createFromFormat('Y-m-d H:i:s',$result['registered_date']));
        
    }
   
   
    public function testAddScheduleGroup()
    {
        $db     = $this->getDoctrineConnection();
        $now    = $db->fetchColumn('SELECT CAST(NOW() AS DATE)',array(),0);
        $later  = $db->fetchColumn('SELECT CAST(date_add(now(),INTERVAL 7 DAY) AS DATE)',array(),0);
        $name   = 'mygroup';
        
        $db->executeQuery('call bm_schedule_add_group(?,?,?,@groupID);',array($name,$now,$later),array(\PDO::PARAM_STR,\PDO::PARAM_STR)); 
        
        $groupID = (int) $db->fetchColumn('SELECT @groupID',array(),0);
        
        # asser out param set
        $this->assertNotEmpty($groupID);
        
        $result = $db->fetchAssoc('SELECT * FROM schedule_groups WHERE group_id = ?',array($groupID));
        
        # assert that dates and name are set
        $this->assertEquals((int)$result['group_id'],$groupID);
        $this->assertEquals($result['valid_from'],$now);
        $this->assertEquals($result['valid_to'],$later);
        $this->assertEquals($result['group_name'],$name);
        
    }
    
    
    public function testAddSchedule()
    {
        
        
        
    }
    
    
    public function testAddScheduleFailsOutOfRangeSchedule()
    {
    
    
    
    }
    
}
   
   
}
/* End of class */

