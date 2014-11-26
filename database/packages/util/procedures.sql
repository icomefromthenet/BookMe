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


-- -----------------------------------------------------
-- procedure utl_create_rule_tmp_tables
-- -----------------------------------------------------
DROP procedure IF EXISTS `utl_create_rule_tmp_tables`$$

CREATE procedure `utl_create_rule_tmp_tables` ()
BEGIN
	
	DROP TEMPORARY TABLE IF EXISTS bm_parsed_ranges;
	CREATE TEMPORARY TABLE bm_parsed_ranges (
		id INT PRIMARY KEY AUTO_INCREMENT,
		range_open INT NOT NULL,
		range_closed INT NOT NULL,
		mod_value INT NULL,
		value_type ENUM('minute','hour','day','month','year')
	) ENGINE=MEMORY;
	
	DROP TEMPORARY TABLE IF EXISTS bm_range_values;
	CREATE TEMPORARY TABLE bm_range_values(
		id INT PRIMARY KEY AUTO_INCREMENT,
		include_minute INT NULL,
		include_hour INT NULL,
		include_day	INT NULL,
		include_month INT NULL,
		include_year INT NULL,
		CONSTRAINT bm_range_uk_1 UNIQUE INDEX (include_minute,include_hour
												,include_day,include_month
												,include_year)
	)ENGINE=MEMORY;
END$$

