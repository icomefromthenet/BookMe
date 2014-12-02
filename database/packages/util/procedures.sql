-- -----------------------------------------------------
-- Procedures for package
-- -----------------------------------------------------
DELIMITER $$

-- -----------------------------------------------------
-- Following utl_proc_* have been adapted from 
-- https://github.com/CaptTofu/Stored-procedure-debugging-routines/blob/master/proclog.sql
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Procedures for `setupProcLog`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `util_proc_setup`$$

CREATE PROCEDURE `util_proc_setup`()
BEGIN
    DECLARE proclog_exists INT DEFAULT 0;

    /* 
       check if proclog is existing. This check seems redundant, but
       simply relying on 'create table if not exists' is not enough because
       a warning is thrown which will be caught by your exception handler
    */
    SELECT count(*) INTO `proclog_exists`
        FROM `information_schema`.`tables` 
        WHERE `table_schema` = database() AND `table_name` = 'proclog';

    IF proclog_exists = 0 THEN 
        CREATE TABLE IF NOT EXISTS `proclog`(
             `entrytime` DATETIME
            ,`connection_id` INT NOT NULL DEFAULT 0
            ,`msg` VARCHAR(512)
        );
    END IF;
    
    /* 
     * temp table is not checked in information_schema because it is a temp
     * table
     */
    CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_proclog`(
         `entrytime` TIMESTAMP
        ,`connection_id` INT NOT NULL DEFAULT 0
        ,`msg` VARCHAR(512)
    ) ENGINE = memory;
    
END $$

-- -----------------------------------------------------
-- Procedures for `util_proc_log`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `util_proc_log`$$

CREATE PROCEDURE `util_proc_log`(IN logMsg VARCHAR(512))
BEGIN
  
  DECLARE CONTINUE HANDLER FOR 1146 -- Table not found
  BEGIN
    CALL util_proc_setup();
    INSERT INTO `tmp_proclog` (`connection_id`, `msg`) VALUES (CONNECTION_ID(), 'reset tmp table');
    INSERT INTO `tmp_proclog` (`connection_id`, `msg`) VALUES (CONNECTION_ID(), logMsg);
  END;

  INSERT INTO `tmp_proclog` (connection_id, msg) VALUES (CONNECTION_ID(), logMsg);
  
END$$

-- -----------------------------------------------------
-- Procedures for `util_proc_cleanup`
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `util_proc_cleanup`$$

CREATE PROCEDURE `util_proc_cleanup` (IN logMsg VARCHAR(512))
BEGIN

   CALL util_proc_log(CONCAT("cleanup() ",IFNULL(logMsg, ''))); 
   INSERT INTO `proclog` SELECT * FROM `tmp_proclog`;
   DROP TABLE `tmp_proclog`;
   
END$$

-- -----------------------------------------------------
-- //End of  Following utl_proc_* adapted--
-- -----------------------------------------------------



-- -----------------------------------------------------
-- procedure util_debug_msg
-- -----------------------------------------------------
DROP procedure IF EXISTS `util_debug_msg`$$

CREATE PROCEDURE `util_debug_msg` (IN enabled INTEGER, IN msg VARCHAR(255))
BEGIN
  IF enabled THEN 
  BEGIN
    select concat("** ", msg) AS '** DEBUG:';
  END;  
  END IF;
END$$



