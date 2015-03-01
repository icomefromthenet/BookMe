-- -----------------------------------------------------
-- procedures for maxbook rule package
-- -----------------------------------------------------
DELIMITER $$

-- -----------------------------------------------------
-- procedure bm_rules_maxbook_add_rule
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_maxbook_add_rule`$$

CREATE PROCEDURE `bm_rules_maxbook_add_rule`( IN ruleName VARCHAR(45)
	                                    , IN validFrom DATE
										, IN validTo DATE
										, IN calendarType VARCHAR(45)
										, IN maxBookingNumber INT
										, OUT newRuleID INT )
BEGIN
	-- Create the debug table
	
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log('bm_rules_maxbook_add_rule');
	END IF;



	-- Assign defaults and check validity range
	
	IF validTo IS NULL THEN
		SET validTo = DATE('3000-01-01');
	ELSE 
		
		IF utl_is_valid_date_range(validFrom,validTo) = 0 THEN 
			SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'Validity period is and invalid range';
		END IF;
		
		SET validTo = validTo + INTERVAL 1 DAY;
		
	END IF;
	
	IF maxBookingNumber < 0 OR maxBookingNumber = 0 THEN
	    SIGNAL SQLSTATE '45000'
	    SET MESSAGE_TEXT = 'Max Booking Number must be gt 0';
	END IF;
	
	IF bm_rules_is_valid_calendar_type(calendarType) = false THEN
	    SIGNAL SQLSTATE '45000'
	    SET MESSAGE_TEXT = 'Calendar Type must be one of the following::day,week,month,year';
	END IF;
	
	
	-- insert into common rules table
	INSERT INTO `rules` (`rule_id`,`rule_name`,`rule_type`,`rule_repeat`,`valid_from`,`valid_to`,`rule_duration`)
	VALUES (NULL,ruleName,'maxbook','runtime',validFrom,validTo,0);
	SET newRuleID = LAST_INSERT_ID();
	IF newRuleID = 0 OR newRuleID IS NULL THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Unable to insert common padding rule';
	END IF;
	
	-- insert rule into concrete table
	INSERT INTO `rules_maxbook` (`rule_id`,`rule_name`,`rule_type`,`rule_repeat`,`valid_from`,`valid_to`,`max_bookings`,`calendar_period`)
	VALUES (newRuleID,ruleName,'maxbook','runtime',validFrom,validTo,maxBookingNumber,calendarType);
    IF ROW_COUNT() = 0 THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Unable to insert concrete maxbook rule';
	END IF;

	IF @bm_debug = true THEN
		CALL util_proc_log(concat('Inserted new rule at:: *',ifnull(newRuleID,'NULL')));
	
		CALL util_proc_cleanup('finished procedure bm_rules_maxbook_add_rule');
	END IF;


END$$


-- -----------------------------------------------------
-- procedure bm_rules_maxbook
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_maxbook`$$

CREATE PROCEDURE `bm_rules_maxbook`( IN scheduleID INT
 									,IN openTimeslotSlotID INT
                                    ,IN closetimeslotSlotID INT)
BEGIN
	
	DECLARE ruleID INT;
	DECLARE maxBookings INT;
	DECLARE calPeriod VARCHAR(45);
	
	DECLARE l_last_row_fetched INT DEFAULT 0;
	
	DECLARE rulesCursor CURSOR FOR 
		SELECT `vw`.`rule_id`, `mb`.`max_bookings`, `mb`.`calendar_period`
		FROM `schedules_rules_vw` vw
		JOIN `rules_maxbook` mb ON `mb`.`rule_id` = `vw`.`rule_id`
		WHERE `vw`.`rule_type` = 'maxbook'
		AND `vw`.`schedule_id` = scheduleID;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET l_last_row_fetched=1;

	-- create the result table
	CALL bm_rules_maxbook_create_tmp_table(openTimeslotSlotID,closetimeslotSlotID);

	
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log('bm_rules_maxbook');
	END IF;
	
	SET l_last_row_fetched=0;
	OPEN rulesCursor;
		cursor_loop:LOOP

		FETCH rulesCursor INTO ruleID,maxBookings,calPeriod;
		
		IF l_last_row_fetched=1 THEN
			LEAVE cursor_loop;
		END IF;
		
		IF @bm_debug = true THEN	
				CALL util_proc_log(concat('Processing Maxbook rule for schedule::',scheduleID
										  , 'for rule::',ruleID, ' maxbook::',maxBookings,' calPeriod::',calPeriod));
		END IF;
		
		
		CASE calPeriod
	    	WHEN 'day'  THEN CALL bm_rules_maxbook_cal_day(scheduleID,maxBookings);
	    	WHEN 'week' THEN CALL bm_rules_maxbook_cal_week(scheduleID,maxBookings);
	    	WHEN 'month'THEN CALL bm_rules_maxbook_cal_month(scheduleID,maxBookings);
	    	WHEN 'year' THEN CALL bm_rules_maxbook_cal_year(scheduleID,maxBookings);
	    	ELSE 
	    		SIGNAL SQLSTATE '45000'
				SET MESSAGE_TEXT = 'Slot length must be divide day evenly';
		END CASE;

		END LOOP cursor_loop;
	CLOSE rulesCursor;
	SET l_last_row_fetched=0;
	
	
	IF @bm_debug = true THEN
		CALL util_proc_cleanup('bm_rules_maxbook');
	END IF;

