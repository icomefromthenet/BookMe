-- -----------------------------------------------------
-- procedure bm_calendar_is_valid_length
-- -----------------------------------------------------
DELIMITER $$
DROP procedure IF EXISTS `bm_calendar_is_valid_length`$$

CREATE PROCEDURE `bm_calendar_is_valid_length`(IN x INT)
BEGIN
	DECLARE maxPeriod INT DEFAULT 10;
		
	-- x is with valid range 
	IF x < 1 OR x > maxPeriod THEN
		SELECT utl_raise_error('Minimum calendar year is 1 and maxium is 10');
	END IF;
	
END$$