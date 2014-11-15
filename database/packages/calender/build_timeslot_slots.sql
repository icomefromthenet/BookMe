-- -----------------------------------------------------
-- procedure bm_calendar_build_timeslot_slots
-- -----------------------------------------------------
DELIMITER $$
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

