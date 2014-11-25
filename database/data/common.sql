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

