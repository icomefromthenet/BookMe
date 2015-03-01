-- -----------------------------------------------------
-- procedures for package
-- -----------------------------------------------------
DELIMITER $$

-- -----------------------------------------------------
-- procedure bm_add_membership
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_add_membership`$$

CREATE PROCEDURE `bm_add_membership` (OUT membershipID INT)
BEGIN
    
    INSERT INTO schedule_membership (membership_id,registered_date) values (NULL,NOW());
    SET membershipID = LAST_INSERT_ID();
    
    IF @bm_debug = true THEN
		CALL util_proc_log(concat('Inserted new membership at::',membershipID));
	END IF;	

END$$

-- -----------------------------------------------------
-- procedure bm_schedule_add_group
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_schedule_add_group`$$

CREATE PROCEDURE `bm_schedule_add_group` (IN groupName VARCHAR(100), IN validFrom DATE, IN validTo DATE, OUT groupID INT)
BEGIN

	-- table uses closed:open interval format
	
	IF (validTo IS NULL) THEN 
     	SET validTo = DATE('3000-01-01');
    ELSE 
		IF utl_is_valid_date_range(validFrom,validTo) = 0 THEN
			SIGNAL SQLSTATE '45000' 
			SET MESSAGE_TEXT = 'Date range is not valid';
		END IF;
	
    	SET validTo = (validTo + INTERVAL 1 DAY);
	END IF;


	
	-- add new group and fetch the assigned ID	
	-- trigger will update the audit table with insert 
	INSERT INTO schedule_groups (group_id,group_name,valid_from,valid_to) 
		VALUES (null,groupName,validFrom,validTo);
	
	SET groupID =  LAST_INSERT_ID();
	
	IF @bm_debug = true THEN
		CALL util_proc_log(concat('Inserted new schedule group at::',groupID,' name::',groupName));
	END IF;	

END$$


-- -----------------------------------------------------
-- procedure bm_schedule_retire_group
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_schedule_retire_group`$$

CREATE PROCEDURE `bm_schedule_retire_group` (IN groupID INT, IN validTo DATE)
BEGIN

	-- table used closed:open interval format
	SET validTo = (validTo + INTERVAL 1 DAY);


	-- assign new valid to date to retire this group
	-- verify that new validTo date is wihin the existsing
    -- validity range.
	UPDATE schedule_groups SET valid_to = validTo
	WHERE group_id = groupID 
	AND NOT EXISTS(
		-- if we have valid schedule we can't reitre the group
		-- until they too are retired
		SELECT 1 FROM schedules
	    WHERE schedule_group_id = groupID
	    AND closed_on > validTo
	    LIMIT 1) 
	-- can't reitire a group before it was valid
	AND valid_from < validTo
	-- cant retire a group on same date it originally had, no change here
	AND valid_to > validTo; 
		
	IF ROW_COUNT() = 0 THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Group not found or validTo date is not within original validity range';
	END IF;
	
	
	IF @bm_debug = true THEN
		CALL util_proc_log(concat('Retired a schedule group at::',groupID));
	END IF;	

END$$

-- -----------------------------------------------------
-- procedure bm_schedule_remove_group
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_schedule_remove_group`$$

CREATE PROCEDURE `bm_schedule_remove_group` (IN groupID INT)
BEGIN

	-- fk relation will stop groups from being removed
	-- that are in use by rules and schedules
	
	-- Used if you add a group to be active in the future
	-- but decide you don't need it any more and remove it before is becomes active.
	DELETE FROM schedule_groups 
	WHERE group_id = groupID
	AND valid_from > CAST(NOW() AS DATE);

	-- If you want to remove any group if its unsuded you
	-- can use a normal delete
	
	IF ROW_COUNT() = 0 THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Unable to remove group the ID given may not have been found or may be active group already';
	END IF;
	
	IF @bm_debug = true THEN
		CALL util_proc_log(concat('Removed schedule group at::',groupID));
	END IF;	


END$$

