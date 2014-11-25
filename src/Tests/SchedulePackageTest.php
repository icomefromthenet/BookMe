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
        $groupID     = $db->fetchColumn('SELECT group_id from schedule_groups where group_name = ?',array('mygroup5'),0);
        $memberID    = 1;
        
        
        if(empty($groupID)) {
            $this->assertFalse(true,'The schedule group could not be found at mygroup5 to execute test');
        } else {
            
            $db->executeQuery('call bm_schedule_add(?,?,?,?,@outScheduleID)',array(
                $groupID
                ,$memberID
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
        $groupID     = $db->fetchColumn('SELECT group_id from schedule_groups where group_name = ?',array('mygroup2'),0);
        $memberID    = 1;
        
        
        if(empty($groupID)) {
            $this->assertFalse(true,'The schedule group could not be found at mygroup2 to execute test');
        } else {
            
            $db->executeQuery('call bm_schedule_add(?,?,?,?,@outScheduleID)',array(
                $groupID
                ,$memberID
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
    
    
    /**
     * @expectedException Doctrine\DBAL\DBALException
     * @expectedExceptionMessage  Group not found or validTo date is not within original validity range
     */ 
    public function testRetireGroupFailsWhenValidToOutRange()
    {
        $db         = $this->getDoctrineConnection();
        
        $groupID    = $db->fetchColumn('SELECT group_id FROM schedule_groups where group_name = ?'
                                        ,array('mygroup3'),0);
        
        # this invalid retire date its week ahead of the original date
        $retireOn   = $db->fetchColumn('SELECT CAST((NOW()+ INTERVAL 14 DAY) AS DATE) AS dte',array(),0);
        
        if(empty($groupID)) {
            $this->assertTrue(false,'the group could not be found to execute test on');   
        } else {
        
            # test that retire operation fails if the new
            # validity end date exceeds the original end date
            
            $db->executeQuery('CALL bm_schedule_retire_group(?,?)'
                                ,array($groupID,$retireOn)
                                ,array(\PDO::PARAM_INT,\PDO::PARAM_STR));
            
            
        }
    }
    
    
    /**
     * @expectedException Doctrine\DBAL\DBALException
     * @expectedExceptionMessage  Group not found or validTo date is not within original validity range
     */
    public function testRetireGroupFailsWhenScheduleExists()
    {
        $db         = $this->getDoctrineConnection();
        
        $groupID    = $db->fetchColumn('SELECT group_id FROM schedule_groups WHERE group_name = ?'
                                        ,array('mygroup3'),0);
        
        if(empty($groupID)) {
            $this->assertTrue(false,'the group could not be found to execute test on');   
        } else {
    
    
            $retireOn   = $db->fetchColumn('SELECT (closed_on - INTERVAL 1 DAY) AS dte 
                                        FROM schedules 
                                        WHERE schedule_group_id = ? 
                                        LIMIT 1',array($groupID),0);
    
    
        
            # test that retire operation fails if the new
            # validity end date ends before the last related schedule close date
            $db->executeQuery('CALL bm_schedule_retire_group(?,?)'
                                ,array($groupID,$retireOn)
                                ,array(\PDO::PARAM_INT,\PDO::PARAM_STR));
            
        }    
        
    }
    
    
    public function testRetireSuccessOnUnsedGroup() 
    {
        $db         = $this->getDoctrineConnection();
        
        $groupID    = $db->fetchColumn('SELECT group_id FROM schedule_groups WHERE group_name = ?'
                                        ,array('mygrouptest6'),0);
        
        $retireOn   = $db->fetchColumn('SELECT CAST((NOW() + INTERVAL 3 WEEK) AS DATE) AS dte'
                                        ,array(),0); 
        
        
        if(empty($groupID)) {
            $this->assertTrue(false,'the group could not be found to execute test on');   
        } else {
            
            $db->executeQuery('CALL bm_schedule_retire_group(?,?)'
                                ,array($groupID,$retireOn)
                                ,array(\PDO::PARAM_INT,\PDO::PARAM_STR));
        
        }
        
    }
    
    
    public function testRetireGroupSucceeds()
    {
        $db         = $this->getDoctrineConnection();
        
        $result     = $db->fetchAssoc('SELECT closed_on, schedule_group_id 
                                        FROM schedules 
                                        WHERE schedule_id = ?
                                        LIMIT 1',array(6));
    
        $retireOn = $result['closed_on'];
        $groupID = $result['schedule_group_id'];
    
        # testing that can retire a group on that last day that
        # an actual schedule is valid
        $db->executeQuery('CALL bm_schedule_retire_group(?,?)'
                                ,array($groupID,$retireOn)
                                ,array(\PDO::PARAM_INT,\PDO::PARAM_STR));
        
    }
    
    /**
     * @expectedException Doctrine\DBAL\DBALException
     * @expectedExceptionMessage  Unable to remove group the ID given may not have been found or may be active group already
     */
    public function testRemovalFailesOnActiveGroup()
    {
        $db         = $this->getDoctrineConnection();
        
        
        # test that cant remove a group where valid_from is < NOW()
        # so we can't remove an active group
        $db->executeQuery('CALL bm_schedule_remove_group(?)'
                                ,array(1)
                                ,array(\PDO::PARAM_INT));
      
        
    
    }
    
    public function testRemovalGroupSucceeds()
    {
        $db         = $this->getDoctrineConnection();
        
        $db->executeQuery('CALL bm_schedule_remove_group(?)'
                                ,array(7)
                                ,array(\PDO::PARAM_INT));
      
    }
    
}
/* End of class */

