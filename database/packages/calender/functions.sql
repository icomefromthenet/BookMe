-- -----------------------------------------------------
-- Function for package
-- -----------------------------------------------------
DELIMITER $$

DROP FUNCTION IF EXISTS `bm_cal_get_slot_date` $$
CREATE FUNCTION `bm_cal_get_slot_date`(slotID INT) 
RETURNS DATE BEGIN
    DECLARE slotDte DATE;
    
    SELECT CAST(`cal_date` AS DATE)
    FROM `slots` 
    WHERE slot_id = slotID 
    INTO slotDte;
    
    RETURN slotDte;
END; 
$$