-- -----------------------------------------------------
-- procedure bm_schedule_add
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_schedule_add`$$

CREATE PROCEDURE `bm_schedule_add` (IN groupID INT
                                  , IN memberID INT
                                  , IN validFrom DATE
                                  , IN validTo DATE
                                  , OUT scheduleID INT)
BEGIN

	IF (validTo IS NULL) THEN 
      SET validTo = DATE('3000-01-01');
	ELSE 
	
		IF (utl_is_valid_date_range(validFrom,validTo) = 0) THEN
			SIGNAL SQLSTATE '45000' 
			SET MESSAGE_TEXT = 'Date range is not valid';
		END IF;

	 -- convert for closed:open interval format
		SET validTo = (validTo + INTERVAL 1 DAY);

	END IF;

	
	-- We require that the Schedule validity period be filled by the validity period of the group
	-- the schedule validity occurs within the validity period of the group.

	INSERT INTO `schedules` (`schedule_id`,`open_from`,`closed_on`,`schedule_group_id`,`membership_id`) 
	VALUES (NULL
								,validFrom
								,validTo
								,(SELECT gs.group_id 
	         FROM schedule_groups gs
	         WHERE gs.valid_from <= validFrom
	         AND gs.valid_to >= validTo
	       	 AND gs.group_id = groupID
	       )
	       ,memberID);
	
	SET scheduleID =  LAST_INSERT_ID();

	IF @bm_debug = true THEN
		CALL util_proc_log(concat('Inserted new schedule at::',scheduleID,' for member::',memberID));
	END IF;	

END;
$$

-- -----------------------------------------------------
-- procedure bm_schedule_retire
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_schedule_retire`$$

CREATE PROCEDURE `bm_schedule_retire` (IN scheduleID INT , IN validTo DATE)
BEGIN

	-- table uses closed:open interval period
	SET validTo = (validTo + INTERVAL 1 DAY);
	
	
	-- only going to retire a schedule if there a zero
	-- bookings occur after the end date
	UPDATE `schedules` s SET `s`.`closed_on` = validTo 
	WHERE NOT EXISTS (
		SELECT 1
		FROM `bookings` b
		WHERE `b`.`schedule_id`  = `s`.`schedule_id`
		AND   `b`.`closing_date` < validTo
	)
	AND `s`.`schedule_id` = scheduleID;
	

	IF ROW_COUNT() = 0 THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Schedule not found or validTo date is not within original validity range';
	END IF;
	
	IF @bm_debug = true THEN
		CALL util_proc_log(concat('Retired a schedule at::',groupID));
	END IF;	
	
END;
$$


-- -----------------------------------------------------
-- procedure bm_schedule_add_booking
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_schedule_add_booking`$$

CREATE PROCEDURE `bm_schedule_add_booking` (IN scheduleID INT
                                            ,IN openTimeslotSlotID INT
                                            ,IN closeTimeslotSlotID INT
                                           	,IN priorityRuleID INT)
BEGIN

	


	IF @bm_debug = true THEN
		CALL util_proc_log('bm_schedule_add_booking');
	END IF;	

END;
$$

-- -----------------------------------------------------
-- procedure bm_schedule_remove_booking
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_schedule_remove_booking`$$

CREATE PROCEDURE `bm_schedule_remove_booking` (IN scheduleID INT)
BEGIN


END;
$$

-- -----------------------------------------------------
-- procedure bm_schedule_change_booking
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_schedule_change_booking`$$

CREATE PROCEDURE `bm_schedule_change_booking` (IN scheduleID INT)
BEGIN


END;
$$



-- -----------------------------------------------------
-- procedure bm_schedule_add_booking_mv
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_schedule_add_booking_mv`$$

