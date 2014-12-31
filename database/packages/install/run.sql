-- -----------------------------------------------------
-- Procedures for Install Package
-- -----------------------------------------------------

DELIMITER $$

-- -----------------------------------------------------
-- procedure bm_install_run
-- -----------------------------------------------------

DROP procedure IF EXISTS `bm_install_run`$$

CREATE PROCEDURE `bm_install_run` (IN years INT)
BEGIN
	DECLARE timeslotID INT;
	DECLARE timeslotLength INT;
	DECLARE l_last_row_fetched INT DEFAULT 0;
	-- timeslot loop vars
	DECLARE timeslots_cursor CURSOR FOR SELECT `timeslot_id`,`timeslot_length` FROM timeslots;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET l_last_row_fetched=1;
	
	-- Create the debug table
	IF @bm_debug = true THEN
		CALL util_proc_setup();
	END IF;
	
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

		FETCH timeslots_cursor INTO timeslotID,timeslotLength;
		IF l_last_row_fetched=1 THEN
			LEAVE cursor_loop;
		END IF;

		IF @bm_debug = true THEN
			CALL util_proc_log(concat('build timeslots for ',ifnull(timeslotID,'null'),' for length ',ifnull(timeslotLength,'null')));
		END IF;
		
		IF MOD((60*24),timeslotLength) > 0 THEN 
			SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'Slot length must be divide day evenly';
		END IF;
		
		CALL bm_calendar_build_timeslot_slots(timeslotID,timeslotLength);

		END LOOP cursor_loop;
	CLOSE timeslots_cursor;
	SET l_last_row_fetched=0;

	
    -- execute debug log cleanup
    IF @bm_debug = true THEN
    	CALL util_proc_cleanup('Finished Procedure bm_install_run()');
    END IF;
    
END$$



