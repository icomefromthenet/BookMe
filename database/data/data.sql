DELIMITER ;

-- -----------------------------------------------------
-- Data for table `schedule members`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `schedule_membership` (`membership_id`,`registered_date`) VALUES (NULL,NOW());
INSERT INTO `schedule_membership` (`membership_id`,`registered_date`) VALUES (NULL,NOW());
INSERT INTO `schedule_membership` (`membership_id`,`registered_date`) VALUES (NULL,NOW());
INSERT INTO `schedule_membership` (`membership_id`,`registered_date`) VALUES (NULL,NOW());
INSERT INTO `schedule_membership` (`membership_id`,`registered_date`) VALUES (NULL,NOW());

COMMIT;


-- -----------------------------------------------------
-- Data for table `schedule groups`
-- -----------------------------------------------------
START TRANSACTION;

-- Current
INSERT INTO `schedule_groups` (`group_id`,`group_name`,`valid_from`,`valid_to`) 
VALUES (1,'mygroup2',CAST(NOW() AS DATE),CAST((NOW()+ INTERVAL 7 DAY) AS DATE));

-- Past
INSERT INTO `schedule_groups` (`group_id`,`group_name`,`valid_from`,`valid_to`) 
VALUES (2,'mygroup3',CAST((NOW() - INTERVAL 7 DAY) AS DATE),CAST((NOW()+ INTERVAL 7 DAY) AS DATE));

-- future
INSERT INTO `schedule_groups` (`group_id`,`group_name`,`valid_from`,`valid_to`) 
VALUES (3,'mygroup4',CAST((NOW() + INTERVAL 7 DAY) AS DATE),CAST((NOW() + INTERVAL 14 DAY) AS DATE));

-- open date
INSERT INTO `schedule_groups` (`group_id`,`group_name`,`valid_from`,`valid_to`) 
VALUES (4,'mygroup5',CAST(NOW() AS DATE), DATE('3000-01-01'));

-- Used in schedule group retire test
INSERT INTO `schedule_groups` (`group_id`,`group_name`,`valid_from`,`valid_to`) 
VALUES (5,'mygrouptest6',CAST(NOW() AS DATE), DATE('3000-01-01'));

-- Used in schedule group retire test
INSERT INTO `schedule_groups` (`group_id`,`group_name`,`valid_from`,`valid_to`) 
VALUES (6,'mygrouptest8',CAST(NOW() AS DATE), DATE('3000-01-01'));

-- Used in schedule group removal test
INSERT INTO `schedule_groups` (`group_id`,`group_name`,`valid_from`,`valid_to`) 
VALUES (7,'mygrouptest9',CAST((NOW() + INTERVAL + 1 DAY) AS DATE), DATE('3000-01-01'));

COMMIT;

-- -----------------------------------------------------
-- Data for table `schedules `
-- -----------------------------------------------------
START TRANSACTION;

-- relates to mygroup2 -- Active from today
INSERT INTO `schedules` (`schedule_id`,`open_from`,`closed_on`,`schedule_group_id`,`membership_id`) 
VALUES (1,CAST(NOW() AS DATE),CAST((NOW() + INTERVAL 7 DAY) AS DATE),1,1);

-- relate to mygroup5  Active in the future
INSERT INTO `schedules` (`schedule_id`,`open_from`,`closed_on`,`schedule_group_id`,`membership_id`) 
VALUES (2,CAST((NOW()+ INTERVAL 2 YEAR) AS DATE),CAST((NOW() + INTERVAL 3 YEAR) AS DATE),4,1);

-- relate to mygroup 3  Active in past
INSERT INTO `schedules` (`schedule_id`,`open_from`,`closed_on`,`schedule_group_id`,`membership_id`) 
VALUES (3,CAST((NOW() - INTERVAL 4 DAY) AS DATE),CAST((NOW() - INTERVAL 1 DAY) AS DATE),2,1);

-- relate to mygroup 3 Active but started in past
INSERT INTO `schedules` (`schedule_id`,`open_from`,`closed_on`,`schedule_group_id`,`membership_id`) 
VALUES (4,CAST((NOW() - INTERVAL 4 DAY) AS DATE),CAST((NOW() + INTERVAL 3 DAY) AS DATE),2,1);

-- Test for the retirement method so don't use in other tests
INSERT INTO `schedules` (`schedule_id`,`open_from`,`closed_on`,`schedule_group_id`,`membership_id`) 
VALUES (5,CAST((NOW() - INTERVAL 4 DAY) AS DATE),CAST(NOW() AS DATE),1,1);

-- Test for the retirement method so don't use in other tests
INSERT INTO `schedules` (`schedule_id`,`open_from`,`closed_on`,`schedule_group_id`,`membership_id`) 
VALUES (6,CAST((NOW() - INTERVAL 4 DAY) AS DATE),CAST(NOW() AS DATE),6,1);


COMMIT;