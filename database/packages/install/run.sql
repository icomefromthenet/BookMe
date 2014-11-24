-- -----------------------------------------------------
-- procedure bm_install_run
-- -----------------------------------------------------
DELIMITER $$
DROP procedure IF EXISTS `bm_install_run`$$

CREATE PROCEDURE `bm_install_run` (IN years INT)
BEGIN
	DECLARE timeslot_id INT;
	DECLARE timeslot_length INT;
	DECLARE l_last_row_fetched INT DEFAULT 0;
	-- timeslot loop vars
	DECLARE timeslots_cursor CURSOR FOR SELECT timeslot_id,timeslot_length FROM timeslots;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET l_last_row_fetched=1;
	
	
	-- set default number of calender years to generate
	IF (years = NULL) THEN
		SET years = 10;
	END IF;

	
	-- setup calender for 10 years
	CALL bm_calendar_setup_cal(years);

	-- setup slots for 10 years
	CALL bm_calender_setup_slots();


	-- buid timeslots found in table into group cache table
	SET l_last_row_fetched=0;
	OPEN timeslots_cursor;
		cursor_loop:LOOP

		FETCH timeslots_cursor INTO timeslot_id,timeslot_length;
		IF l_last_row_fetched=1 THEN
			LEAVE cursor_loop;
		END IF;

		-- build timeslot cache table for this timeslot		
		CALL util_debug_msg(@bm_debug,concat('build timeslots for ',ifnull(timeslot_id,'null'),' for length ',ifnull(timeslot_length,'null')));
		
		CALL bm_calendar_build_timeslot_slots(timeslot_id,timeslot_length);

		END LOOP cursor_loop;
	CLOSE timeslots_cursor;
	SET l_last_row_fetched=0;

	-- build inclusion rules

    -- build exclusion rules
END$$
