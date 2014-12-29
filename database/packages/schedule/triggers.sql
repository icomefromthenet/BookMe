-- -----------------------------------------------------
-- Triggers for package
-- -----------------------------------------------------
DELIMITER $$


-- -----------------------------------------------------
-- trigger bm_schedule_group_audit_insert
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS `bm_schedule_group_audit_insert`$$

CREATE TRIGGER `bm_schedule_group_audit_insert` AFTER INSERT ON `schedule_groups`
FOR EACH ROW
INSERT INTO audit_schedule_groups (`change_seq`,`group_id`,`group_name`,`valid_from`,`valid_to`,`changed_by`,`action`,`change_time`) 
VALUES (NULL,NEW.group_id,NEW.group_name,NEW.valid_from,NEW.valid_to,USER(),'I',NOW());
$$

-- -----------------------------------------------------
-- trigger bm_schedule_group_audit_update
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS `bm_schedule_group_audit_update`$$

CREATE TRIGGER `bm_schedule_group_audit_update` AFTER UPDATE ON `schedule_groups`
FOR EACH ROW
INSERT INTO audit_schedule_groups (`change_seq`,`group_id`,`group_name`,`valid_from`,`valid_to`,`changed_by`,`action`,`change_time`) 
VALUES (NULL,NEW.group_id,NEW.group_name,NEW.valid_from,NEW.valid_to,USER(),'U',NOW());
$$

-- -----------------------------------------------------
-- trigger bm_schedule_group_audit_delete
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS `bm_schedule_group_audit_delete`$$

CREATE TRIGGER `bm_schedule_group_audit_delete` AFTER DELETE ON `schedule_groups`
FOR EACH ROW
INSERT INTO audit_schedule_groups (`change_seq`,`group_id`,`group_name`,`valid_from`,`valid_to`,`changed_by`,`action`,`change_time`) 
VALUES (NULL,OLD.group_id,OLD.group_name,OLD.valid_from,OLD.valid_to,USER(),'D',NOW());
$$



-- -----------------------------------------------------
-- trigger bm_schedule_bookings_mv_insert
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS `bm_schedule_bookings_mv_insert`$$

CREATE TRIGGER `bm_schedule_bookings_mv_insert` AFTER INSERT ON `bookings`
FOR EACH ROW

CALL bm_schedule_add_booking_mv (NEW.`schedule_id`,NEW.`starting_date`, NEW.`closing_date`);

$$

-- -----------------------------------------------------
-- trigger bm_schedule_bookings_mv_delete
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS `bm_schedule_bookings_mv_delete`$$

CREATE TRIGGER `bm_schedule_bookings_mv_delete` AFTER DELETE ON `bookings`
FOR EACH ROW

CALL bm_schedule_remove_booking_mv (OLD.`schedule_id`,OLD.`starting_date`, OLD.`closing_date`);

$$