END;
$$


-- -----------------------------------------------------
-- procedure bm_rules_maxbook_create_tmp_table
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_maxbook_create_tmp_table`$$

CREATE PROCEDURE `bm_rules_maxbook_create_tmp_table`(IN openTimeslotSlotID INT,IN closeTimeslotSlotID INT)
BEGIN

	/*
	* This procedure is used to create the temporary global table that will be used to
	* join onto the master schedule this temp table contains the same timeslotSlots
	* that schedule would. We Use a range of TimeslotSlotID's making the assumption that the
	* timeslot slots are grouped into non overlapping sequences.
	*/

	IF openTimeslotSlotID > closeTimeslotSlotID THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Clsoing timeslotSlot must proceed the opening slot id';
	END IF;


	DROP TEMPORARY TABLE IF EXISTS `schedule_maxbool_slots`;
	CREATE TEMPORARY TABLE `schedule_maxbool_slots` (
		`timeslot_slot_id` INT NOT NULL PRIMARY KEY,
		`m` TINYINT NULL COMMENT 'month of the year',
  		`d` TINYINT NULL COMMENT 'numeric date part',
		`y` SMALLINT NULL COMMENT 'year where date occurs',
		`w` TINYINT NULL COMMENT 'week number in the year',
		`cal_date` DATE NULL COMMENT 'CAL date of this row'
		
		`has_maxed` TINYINT DEFAULT 0,
		
		CONSTRAINT `fk_maxbook_slots_1`
    	FOREIGN KEY (`timeslot_slot_id`)
    	REFERENCES `timeslot_slots` (`timeslot_slot_id`)
	  	ON DELETE NO ACTION
    	ON UPDATE NO ACTION
    	
  	) ENGINE=MEMORY;
	
	-- If a timeslot_slot crosses a day boundary, it will be counted in the cal day of its opening slot value.
	
	INSERT INTO `schedule_maxbool_slots` (`timeslot_slot_id`,`y`,`m`,`d`,`w`,cal_date,`has_maxed`)
	SELECT `s`.`timeslot_slot_id`, extract(year from sl.cal_date), extract(month from sl.cal_date), extract(day from sl.cal_date),extract(week from sl.cal_date),date(sl.cal_date),0
	FROM `timeslot_slots` s
	JOIN slots sl on `sl`.`slot_id` = `s`.`opening_slot_id`
	WHERE `s`.`timeslot_slot_id` >= openTimeslotSlotID
	AND `s`.`timeslot_slot_id` <= closeTimeslotSlotID;

END;
$$


-- -----------------------------------------------------
-- procedure bm_rules_maxbook_cal_day
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_maxbook_cal_day`$$

CREATE PROCEDURE `bm_rules_maxbook_cal_day`(IN scheduleID INT,IN maxBookNum INT)
BEGIN
	DECLARE calDate DATE;
	DECLARE numberBooked INT DEFAULT 0;
	DECLARE l_last_row_fetched INT DEFAULT 0;
	-- timeslot loop vars
	DECLARE timeslots_cursor CURSOR FOR 
		SELECT `cal_date`
		FROM schedule_maxbool_slots 
		GROUP BY `cal_date`;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET l_last_row_fetched=1;

	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log('bm_rules_maxbook_cal_day');
	END IF;
 
	-- iterate over day in tmp table and check if maxed the bookings	
	SET l_last_row_fetched=0;
	OPEN timeslots_cursor;
		cursor_loop:LOOP

		FETCH timeslots_cursor INTO calDate;
		
		IF l_last_row_fetched=1 THEN
			LEAVE cursor_loop;
		END IF;
		
		IF @bm_debug = true THEN	
				CALL util_proc_log(concat('Looking for bookings on date ',extract(day from calDate),'/',extract(month from calDate),'/',extract(year from calDate)));
		END IF;
		
		-- count the number of bookings for this date
		SET numberBooked = (SELECT count(`b`.`booking_id`)
							FROM bookings b 
							WHERE `b`.`schedule_id`  = scheduleID 
							AND   date(`b`.`starting_date`) = calDate);
							
		
		-- do bulk update of all slots for this day
		IF numberBooked > maxBookNum THEN
			
			UPDATE `schedule_maxbool_slots` SET has_maxed = 1
			WHERE `cal_date` = `cal_date`;
			
			IF @bm_debug = true THEN	
				CALL util_proc_log(concat('Updated ',ROW_COUNT(),' number of timeslots in schedule_maxbool_slots table'));
			END IF;
			
		END IF;

		END LOOP cursor_loop;
	CLOSE timeslots_cursor;
	SET l_last_row_fetched=0;

	
	IF @bm_debug = true THEN
		CALL util_proc_cleanup('finished procedure bm_rules_maxbook_cal_day');
	END IF;


