-- -----------------------------------------------------
-- procedures for package
-- -----------------------------------------------------
DELIMITER $$

-- -----------------------------------------------------
-- procedure bm_calendar_addtimeslot
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `bm_calendar_add_timeslot`$$

CREATE PROCEDURE `bm_calendar_add_timeslot` (IN slotLength INT,OUT timeslotID INT)
BEGIN
	
	IF slotLength <= 1 AND slotLength > (60*24) THEN 
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Slot must be between 1 minutes and 1440 (day) in length';
	END IF;
	
	-- unique index on length column stop duplicates
    -- trigger should fire that record this addition onto audit table
	INSERT INTO timeslots (timeslot_id,timeslot_length) values (NULL,slotLength);

    -- calculate this timeslots , slot groups. 
	SET timeslotID = LAST_INSERT_ID();
	
	CALL bm_calendar_build_timeslot_slots(timeslotID,slotLength);

END$$

-- -----------------------------------------------------
-- procedure bm_calendar_remove_timeslot
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `bm_calendar_remove_timeslot`$$

CREATE PROCEDURE `bm_calendar_remove_timeslot` (IN slotID INT)
BEGIN
	
	-- remove the slot form the relation
	DELETE FROM timeslot_slots WHERE timeslot_id = slotID;
	
	-- if slots not removed above the fk relation will
	-- case this delete to error
	DELETE FROM timeslots WHERE timeslot_id = slotID;
    
    
   	IF ROW_COUNT() = 0 THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Unable to remove the timeslot could be unknown slot ID was given';
	END IF;
	

END$$

-- -----------------------------------------------------
-- procedure bm_calendar_build_timeslot_slots
-- -----------------------------------------------------

DROP procedure IF EXISTS `bm_calendar_build_timeslot_slots`$$

CREATE PROCEDURE `bm_calendar_build_timeslot_slots` (IN timeslotID INT
													,IN timeslotLength INT )
BEGIN
		
	-- Need to group our slots and insert results into group cache table
    -- As out slot tabe has sequential id we can use this to build buckets
	INSERT INTO timeslot_slots (timeslot_slot_id,opening_slot_id,closing_slot_id,timeslot_id)  
		SELECT NULL
              ,min(a.slot_id) as slot_open_id	
			  ,max(a.slot_id) as slot_close_id
              ,timeslotID
        FROM slots a
		GROUP BY ceil(a.slot_id/timeslotLength);	
END$$

-- -----------------------------------------------------
-- procedure bm_calendar_is_valid_length
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_calendar_is_valid_length`$$

CREATE PROCEDURE `bm_calendar_is_valid_length`(IN x INT)
BEGIN
	DECLARE maxPeriod INT DEFAULT 10;
		
	-- x is with valid range 
	IF x < 1 OR x > maxPeriod THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Minimum calendar year is 1 and maxium is 10';
	END IF;
	
END$$