-- -----------------------------------------------------
-- procedures for package
-- -----------------------------------------------------
DELIMITER $$

-- -----------------------------------------------------
-- procedure bm_add_membership
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_add_membership`$$

CREATE PROCEDURE `bm_add_membership` (out membership_id INT)
BEGIN
    
    INSERT INTO schedule_membership (membership_id,registered_date) values (NULL,NOW());
    SET membership_id = LAST_INSERT_ID();
    
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
	IF validTo IS NULL THEN 
      SET validTo = DATE('3000-01-01');
	END IF;
	
	IF utl_is_valid_date_range(validFrom,validTo) = 0 THEN
		SELECT utl_raise_error('Dates are not a valid range');
	END IF;
	
	-- add new group and fetch the assigned ID	
	-- trigger will update the audit table with insert 
	INSERT INTO schedule_groups (group_id,group_name,valid_from,valid_to) 
		VALUES (null,groupName,validFrom,validTo);
	
	SET groupID =  LAST_INSERT_ID();
	
	CALL util_debug_msg(@bm_debug,concat('Inserted new schedule group at::',groupID,' name::',groupName));	
	
END$$


-- -----------------------------------------------------
-- procedure bm_schedule_retire_group
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_schedule_retire_group`$$

CREATE PROCEDURE `bm_schedule_retire_group` (IN groupID INT, IN validTo Date )
BEGIN
		
DECLARE isNotFound INT DEFAULT 1;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET isNotFound = 0;

	-- verify that valid to is valid date range and group given exists

	SELECT 1 
	FROM schedule_groups
	WHERE group_id = groupID
	AND valid_from < valid_to;

	IF isNotFound THEN
		SELECT utl_raise_error(concat('Group at ',groupID ,' not found or validTo date is invalid'));
	END IF;

	-- assign new valid to date to retire this group

	UPDATE schedule_groups SET valid_to = validTo
	WHERE group_id = groupID;

END$$

-- -----------------------------------------------------
-- procedure bm_schedule_add
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_schedule_add`$$

CREATE PROCEDURE `bm_schedule_add` (IN groupID INT
                                  , IN memberID INT
                                  , IN timeslotID INT
                                  , IN validFrom DATE
                                  , IN validTo DATE
                                  , OUT schedueID INT)
BEGIN

	IF validTo IS NULL THEN 
      SET validTo = DATE('3000-01-01');
	END IF;
	
	IF utl_is_valid_date_range(validFrom,validTo) = 0 THEN
		SELECT utl_raise_error('Dates are not a valid range');
	END IF;
	
	-- We require that the schedule validity period  be contained within the assigned groups validity period
	-- that being the assign group must be valid for each day the schedule exists.
	
	-- Members, Timeslot do not have a validity period, the normal FK will maintain consistency
	
	INSERT INTO `schedules` (`schedule_id`,`timeslot_id`,`open_from`,`closed_on`,`schedule_group_id`,`membership_id`) 
	VALUES (NULL,timeslotID,validFrom,validTo,(SELECT gs.group_id 
	                                           FROM schedule_groups gs
	                                           WHERE gs.valid_from <= validFrom
	                                           AND gs.valid_to >= validTo
	                                           AND gs.group_id = groupID),memberID);
	
	SET schedueID =  LAST_INSERT_ID();

END$$