CREATE PROCEDURE `bm_schedule_add_booking_mv` (IN scheduleID INT, IN startingDate DATE, IN endingDate DATE)
BEGIN
 /*
  This procedure will saving agg booking counts over calendar week series.
  
  There are two general usecases 
  	1. Multi day length bookings
  	2. Short term bookings contained within single day.
 
  In the case of short term booking the start and end date be same (closed:closed) interval format
  so the agg will be added to that cal day in the cal week.
  
  With multi day bookings each day during that appointment will have a booking counting. 
  For example if this being used for short term rentals (each resource own schedule) this view is can
  be used to display which resources are in use over a calender period as each day the booking extends over 
  will be shown.
  
 */
 DECLARE calDate DATE;
 DECLARE calDay INT;
 DECLARE calWeek INT;
 DECLARE calMonth INT;
 DECLARE calYear INT;
 DECLARE dateDiff INT DEFAULT 0;
 DECLARE calSun INT DEFAULT 0;
 DECLARE calMon INT DEFAULT 0;
 DECLARE calTue INT DEFAULT 0;
 DECLARE calWed INT DEFAULT 0;
 DECLARE calThu INT DEFAULT 0;
 DECLARE calFri INT DEFAULT 0;
 DECLARE calSat INT DEFAULT 0;
 
 -- extract the date information
 IF startingDate > endingDate THEN
 		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Start date must come before end date';
 END IF;
 
 
 SET dateDiff = DATEDIFF(endingDate,startingDate) +1;
 
 counter: WHILE dateDiff > 0 DO
	 	
	 	SET calDate = startingDate + INTERVAL (dateDiff-1) DAY;
		 
		-- increment the correct day value
		SET calDay   = WEEKDAY(calDate);
		SET calWeek  = EXTRACT(WEEK  FROM calDate);
		SET calMonth = EXTRACT(MONTH FROM calDate);
		SET calYear  = EXTRACT(YEAR  FROM calDate);
		 	
		CASE calDay
			WHEN 1 THEN SET calSun = 1; 
			WHEN 2 THEN SET calMon = 1;
			WHEN 3 THEN SET calTue = 1;
			WHEN 4 THEN SET calWed = 1;
			WHEN 5 THEN SET calThu = 1;
			WHEN 6 THEN SET calFri = 1;
			WHEN 7 THEN SET calSat = 1;
		END CASE;
		
		-- insert row in the Materialized View. 
			 
		INSERT INTO `bookings_agg_mv` (schedule_id,cal_week,cal_month,cal_year,cal_sun
		,cal_mon,cal_tue,cal_wed,cal_thu,cal_fri,cal_sat,open_slot_id,close_slot_id) 
		VALUES (scheduleID,calWeek,calMonth
				,calYear,calSun,calMon
				,calTue,calWed,calThu,calFri,calSat
				,(SELECT `cw`.`open_slot_id` 
				  FROM calendar_weeks cw 
				  WHERE `cw`.`w` = calWeek AND `cw`.`y` = calYear)
				,(SELECT `cw`.`close_slot_id` 
				  FROM calendar_weeks cw 
				  WHERE `cw`.`w` = calWeek AND `cw`.`y` = calYear)
		)
		ON DUPLICATE KEY
		UPDATE cal_sun = cal_sun + calSun,
			   cal_mon = cal_mon + calMon,
			   cal_tue = cal_tue + calTue,
			   cal_wed = cal_wed + calWed,
			   cal_thu = cal_thu + calThu,
			   cal_fri = cal_fri + calFri,
			   cal_sat = cal_sat + calSat;
			        
	
	SET dateDiff = dateDiff -1;
	END WHILE counter;

END;
$$

-- -----------------------------------------------------
-- procedure bm_schedule_remove_booking_mv
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_schedule_remove_booking_mv`$$

CREATE PROCEDURE `bm_schedule_remove_booking_mv` (IN scheduleID INT, IN startingDate DATE, IN endingDate DATE)
BEGIN

 DECLARE calDate DATE;
 DECLARE calDay INT;
 DECLARE calWeek INT;
 DECLARE calMonth INT;
 DECLARE calYear INT;
 DECLARE dateDiff INT DEFAULT 0;
 DECLARE calSun INT DEFAULT 0;
 DECLARE calMon INT DEFAULT 0;
 DECLARE calTue INT DEFAULT 0;
 DECLARE calWed INT DEFAULT 0;
 DECLARE calThu INT DEFAULT 0;
 DECLARE calFri INT DEFAULT 0;
 DECLARE calSat INT DEFAULT 0;
 
 -- extract the date information
 IF startingDate > endingDate THEN
 		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Start date must come before end date';
 END IF;
 
 
 SET dateDiff = DATEDIFF(endingDate,startingDate) +1;
 
 counter: WHILE dateDiff > 0 DO
	 	
	 	SET calDate = startingDate + INTERVAL (dateDiff-1) DAY;
		 
		-- increment the correct day value
		SET calDay   = WEEKDAY(calDate);
		SET calWeek  = EXTRACT(WEEK  FROM calDate);
		SET calMonth = EXTRACT(MONTH FROM calDate);
		SET calYear  = EXTRACT(YEAR  FROM calDate);
		 	
		CASE calDay
			WHEN 1 THEN SET calSun = -1; 
			WHEN 2 THEN SET calMon = -1;
			WHEN 3 THEN SET calTue = -1;
			WHEN 4 THEN SET calWed = -1;
			WHEN 5 THEN SET calThu = -1;
			WHEN 6 THEN SET calFri = -1;
			WHEN 7 THEN SET calSat = -1;
		END CASE;
		
		-- insert row in the Materialized View. 
			 
		UPDATE `bookings_agg_mv` 
		SET  cal_sun = if(cal_sun=0,0,(cal_sun + calSun))
		    ,cal_mon = if(cal_mon=0,0,(cal_mon + calMon))
		    ,cal_tue = if(cal_tue=0,0,(cal_tue + calTue))
			,cal_wed = if(cal_wed=0,0,(cal_wed + calWed))
			,cal_thu = if(cal_thu=0,0,(cal_thu + calThu))
			,cal_fri = if(cal_fri=0,0,(cal_fri + calFri))
			,cal_sat = if(cal_sat=0,0,(cal_sat + calSat))
		WHERE schedule_id 	= scheduleID 
		AND   cal_week 		= calWeek 
		AND   cal_year 		= calYear;
	
	SET dateDiff = dateDiff -1;
	END WHILE counter;

END;
$$