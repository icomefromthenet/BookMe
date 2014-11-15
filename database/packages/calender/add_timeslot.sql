-- -----------------------------------------------------
-- procedure bm_calendar_addtimeslot
-- -----------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS `bm_calendar_add_timeslot`$$

CREATE PROCEDURE `bm_calendar_add_timeslot` (IN slotLength INT)
BEGIN
	DECLARE timeslotID INT;	

	START TRANSACTION;

	IF slotLength <= 1 AND slotLength > (60*24) THEN 
		 SELECT utl_raise_error('Slot must be between 1 minutes and 1440 (day) in length');
	END IF;
	
	-- unique index on length column stop duplicates
    -- trigger should fire that record this addition onto audit table
	INSERT INTO timeslots (timeslot_id,timeslot_length) values (NULL,slotLength);

    -- calculate this timeslots , slot groups. 
	SELECT LAST_INSERT_ID() INTO timeslotID;
	
	CALL bm_calendar_build_timeslot_slots(timeslotID,slotLength);

	COMMIT;


END$$