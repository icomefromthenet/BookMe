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

CREATE PROCEDURE `bm_schedule_add_group` (IN groupName VARCHAR(100)
											, IN validFrom DATE
											, IN validTo DATE
											, OUT groupID INT)
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

		SET validTo = (validTo + INTERVAL 1 DAY);

	END IF;

	
	-- We require that the schedule validity period  be contained within the assigned groups validity period
	-- that being the assign group must be valid for each day the schedule exists.
	
	-- Members, Timeslot do not have a validity period, the normal FK will maintain consistency
	
	
	INSERT INTO `schedules` (`schedule_id`,`open_from`,`closed_on`,`schedule_group_id`,`membership_id`) 
	VALUES (NULL,validFrom,validTo,(SELECT gs.group_id 
	                                           FROM schedule_groups gs
	                                           WHERE gs.valid_from <= validFrom
	                                           AND gs.valid_to > validTo
	                                           AND gs.group_id = groupID),memberID);
	
	SET scheduleID =  LAST_INSERT_ID();

	IF @bm_debug = true THEN
		CALL util_proc_log(concat('Inserted new schedule at::',scheduleID,' for member::',memberID));
	END IF;	

END$$

-- -----------------------------------------------------
-- procedure bm_schedule_retire
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_schedule_retire`$$

CREATE PROCEDURE `bm_schedule_retire` (IN scheduleID INT
                                   	 , IN validTo DATE)
BEGIN

	-- table uses closed:open interval period
	SET validTo = (validTo + INTERVAL 1 DAY);
	
	UPDATE `schedules` s SET `s`.`closed_on` = validTo 
	WHERE NOT EXISTS (
		SELECT 1
		FROM `bookings` b
		WHERE schedule_id
	)
	AND `s`.`schedule_id` = scheduleID;
	

	IF ROW_COUNT() = 0 THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Schedule not found or validTo date is not within original validity range';
	END IF;
	
	IF @bm_debug = true THEN
		CALL util_proc_log(concat('Retired a schedule at::',groupID));
	END IF;	
	
END$$