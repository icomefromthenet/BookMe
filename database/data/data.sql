-- -----------------------------------------------------
-- Prep
-- -----------------------------------------------------
DELIMITER ;

CALL util_proc_setup();

START TRANSACTION;



-- -----------------------------------------------------
-- Fake Membership
-- -----------------------------------------------------

CALL bm_add_membership(@membershipID1);
CALL bm_add_membership(@membershipID2);
CALL bm_add_membership(@membershipID3);
CALL bm_add_membership(@membershipID4);
CALL bm_add_membership(@membershipID5);
CALL bm_add_membership(@membershipID6);



-- -----------------------------------------------------
-- Fake `schedule groups`
-- -----------------------------------------------------


-- Current
CALL bm_schedule_add_group('mygroup2',CAST(NOW() AS DATE),CAST((NOW()+ INTERVAL 7 DAY) AS DATE),@newScheduleGroupID1);

-- Past, force past date as setup method will reject
CALL bm_schedule_add_group('mygroup3',CAST(NOW() AS DATE),CAST((NOW()+ INTERVAL 7 DAY) AS DATE),@newScheduleGroupID2);
UPDATE schedule_groups set valid_from = CAST((NOW() - INTERVAL 7 DAY) AS DATE) WHERE group_id = 2;

-- future
CALL bm_schedule_add_group('mygroup4',CAST((NOW() + INTERVAL 7 DAY) AS DATE),CAST((NOW() + INTERVAL 14 DAY) AS DATE),@newScheduleGroupID3);

-- open date
CALL bm_schedule_add_group('mygroup5',CAST(NOW() AS DATE), DATE('3000-01-01'),@newScheduleGroupID4);

-- Used in schedule group retire test
CALL bm_schedule_add_group('mygrouptest6',CAST(NOW() AS DATE), DATE('3000-01-01'),@newScheduleGroupID5);

-- Used in schedule group retire test
CALL bm_schedule_add_group('mygrouptest8',CAST(NOW() AS DATE), DATE('3000-01-01'),@newScheduleGroupID6);

-- Used in schedule group removal test
CALL bm_schedule_add_group('mygrouptest9',CAST((NOW() + INTERVAL + 1 DAY) AS DATE), DATE('3000-01-01'),@newScheduleGroupID7);

-- Used for rules packages tests
CALL bm_schedule_add_group('myscheduleGroup1',CAST((NOW() + INTERVAL + 1 DAY) AS DATE), DATE('3000-01-01'),@newScheduleGroupID8);



-- -----------------------------------------------------
-- Data for table `schedules`
-- -----------------------------------------------------


-- relates to mygroup2 -- Active from today
CALL bm_schedule_add(@newScheduleGroupID1,@membershipID1,CAST(NOW() AS DATE),CAST((NOW() + INTERVAL 7 DAY) AS DATE),@schedule1);

-- relate to mygroup5  Active in the future
CALL bm_schedule_add(@newScheduleGroupID4,@membershipID1,CAST((NOW()+ INTERVAL 2 YEAR) AS DATE),CAST((NOW() + INTERVAL 3 YEAR) AS DATE),@schedule2);

-- relate to mygroup 3  Active in past
CALL bm_schedule_add(@newScheduleGroupID2,@membershipID1,CAST(NOW() AS DATE),CAST(NOW() AS DATE),@schedule3);
UPDATE schedules SET `open_from` = (`open_from` - INTERVAL 4 DAY), `closed_on` = (`closed_on` - INTERVAL 1 DAY) WHERE `schedule_id` = @schedule3;

-- relate to mygroup 3 Active but started in past
CALL bm_schedule_add(@newScheduleGroupID2,@membershipID1,CAST(NOW() AS DATE),CAST((NOW() + INTERVAL 3 DAY) AS DATE),@schedule4);
UPDATE schedules SET `open_from` = (`open_from` - INTERVAL 4 DAY) WHERE `schedule_id` = @schedule3;

-- Test for the retirement method so don't use in other tests
CALL bm_schedule_add(@newScheduleGroupID1,@membershipID1,CAST(NOW() AS DATE),CAST(NOW() AS DATE),@schedule5);
UPDATE schedules SET `open_from` = (`open_from` - INTERVAL 4 DAY) WHERE `schedule_id` = @schedule3;

-- Test for the retirement method so don't use in other tests
CALL bm_schedule_add(@newScheduleGroupID6,@membershipID1,CAST(NOW() AS DATE),CAST(NOW() AS DATE),@schedule6);
UPDATE schedules SET `open_from` = (`open_from` - INTERVAL 4 DAY) WHERE `schedule_id` = @schedule3;

-- Schedules used in Rules Package tests
CALL bm_schedule_add(@newScheduleGroupID1,@membershipID1,CAST(NOW() AS DATE),CAST((NOW() + INTERVAL 7 DAY) AS DATE),@schedule7);



-- -----------------------------------------------------
-- Data for table `rules`
-- -----------------------------------------------------

/*
-- Work Day (Inclusion rule) '9am-5pm monday-friday every day of month and every month for 1 year'
CALL bm_rules_add_repeat_rule('workday','inclusion'
                             ,'0','9-17','1-5','*','*'
                             ,DATE_FORMAT((NOW() - INTERVAL 1 YEAR),'%Y-01-01'),DATE_FORMAT(NOW() ,'%Y-12-31')
                             ,59,NOW(),DATE('3000-01-01')
                             ,NULL,@membershipID6
                             ,@newRuleID1);
*/

-- -----------------------------------------------------
-- Cleanup
-- -----------------------------------------------------

COMMIT;

CALL util_proc_cleanup('finished adding test data');