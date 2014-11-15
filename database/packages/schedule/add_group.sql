-- -----------------------------------------------------
-- procedure bm_schedule_add_group
-- -----------------------------------------------------
DELIMITER $$
DROP procedure IF EXISTS `bm_schedule_add_group`$$

CREATE PROCEDURE `bm_schedule_add_group` (IN groupName VARCHAR(100)
											, IN validFrom DATE
											, IN validTo DATE
											, OUT groupID INT)
BEGIN
	START TRANSACTION;
		
	IF validTo IS NULL THEN 
      SET validTo = DATE('3000-01-01');
	END IF;
	
	IF utl_is_valid_date_range(validFrom,validTo) = 0 THEN
		SELECT raise_error('Dates are not a valid range');
	END IF;
	
	-- add new group and fetch the assigned ID	
	-- trigger will update the audit table with insert 
	INSERT INTO schedule_groups (group_id,group_name,valid_from,valid_to) 
		VALUES (null,groupName,validFrom,validTo);
	SELECT LAST_INSERT_ID() INTO groupID;

	COMMIT;

END$$
