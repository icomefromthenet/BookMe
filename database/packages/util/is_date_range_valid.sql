-- -----------------------------------------------------
-- function utl_is_valid_date_range
-- -----------------------------------------------------
DELIMITER $$
DROP function IF EXISTS `utl_is_valid_date_range`$$

CREATE FUNCTION `utl_is_valid_date_range`(validFrom DATE,validTo DATE) 
RETURNS INTEGER DETERMINISTIC BEGIN
	DECLARE isValid INT DEFAULT 0;

	IF validFrom < validTo THEN
		SET isValid = 1;
	END IF;

	RETURN isValid;

END$$