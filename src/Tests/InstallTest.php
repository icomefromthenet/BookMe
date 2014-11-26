<?php
namespace IComeFromTheNet\BookMe\Tests;

use IComeFromTheNet\BookMe\BookMeService;


class InstallTest extends BasicTest
{
    
    
    
    public function testInstallMethod()
    {
        $container = self::getContainer();
        
        $this->assertTrue(true);    
        
        
    }
    
    /*
    public function testAddTimeslot()
    {
        $db             = $this->getDoctrineConnection();
        $timeslotLength = 60*8;
        $slotCount = $db->fetchColumn('SELECT count(`slot_id`) FROM `slots`',array(),0);
        
        if(empty($slotCount)) {
            $this->assertTrue(false,'Unable to fetch slot table count');   
        } else {
        
        # call procedure and assert the outparam        
        $db->executeQuery('CALL bm_calendar_add_timeslot(?,@timeslotID)',array($timeslotLength));
        $id = $db->fetchColumn('SELECT @timeslotID',array(),0);

        $this->assertNotEmpty($id);
        
        #assert the relation table has values
        
        $nCount = ceil($slotCount / $timeslotLength);
        
        $timeslotResut = $db->fetchAssoc('SELECT COUNT(*) AS slot_count
                                                , MIN(`os`.`slot_open`) AS min_slot
                                                , MAX(`cs`.`slot_close`) AS max_slot
                                         FROM `timeslot_slots` ts
                                         JOIN `slots` os ON `ts`.`opening_slot_id` =`os`.`slot_id`
                                         JOIN `slots` cs ON `ts`.`closing_slot_id` =`os`.`slot_id`
                                         WHERE `timeslot_id` = ?
                                         GROUP BY timeslot_id',array($id));
                                         
        $calenderResult = $db->fetchAssoc('SELECT MIN(`calendar_date`) AS first_day
                                                , MAX(`calendar_date`) AS last_day 
                                         FROM `calendar`');
                                         
        # make sure the timeslots generated equal max and min dates from cal table                                 
                                         
                         
        }
    } */
    
    public function testAddTimeslotFailesOnDuplicate()
    {
        
        
    }
    
    
    public function testAddTimeslotFailesOnInvalidLength()
    {
        
        
    }
    
    public function testRemoveTimeslot()
    {
        
        
    }
    
    
    public function testRemoveTimeslotFailsInvalidID()
    {
        
        
    }
    
    
    
    
}
/* End of file */
    