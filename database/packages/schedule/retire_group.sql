-- -----------------------------------------------------
-- procedure bm_schedule_retire_group
-- -----------------------------------------------------
DELIMITER $$
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