END;
$$


-- -----------------------------------------------------
-- procedure bm_rules_maxbook_cal_week
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_maxbook_cal_week`$$

CREATE PROCEDURE `bm_rules_maxbook_cal_week`()
BEGIN
	DECLARE weekValue INT DEFAULT 0;
	DECLARE yearValue INT DEFAULT 0;
	DECLARE numberBooked INT DEFAULT 0;
	DECLARE l_last_row_fetched INT DEFAULT 0;
	-- timeslot loop vars
	DECLARE timeslots_cursor CURSOR FOR 
		SELECT `w`,`y` 
		FROM schedule_maxbool_slots 
		GROUP BY `w`,`y`;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET l_last_row_fetched=1;

	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log('bm_rules_maxbook_cal_week');
	END IF;
 
	-- iterate over day in tmp table and check if maxed the bookings	
	SET l_last_row_fetched=0;
	OPEN timeslots_cursor;
		cursor_loop:LOOP

		FETCH timeslots_cursor INTO weekValue,yearValue;
		
		IF l_last_row_fetched=1 THEN
			LEAVE cursor_loop;
		END IF;
		
		IF @bm_debug = true THEN	
				CALL util_proc_log(concat('Looking for bookings on week ',weekValue,'/',yearValue));
		END IF;
		
		-- count the number of bookings for this date
		SET numberBooked = (SELECT (ifnull(sum(`c`.`cal_sun`),0) 
									   + ifnull(sum(`c`.`cal_mon`),0) 
								       + ifnull(sum(`c`.`cal_tue`),0)
								       + ifnull(sum(`c`.`cal_wed`),0) 
									   + ifnull(sum(`c`.`cal_thu`),0) 
								       + ifnull(sum(`c`.`cal_fri`),0)
								       + ifnull(sum(`c`.`cal_sat`),0)
								) as bcount
							FROM  bookings_agg_mv c 
							WHERE `c`.`cal_year` = yearValue
							AND `c`.`cal_week` = weekValue
							AND `c`.`schedule_id` = schedule_id);
		
		-- do bulk update of all slots for this day
		IF numberBooked > maxBookNum THEN
			
			UPDATE `schedule_maxbool_slots` SET has_maxed = 1
			WHERE  `w` = weekValue
			AND    `y` = yearValue AND    `has_maxed` != 1;
			
			IF @bm_debug = true THEN	
				CALL util_proc_log(concat('Updated ',ROW_COUNT(),' number of timeslots in schedule_maxbool_slots table'));
			END IF;
			
		END IF;

		END LOOP cursor_loop;
	CLOSE timeslots_cursor;
	SET l_last_row_fetched=0;

	
	IF @bm_debug = true THEN
		CALL util_proc_cleanup('finished procedure bm_rules_maxbook_cal_week');
	END IF;

END;
$$


-- -----------------------------------------------------
-- procedure bm_rules_maxbook_cal_month
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_maxbook_cal_month`$$

