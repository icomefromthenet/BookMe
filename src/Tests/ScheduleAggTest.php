<?php
namespace IComeFromTheNet\BookMe\Tests;

use IComeFromTheNet\BookMe\BookMeService;
use IComeFromTheNet\BookMe\Tests\BasicTest;
use Doctrine\DBAL\DBALException;

class ScheduleAggTest extends BasicTest
{
    
    public function testAddBookingToSingleNewAggSchedule()
    {
        $db         = $this->getDoctrineConnection();
        $scheduleID = $db->fetchColumn('SELECT schedule_id from schedules ORDER BY RAND() LIMIT 1',array(),0);
        
        $dateResult = $db->executeQuery('SELECT  bm_cal_get_slot_date(?) as open_date
                                                ,bm_cal_get_slot_date(?) as close_date
                                                ,EXTRACT(YEAR FROM bm_cal_get_slot_date(?)) as open_year
                                                ,EXTRACT(WEEK FROM bm_cal_get_slot_date(?)) as open_week
                                                ,WEEKDAY(bm_cal_get_slot_date(?)) as week_day'
                                        ,array(1,100,1,1,1))
                         ->fetch();
        
        $openingDate = $dateResult['open_date'];
        $closingDate = $dateResult['close_date'];
        $openWeek    = $dateResult['open_week'];
        $openYear    = $dateResult['open_year'];
        $weekDay     = (int) $dateResult['week_day'];
        
        
        $db->executeQuery('CALL bm_schedule_add_booking_mv(?,?,?)',array($scheduleID,$openingDate,$closingDate));
        
        $resultSTH = $db->executeQuery('SELECT * 
                                        FROM `bookings_agg_mv` 
                                        WHERE `schedule_id` = ? 
                                        AND `cal_week` = ? 
                                        AND `cal_year` = ?',array($scheduleID,$openWeek,$openYear));

        
        $result = $resultSTH->fetch();
        
        $dayCount = array(
             1 => 0
            ,2 => 0
            ,3 => 0
            ,4 => 0
            ,5 => 0
            ,6 => 0
            ,7 => 0
        );
        $dayCount[$weekDay] = $dayCount[$weekDay] +1;
        
        $this->assertEquals($dayCount[1],$result['cal_sun']);
        $this->assertEquals($dayCount[2],$result['cal_mon']);
        $this->assertEquals($dayCount[3],$result['cal_tue']);
        $this->assertEquals($dayCount[4],$result['cal_wed']);
        $this->assertEquals($dayCount[5],$result['cal_thu']);
        $this->assertEquals($dayCount[6],$result['cal_fri']);
        $this->assertEquals($dayCount[7],$result['cal_sat']);
        
        return array($scheduleID,$dayCount);
    }
    
    
    /**
     * @depends testAddBookingToSingleNewAggSchedule
     */ 
    public function testAddBookingToSingleExistingAggSchedule($data)
    {
        $db = $this->getDoctrineConnection();
        
        $scheduleID = $data[0];
        $dayCount   = $data[1];
        
        
        $dateResult = $db->executeQuery('SELECT  bm_cal_get_slot_date(?) as open_date
                                                ,bm_cal_get_slot_date(?) as close_date
                                                ,EXTRACT(YEAR FROM bm_cal_get_slot_date(?)) as open_year
                                                ,EXTRACT(WEEK FROM bm_cal_get_slot_date(?)) as open_week
                                                ,WEEKDAY(bm_cal_get_slot_date(?)) as week_day'
                                        ,array(101,200,101,101,101))
                         ->fetch();
        
        $openingDate = $dateResult['open_date'];
        $closingDate = $dateResult['close_date'];
        $openWeek    = $dateResult['open_week'];
        $openYear    = $dateResult['open_year'];
        $weekDay     = (int) $dateResult['week_day'];
        
        
        $db->executeQuery('CALL bm_schedule_add_booking_mv(?,?,?)',array($scheduleID,$openingDate,$closingDate));
        
        $resultSTH = $db->executeQuery('SELECT * 
                                        FROM `bookings_agg_mv` 
                                        WHERE `schedule_id` = ? 
                                        AND `cal_week` = ? 
                                        AND `cal_year` = ?',array($scheduleID,$openWeek,$openYear));

        
        $result = $resultSTH->fetch();
        $dayCount[$weekDay] = $dayCount[$weekDay] + 1;
        
        $this->assertEquals($dayCount[1],$result['cal_sun']);
        $this->assertEquals($dayCount[2],$result['cal_mon']);
        $this->assertEquals($dayCount[3],$result['cal_tue']);
        $this->assertEquals($dayCount[4],$result['cal_wed']);
        $this->assertEquals($dayCount[5],$result['cal_thu']);
        $this->assertEquals($dayCount[6],$result['cal_fri']);
        $this->assertEquals($dayCount[7],$result['cal_sat']);
        
         return array($scheduleID,$dayCount);
    } 
    
    
    /**
     *  @depends testAddBookingToSingleExistingAggSchedule
     */ 
    public function testDeleteBookingFromSingle(array $data)
    {
        $db = $this->getDoctrineConnection();
        
        $scheduleID = $data[0];
        $dayCount   = $data[1];
        
        
        $dateResult = $db->executeQuery('SELECT  bm_cal_get_slot_date(?) as open_date
                                                ,bm_cal_get_slot_date(?) as close_date
                                                ,EXTRACT(YEAR FROM bm_cal_get_slot_date(?)) as open_year
                                                ,EXTRACT(WEEK FROM bm_cal_get_slot_date(?)) as open_week
                                                ,WEEKDAY(bm_cal_get_slot_date(?)) as week_day'
                                        ,array(1,100,1,1,1))
                         ->fetch();
        
        $openingDate = $dateResult['open_date'];
        $closingDate = $dateResult['close_date'];
        $openWeek    = $dateResult['open_week'];
        $openYear    = $dateResult['open_year'];
        $weekDay     = (int) $dateResult['week_day'];
        
        
        $db->executeQuery('CALL bm_schedule_remove_booking_mv(?,?,?)',array($scheduleID,$openingDate,$closingDate));
        
        
         $resultSTH = $db->executeQuery('SELECT * 
                                        FROM `bookings_agg_mv` 
                                        WHERE `schedule_id` = ? 
                                        AND `cal_week` = ? 
                                        AND `cal_year` = ?',array($scheduleID,$openWeek,$openYear));

        
        $result = $resultSTH->fetch();
        $dayCount[$weekDay] = $dayCount[$weekDay] - 1;
        
        $this->assertEquals($dayCount[1],$result['cal_sun']);
        $this->assertEquals($dayCount[2],$result['cal_mon']);
        $this->assertEquals($dayCount[3],$result['cal_tue']);
        $this->assertEquals($dayCount[4],$result['cal_wed']);
        $this->assertEquals($dayCount[5],$result['cal_thu']);
        $this->assertEquals($dayCount[6],$result['cal_fri']);
        $this->assertEquals($dayCount[7],$result['cal_sat']);
    }
    
}