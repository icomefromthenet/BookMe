DELIMITER ;

-- -----------------------------------------------------
-- Data for table `timeslots`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `timeslots` (`timeslot_id`, `timeslot_length`) VALUES (NULL, 15);
INSERT INTO `timeslots` (`timeslot_id`, `timeslot_length`) VALUES (NULL, 30);
INSERT INTO `timeslots` (`timeslot_id`, `timeslot_length`) VALUES (NULL, 45);
INSERT INTO `timeslots` (`timeslot_id`, `timeslot_length`) VALUES (NULL, 60);
INSERT INTO `timeslots` (`timeslot_id`, `timeslot_length`) VALUES (NULL, 90);
INSERT INTO `timeslots` (`timeslot_id`, `timeslot_length`) VALUES (NULL, 120);

COMMIT;


-- -----------------------------------------------------
-- Data for table `ints`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `ints` (`i`) VALUES (0);
INSERT INTO `ints` (`i`) VALUES (1);
INSERT INTO `ints` (`i`) VALUES (2);
INSERT INTO `ints` (`i`) VALUES (3);
INSERT INTO `ints` (`i`) VALUES (4);
INSERT INTO `ints` (`i`) VALUES (5);
INSERT INTO `ints` (`i`) VALUES (6);
INSERT INTO `ints` (`i`) VALUES (7);
INSERT INTO `ints` (`i`) VALUES (8);
INSERT INTO `ints` (`i`) VALUES (9);

COMMIT;

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
VALUES (NULL,'mygroup2',CAST(NOW() AS DATE),CAST((NOW()+ INTERVAL 7 DAY) AS DATE));

-- Past
INSERT INTO `schedule_groups` (`group_id`,`group_name`,`valid_from`,`valid_to`) 
VALUES (NULL,'mygroup3',CAST((NOW() - INTERVAL 7 DAY) AS DATE),CAST((NOW()+ INTERVAL 7 DAY) AS DATE));

-- future
INSERT INTO `schedule_groups` (`group_id`,`group_name`,`valid_from`,`valid_to`) 
VALUES (NULL,'mygroup4',CAST((NOW() + INTERVAL 7 DAY) AS DATE),CAST((NOW() + INTERVAL 14 DAY) AS DATE));

-- open date
INSERT INTO `schedule_groups` (`group_id`,`group_name`,`valid_from`,`valid_to`) 
VALUES (NULL,'mygroup5',CAST(NOW() AS DATE), DATE('3000-01-01'));


COMMIT;

-- -----------------------------------------------------
-- Data for table `schedules `
-- -----------------------------------------------------
START TRANSACTION;

-- relates to mygroup2 -- Active from today
INSERT INTO `schedules` (`schedule_id`,`timeslot_id`,`open_from`,`closed_on`,`schedule_group_id`,`membership_id`) 
VALUES (NULL,1,CAST(NOW() AS DATE),CAST((NOW() + INTERVAL 7 DAY) AS DATE),1,1);

-- relate to mygroup5  Active in the future
INSERT INTO `schedules` (`schedule_id`,`timeslot_id`,`open_from`,`closed_on`,`schedule_group_id`,`membership_id`) 
VALUES (NULL,1,CAST((NOW()+ INTERVAL 2 YEAR) AS DATE),CAST((NOW() + INTERVAL 3 YEAR) AS DATE),4,1);

-- relate to mygroup 3  Active in past
INSERT INTO `schedules` (`schedule_id`,`timeslot_id`,`open_from`,`closed_on`,`schedule_group_id`,`membership_id`) 
VALUES (NULL,1,CAST((NOW() - INTERVAL 4 DAY) AS DATE),CAST((NOW() - INTERVAL 1 DAY) AS DATE),2,1);

-- relate to mygroup 3 Active but started in past
INSERT INTO `schedules` (`schedule_id`,`timeslot_id`,`open_from`,`closed_on`,`schedule_group_id`,`membership_id`) 
VALUES (NULL,1,CAST((NOW() - INTERVAL 4 DAY) AS DATE),CAST((NOW() + INTERVAL 3 DAY) AS DATE),2,1);


COMMIT;