CREATE PROCEDURE `bm_rules_maxbook_cal_month`()
BEGIN
	DECLARE monthValue INT DEFAULT 0;
	DECLARE yearValue INT DEFAULT 0;
	DECLARE numberBooked INT DEFAULT 0;
	DECLARE l_last_row_fetched INT DEFAULT 0;
	-- timeslot loop vars
	DECLARE timeslots_cursor CURSOR FOR 
		SELECT `m`,`y` 
		FROM schedule_maxbool_slots 
		GROUP BY `m`,`y`;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET l_last_row_fetched=1;

	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log('bm_rules_maxbook_cal_month');
	END IF;
 
	-- iterate over day in tmp table and check if maxed the bookings	
	SET l_last_row_fetched=0;
	OPEN timeslots_cursor;
		cursor_loop:LOOP

		FETCH timeslots_cursor INTO monthValue,yearValue;
		
		IF l_last_row_fetched=1 THEN
			LEAVE cursor_loop;
		END IF;
		
		IF @bm_debug = true THEN	
				CALL util_proc_log(concat('Looking for bookings on month ',monthValue,'/',yearValue));
		END IF;
		
		-- count the number of bookings for this date
		SET numberBooked = (SELECT (ifnull(sum(`c`.`cal_sun`),0) 
									   + ifnull(sum(`c`.`cal_mon`),0) 
								       + ifnull(sum(`c`.`cal_tue`),0)
								       + ifnull(sum(`c`.`cal_wed`),0) 
									   + ifnull(sum(`c`.`cal_thu`),0) 
								       + ifnull(sum(`c`.`cal_fri`),0)
								       + ifnull(sum(`c`.`cal_sat`),0)
								) as bcount
							FROM  bookings_monthly_agg_vw c 
							WHERE `c`.`y` = yearValue
							AND `c`.`m` = monthValue
							AND `c`.`schedule_id` = schedule_id);
		
		-- do bulk update of all slots for this day
		IF numberBooked > maxBookNum THEN
			
			UPDATE `schedule_maxbool_slots` SET has_maxed = 1
			WHERE  `m` = monthValue
			AND    `y` = yearValue AND `has_maxed` != 1;
			
			IF @bm_debug = true THEN	
				CALL util_proc_log(concat('Updated ',ROW_COUNT(),' number of timeslots in schedule_maxbool_slots table'));
			END IF;
			
		END IF;

		END LOOP cursor_loop;
	CLOSE timeslots_cursor;
	SET l_last_row_fetched=0;

	
	IF @bm_debug = true THEN
		CALL util_proc_cleanup('finished procedure bm_rules_maxbook_cal_month');
	END IF;

END;
$$


-- -----------------------------------------------------
-- procedure bm_rules_maxbook_cal_year
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_maxbook_cal_year`$$

CREATE PROCEDURE `bm_rules_maxbook_cal_year`()
BEGIN
	DECLARE yearValue INT DEFAULT 0;
	DECLARE numberBooked INT DEFAULT 0;
	DECLARE l_last_row_fetched INT DEFAULT 0;
	-- timeslot loop vars
	DECLARE timeslots_cursor CURSOR FOR 
		SELECT `y` 
		FROM schedule_maxbool_slots 
		GROUP BY `y`;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET l_last_row_fetched=1;

	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log('bm_rules_maxbook_cal_year');
	END IF;
 
	-- iterate over day in tmp table and check if maxed the bookings	
	SET l_last_row_fetched=0;
	OPEN timeslots_cursor;
		cursor_loop:LOOP

		FETCH timeslots_cursor INTO yearValue;
		
		IF l_last_row_fetched=1 THEN
			LEAVE cursor_loop;
		END IF;
		
		IF @bm_debug = true THEN	
				CALL util_proc_log(concat('Looking for bookings on year ',yearValue));
		END IF;
		
		-- count the number of bookings for this date
		SET numberBooked = (SELECT (ifnull(sum(`c`.`cal_sun`),0) 
									   + ifnull(sum(`c`.`cal_mon`),0) 
								       + ifnull(sum(`c`.`cal_tue`),0)
								       + ifnull(sum(`c`.`cal_wed`),0) 
									   + ifnull(sum(`c`.`cal_thu`),0) 
								       + ifnull(sum(`c`.`cal_fri`),0)
								       + ifnull(sum(`c`.`cal_sat`),0)
								) as bcount
							FROM  bookings_yearly_agg_vw c 
							-- join all bookings that are encompased by this days slots for schedule x
							WHERE `c`.`y` = yearValue
							AND `c`.`schedule_id` = schedule_id);
		
		-- do bulk update of all slots for this day
		IF numberBooked > maxBookNum THEN
			
			UPDATE `schedule_maxbool_slots` SET has_maxed = 1
			WHERE  `y` = yearValue AND  `has_maxed` != 1;
			
			IF @bm_debug = true THEN	
				CALL util_proc_log(concat('Updated ',ROW_COUNT(),' number of timeslots in schedule_maxbool_slots table'));
			END IF;
			
		END IF;

		END LOOP cursor_loop;
	CLOSE timeslots_cursor;
	SET l_last_row_fetched=0;

	
	IF @bm_debug = true THEN
		CALL util_proc_cleanup('finished procedure bm_rules_maxbook_cal_year');
	END IF;

END;
$$