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
        $db         = $this->getDoctrineConnection();
        $now         = $db->fetchColumn('SELECT CAST(NOW() AS DATE)',array(),0);
        $later       = $db->fetchColumn('SELECT CAST(date_add(now(),INTERVAL 1 YEAR) AS DATE)',array(),0);
        $timeslotID  = 1;
        $groupID     = $db->fetchColumn('SELECT group_id from schedule_groups where group_name = ?',array('mygroup5'),0);
        $memberID    = 1;
        
        
        if(empty($groupID)) {
            $this->assertFalse(true,'The schedule group could not be found at mygroup5 to execute test');
        } else {
            
            $db->executeQuery('call bm_schedule_add(?,?,?,?,?,@outScheduleID)',array(
                $groupID
                ,$memberID
                ,$timeslotID
                ,$now
                ,$later
                ),array(
                \PDO::PARAM_INT
                ,\PDO::PARAM_INT
                ,\PDO::PARAM_INT
                ,\PDO::PARAM_STR
                ,\PDO::PARAM_STR
            ));
            
            # assert the out param
            $scheduleID = (int) $db->fetchColumn('SELECT @outScheduleID',array(),0);
            $this->assertNotEmpty($scheduleID);
            
            # assert the values match
            $result = $db->fetchAssoc('SELECT * FROM schedules WHERE schedule_id = ?',array($scheduleID));
            
            $this->assertEquals($scheduleID ,(int)$result['schedule_id']);
            $this->assertEquals($now,$result['open_from']);
            $this->assertEquals($later,$result['closed_on']);
            $this->assertEquals($groupID,$result['schedule_group_id']);
            $this->assertEquals($memberID,$result['membership_id']);
            
        }
        
    }
    
    /**
     * Test if a group that not valid during entire schedule validiy period should fail to insert.
     * 
     * @expectedException Doctrine\DBAL\DBALException
     * @expectedExceptionMessage Integrity constraint violation: 1048 Column 'schedule_group_id' cannot be null
     */ 
    public function testAddScheduleFailsOutOfRangeSchedule()
    {
        $db         = $this->getDoctrineConnection();
        $now         = $db->fetchColumn('SELECT CAST(NOW() AS DATE)',array(),0);
        $later       = $db->fetchColumn('SELECT CAST(date_add(now(),INTERVAL 1 YEAR) AS DATE)',array(),0);
        $timeslotID  = 1;
        $groupID     = $db->fetchColumn('SELECT group_id from schedule_groups where group_name = ?',array('mygroup2'),0);
        $memberID    = 1;
        
        
        if(empty($groupID)) {
            $this->assertFalse(true,'The schedule group could not be found at mygroup2 to execute test');
        } else {
            
            $db->executeQuery('call bm_schedule_add(?,?,?,?,?,@outScheduleID)',array(
                $groupID
                ,$memberID
                ,$timeslotID
                ,$now
                ,$later
                ),array(
                \PDO::PARAM_INT
                ,\PDO::PARAM_INT
                ,\PDO::PARAM_INT
                ,\PDO::PARAM_STR
                ,\PDO::PARAM_STR
            ));
        }
    
    }
    
}
/* End of class */

