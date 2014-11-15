-- -----------------------------------------------------
-- procedure util_debug_msg
-- -----------------------------------------------------
DELIMITER $$
DROP procedure IF EXISTS `util_debug_msg`$$

CREATE PROCEDURE util_debug_msg(enabled INTEGER, msg VARCHAR(255))
BEGIN
  IF enabled THEN BEGIN
    select concat("** ", msg) AS '** DEBUG:';
  END; END IF;
END$$