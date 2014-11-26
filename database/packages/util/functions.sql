-- -----------------------------------------------------
-- functions for package
-- -----------------------------------------------------
DELIMITER $$

-- -----------------------------------------------------
-- function utl_is_valid_date_range
-- -----------------------------------------------------
DROP function IF EXISTS `utl_is_valid_date_range`$$

CREATE FUNCTION `utl_is_valid_date_range`(validFrom DATE,validTo DATE) 
RETURNS INTEGER DETERMINISTIC BEGIN
	DECLARE isValid INT DEFAULT 0;

	-- test if closure date occurs after the opening date 
	-- and opening date does not occur in past.
	-- smallest temportal unit is 1 day we need to cast to a date
	IF CAST(validFrom AS DATE) <= CAST(validTo AS DATE) && CAST(validFrom AS DATE) >= CAST(NOW() AS DATE) THEN
		SET isValid = 1;
	END IF;

	RETURN isValid;

END$$