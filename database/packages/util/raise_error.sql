-- -----------------------------------------------------
-- function utl_raise_error
-- -----------------------------------------------------
DELIMITER $$
DROP function IF EXISTS `utl_raise_error`$$

CREATE FUNCTION `utl_raise_error`(MESSAGE VARCHAR(255)) 
RETURNS INTEGER DETERMINISTIC BEGIN
  DECLARE ERROR INTEGER;
  set ERROR := MESSAGE;
  RETURN 0;
END$$