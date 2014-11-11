SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


-- -----------------------------------------------------
-- Table `calendar`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `calendar` ;

CREATE TABLE IF NOT EXISTS `calendar` (
  `calendar_date` DATE NOT NULL COMMENT 'date and table key',
  `y` SMALLINT NULL COMMENT 'year where date occurs',
  `q` TINYTEXT NULL COMMENT 'quarter of the year date belongs',
  `m` TINYINT NULL COMMENT 'month of the year',
  `d` TINYINT NULL COMMENT 'numeric date part',
  `dw` TINYINT NULL COMMENT 'day number of the date in a week',
  `month_name` VARCHAR(9) NULL COMMENT 'text name of the month',
  `day_name` VARCHAR(9) NULL COMMENT 'text name of the day\n',
  `w` TINYINT NULL COMMENT 'week number in the year',
  `is_week_day` TINYINT NULL COMMENT 'true value if current date falls between monday-friday\n',
  PRIMARY KEY (`calendar_date`))
ENGINE = InnoDB
COMMENT = 'Calender table that store the next 10 years of dates';


-- -----------------------------------------------------
-- Table `slots`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `slots` ;

CREATE TABLE IF NOT EXISTS `slots` (
  `slot_id` INT NOT NULL AUTO_INCREMENT COMMENT 'Table primary key',
  `cal_date` DATE NOT NULL COMMENT 'Date this slot occurs on used to join date time',
  `slot_open` DATETIME NOT NULL COMMENT 'Opending Interval of this slot',
  `slot_close` DATETIME NOT NULL COMMENT 'closing internal of slot',
  PRIMARY KEY (`slot_id`),
  INDEX `fk_slots_1_idx` (`cal_date` ASC),
  CONSTRAINT `fk_slots_1`
    FOREIGN KEY (`cal_date`)
    REFERENCES `calendar` (`calendar_date`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'The common slots table, each slot is the minium slot duratio /* comment truncated */ /*n of 1 minute. */';


-- -----------------------------------------------------
-- Table `schedule_groups`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `schedule_groups` ;

CREATE TABLE IF NOT EXISTS `schedule_groups` (
  `group_id` INT NOT NULL AUTO_INCREMENT,
  `group_name` VARCHAR(45) NULL,
  `valid_from` DATE NOT NULL COMMENT 'frist date this group valid from',
  `valid_to` DATE NOT NULL COMMENT 'Last day group valid too',
  PRIMARY KEY (`group_id`),
  UNIQUE INDEX `tag_name_UNIQUE` (`group_name` ASC))
ENGINE = InnoDB
COMMENT = 'Ways to group schedules.';


-- -----------------------------------------------------
-- Table `timeslots`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `timeslots` ;

CREATE TABLE IF NOT EXISTS `timeslots` (
  `timeslot_id` INT NOT NULL AUTO_INCREMENT COMMENT 'Table Primary key\n',
  `timeslot_length` INT NOT NULL COMMENT 'Number of minutes in the slot',
  PRIMARY KEY (`timeslot_id`),
  UNIQUE INDEX `timeslot_length_UNIQUE` (`timeslot_length` ASC))
ENGINE = InnoDB
COMMENT = 'This describes the intervals lengths ie timeslots that used  /* comment truncated */ /*by schedules*/';


-- -----------------------------------------------------
-- Table `schedule_membership`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `schedule_membership` ;

CREATE TABLE IF NOT EXISTS `schedule_membership` (
  `membership_id` INT NOT NULL AUTO_INCREMENT,
  `registered_date` DATETIME NOT NULL,
  PRIMARY KEY (`membership_id`))
ENGINE = InnoDB
COMMENT = 'Used to group schedules by externel membership entity';


-- -----------------------------------------------------
-- Table `schedules`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `schedules` ;

CREATE TABLE IF NOT EXISTS `schedules` (
  `schedule_id` INT NOT NULL AUTO_INCREMENT COMMENT 'Table Primary key\n',
  `timeslot_id` INT NULL,
  `open_from` DATE NOT NULL COMMENT 'Date to start schedule on',
  `closed_on` DATE NOT NULL DEFAULT '3000-01-01' COMMENT 'Date schedule is not available',
  `schedule_group_id` INT NOT NULL,
  `membership_id` INT NOT NULL,
  PRIMARY KEY (`schedule_id`),
  INDEX `fk_schedules_1_idx` (`timeslot_id` ASC),
  INDEX `fk_schedules_2_idx` (`schedule_group_id` ASC),
  INDEX `fk_schedules_3_idx` (`membership_id` ASC),
  CONSTRAINT `fk_schedules_1`
    FOREIGN KEY (`timeslot_id`)
    REFERENCES `timeslots` (`timeslot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_schedules_2`
    FOREIGN KEY (`schedule_group_id`)
    REFERENCES `schedule_groups` (`group_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_schedules_3`
    FOREIGN KEY (`membership_id`)
    REFERENCES `schedule_membership` (`membership_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'This contains a list of schedules that can have bookings';


-- -----------------------------------------------------
-- Table `timeslot_slots`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `timeslot_slots` ;

CREATE TABLE IF NOT EXISTS `timeslot_slots` (
  `timeslot_slot_id` INT NOT NULL AUTO_INCREMENT,
  `opening_slot_id` INT NOT NULL,
  `closing_slot_id` INT NOT NULL,
  `timeslot_id` INT NOT NULL,
  PRIMARY KEY (`timeslot_slot_id`),
  INDEX `fk_timeslot_slots_1_idx` (`timeslot_id` ASC),
  INDEX `fk_timeslot_slots_2_idx` (`opening_slot_id` ASC),
  INDEX `fk_timeslot_slots_3_idx` (`closing_slot_id` ASC),
  UNIQUE INDEX `iimeslot_slots_uk1` (`opening_slot_id` ASC, `closing_slot_id` ASC, `timeslot_id` ASC),
  CONSTRAINT `fk_timeslot_slots_1`
    FOREIGN KEY (`timeslot_id`)
    REFERENCES `timeslots` (`timeslot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_timeslot_slots_2`
    FOREIGN KEY (`opening_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_timeslot_slots_3`
    FOREIGN KEY (`closing_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Groups our timeslots into slot groups.';


-- -----------------------------------------------------
-- Table `exclusion_rules`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `exclusion_rules` ;

CREATE TABLE IF NOT EXISTS `exclusion_rules` (
  `exclusion_rule_id` INT NOT NULL AUTO_INCREMENT,
  `rule_name` VARCHAR(50) NULL COMMENT 'name for this rule',
  `valid_from` DATE NOT NULL COMMENT 'opening application date',
  `valid_to` DATE NOT NULL DEFAULT '3000-01-01' COMMENT 'closing application date\n',
  `apply_on` TINYINT NOT NULL DEFAULT 0 COMMENT '0 = weekday/weekend\n1 = weekend\n2 = weekday',
  `exclude_length` INT NOT NULL COMMENT 'number of minutes to exclude\nuse minutes as our common slot is 1 minute durations',
  `repeat_minute` VARCHAR(50) NOT NULL DEFAULT 0 COMMENT 'min (0 - 59)',
  `repeat_hour` VARCHAR(50) NOT NULL DEFAULT 0 COMMENT 'hour (0 - 23)',
  `repeat_dayofweek` VARCHAR(50) NOT NULL DEFAULT 0 COMMENT 'day of week (0 - 6) (0 to 6 are Sunday to Saturday, or use names; 7 is Sunday, the same as 0)',
  `repeat_month` VARCHAR(50) NOT NULL DEFAULT 0 COMMENT 'day of week (0 - 6) (0 to 6 are Sunday to Saturday, or use names; 7 is Sunday, the same as 0)',
  `repeat_dayofmonth` VARCHAR(50) NOT NULL DEFAULT 0 COMMENT 'day of month (1 - 31)',
  `repeat_year` VARCHAR(50) NOT NULL DEFAULT 0,
  `schedule_group_id` INT(11) NOT NULL,
  PRIMARY KEY (`exclusion_rule_id`),
  UNIQUE INDEX `rule_name_UNIQUE` (`rule_name` ASC),
  INDEX `fk_exclusion_rules_1_idx` (`schedule_group_id` ASC),
  CONSTRAINT `fk_exclusion_rules_1`
    FOREIGN KEY (`schedule_group_id`)
    REFERENCES `schedule_groups` (`group_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Describes the rules that exclude timeslots';


-- -----------------------------------------------------
-- Table `inclusion_rules`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `inclusion_rules` ;

CREATE TABLE IF NOT EXISTS `inclusion_rules` (
  `inclusion_rule_id` INT NOT NULL AUTO_INCREMENT,
  `rule_name` VARCHAR(45) NOT NULL,
  `valid_from` DATE NOT NULL,
  `valid_to` DATE NOT NULL DEFAULT '3000-01-01',
  `interval_start` TIME NULL COMMENT 'starting slot that is open',
  `interval_length` TIME NULL COMMENT 'last slot time to include in rule\n',
  `repeat_minute` VARCHAR(45) NOT NULL,
  `repeat_hour` VARCHAR(45) NOT NULL,
  `repeat_dayofweek` VARCHAR(45) NOT NULL,
  `repeat_dayofmonth` VARCHAR(45) NOT NULL,
  `repeat_month` VARCHAR(45) NOT NULL,
  `repeat_year` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`inclusion_rule_id`),
  UNIQUE INDEX `rule_name_UNIQUE` (`rule_name` ASC))
ENGINE = InnoDB
COMMENT = 'Rules that mark intervals to are available to schedule\n';


-- -----------------------------------------------------
-- Table `inclusion_overrides`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `inclusion_overrides` ;

CREATE TABLE IF NOT EXISTS `inclusion_overrides` (
  `inclusion_overrides_id` INT NOT NULL AUTO_INCREMENT,
  `schedule_id` INT NOT NULL,
  `slot_id` INT NOT NULL,
  PRIMARY KEY (`inclusion_overrides_id`),
  INDEX `fk_inclusion_overrides_1_idx` (`slot_id` ASC),
  INDEX `fk_inclusion_overrides_2_idx` (`schedule_id` ASC),
  UNIQUE INDEX `inclusion_overrides_uk1` (`schedule_id` ASC, `slot_id` ASC),
  CONSTRAINT `fk_inclusion_overrides_1`
    FOREIGN KEY (`slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inclusion_overrides_2`
    FOREIGN KEY (`schedule_id`)
    REFERENCES `schedules` (`schedule_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Slots that should be included in schedule will override excl /* comment truncated */ /*usion rules but not exclusion overrides
*/';


-- -----------------------------------------------------
-- Table `exclusion_overrides`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `exclusion_overrides` ;

CREATE TABLE IF NOT EXISTS `exclusion_overrides` (
  `exclusion_override_id` INT NOT NULL AUTO_INCREMENT,
  `slot_id` INT NOT NULL,
  `schedule_id` INT NOT NULL,
  PRIMARY KEY (`exclusion_override_id`),
  INDEX `fk_exclusion_override_1_idx` (`slot_id` ASC),
  INDEX `fk_exclusion_override_2_idx` (`schedule_id` ASC),
  UNIQUE INDEX `index4` (`slot_id` ASC, `schedule_id` ASC),
  CONSTRAINT `fk_exclusion_override_1`
    FOREIGN KEY (`slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_exclusion_override_2`
    FOREIGN KEY (`schedule_id`)
    REFERENCES `schedules` (`schedule_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'List of slots that should not be included in schedule, will  /* comment truncated */ /*override all inclusion rules and inclusion overrides*/';


-- -----------------------------------------------------
-- Table `ints`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ints` ;

CREATE TABLE IF NOT EXISTS `ints` (
  `i` TINYINT NOT NULL,
  PRIMARY KEY (`i`))
ENGINE = InnoDB
COMMENT = 'seed table for creating calender';


-- -----------------------------------------------------
-- Table `bookings`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `bookings` ;

CREATE TABLE IF NOT EXISTS `bookings` (
  `booking_id` INT NOT NULL,
  `start_slot_id` INT NOT NULL,
  `end_slot_id` INT NOT NULL,
  `valid_from` DATE NOT NULL,
  `valid_to` DATE NULL DEFAULT '3000-01-01',
  `schedule_id` INT NOT NULL,
  PRIMARY KEY (`booking_id`),
  INDEX `fk_bookings_1_idx` (`schedule_id` ASC),
  CONSTRAINT `fk_bookings_1`
    FOREIGN KEY (`schedule_id`)
    REFERENCES `schedules` (`schedule_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- procedure bm_calendar_setup_cal
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_calendar_setup_cal`;

DELIMITER $$
CREATE PROCEDURE `bm_calendar_setup_cal` (IN x INT)
BEGIN
	START TRANSACTION;

	-- validate the length is in valid range
	CALL bm_calendar_is_valid_length(x);

	INSERT INTO calendar (calendar_date)
		SELECT DATE_FORMAT(NOW() ,'%Y-01-01') + INTERVAL a.i*10000 + b.i*1000 + c.i*100 + d.i*10 + e.i DAY
		FROM ints a JOIN ints b JOIN ints c JOIN ints d JOIN ints e
		WHERE (a.i*10000 + b.i*1000 + c.i*100 + d.i*10 + e.i) <= DATEDIFF(DATE_FORMAT(NOW()+ INTERVAL (x -1) YEAR,'%Y-12-31'),DATE_FORMAT(NOW() ,'%Y-01-01'))
		ORDER BY 1;
	
	
	UPDATE calendar
	SET is_week_day = CASE WHEN dayofweek(calendar_date) IN (1,7) THEN 0 ELSE 1 END,
		y = YEAR(calendar_date),
		q = quarter(calendar_date),
		m = MONTH(calendar_date),
		d = dayofmonth(calendar_date),
		dw = dayofweek(calendar_date),
		month_name = monthname(calendar_date),
		day_name = dayname(calendar_date),
		w = week(calendar_date);

	COMMIT;

END$$

DELIMITER ;

-- -----------------------------------------------------
-- procedure bm_calender_setup_slots
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_calender_setup_slots`;

DELIMITER $$
CREATE PROCEDURE `bm_calender_setup_slots` ()
BEGIN
	START TRANSACTION;

		-- produce 1 slot per minute for each day in the calender
    -- each day need 1440 slots
	INSERT INTO slots (slot_id,cal_date,slot_open,slot_close)
		SELECT NULL
              ,calendar_date 
			  ,calendar_date + INTERVAL d.i *1000 + c.i *100 + b.i*10 + a.i MINUTE as slot_open
			  ,calendar_date + INTERVAL d.i *1000 + c.i *100 + b.i*10 + a.i + 1 MINUTE as slot_closed FROM calendar
		JOIN ints a JOIN ints b JOIN ints c JOIN ints d
		WHERE d.i*1000 + c.i *100 + b.i*10 + a.i < 1440;

	COMMIT;
END$$

DELIMITER ;

-- -----------------------------------------------------
-- procedure bm_install_run
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_install_run`;

DELIMITER $$
CREATE PROCEDURE `bm_install_run` ()
BEGIN
	DECLARE timeslot_id INT;
	DECLARE timeslot_length INT;
	DECLARE l_last_row_fetched INT DEFAULT 0;

	-- timeslot loop vars
	DECLARE timeslots_cursor CURSOR FOR 
		SELECT timeslot_id,timeslot_length;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET l_last_row_fetched=1;

	
	START TRANSACTION;

	-- setup calender for 10 years
	CALL bm_calendar_setup_cal(10);

	-- setup slots for 10 years
	CALL bm_calender_setup_slots();


	-- buid timeslots found in table into group cache table
	OPEN timeslots_cursor;
		cursor_loop:LOOP

		IF l_last_row_fetched=1 THEN
			LEAVE cursor_loop;
		END IF;

		FETCH timeslots_cursor INTO timeslot_id,timeslot_length;

		-- build timeslot cache table for this timeslot		
		CALL bm_calendar_build_timeslot_slots(timeslot_id,timeslot_length);

		END LOOP cursor_loop;
	CLOSE timeslots_cursor;
	SET l_last_row_fetched=0;

	-- build inclusion rules

    -- build exclusion rules

	COMMIT;

END$$

DELIMITER ;

-- -----------------------------------------------------
-- function utl_raise_error
-- -----------------------------------------------------
DROP function IF EXISTS `utl_raise_error`;

DELIMITER $$
CREATE FUNCTION `utl_raise_error`(MESSAGE VARCHAR(255)) 
RETURNS INTEGER DETERMINISTIC BEGIN
  DECLARE ERROR INTEGER;
  set ERROR := MESSAGE;
  RETURN 0;
END $$

DELIMITER ;

-- -----------------------------------------------------
-- procedure bm_calendar_is_valid_length
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_calendar_is_valid_length`;

DELIMITER $$
CREATE PROCEDURE `bm_calendar_is_valid_length`(IN x INT)
BEGIN
	DECLARE maxPeriod INT DEFAULT 10;
		
	-- x is with valid range 
	IF x < 1 OR x > maxPeriod THEN
		SELECT utl_raise_error('Minimum calendar year is 1 and maxium is 10');
	END IF;
	
	
END$$

DELIMITER ;

-- -----------------------------------------------------
-- procedure bm_calendar_build_timeslot_slots
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_calendar_build_timeslot_slots`;

DELIMITER $$
CREATE PROCEDURE `bm_calendar_build_timeslot_slots` (IN timeslotID INT
													,IN timeslotLength INT )
BEGIN
		
	START TRANSACTION;

	-- Need to group our slots and insert results into group cache table
    -- As out slot tabe has sequential id we can use this to build buckets
	INSERT INTO timeslot_slots (timeslot_slot_id,opening_slot_id,closing_slot_id,timeslot_id)  
		SELECT NULL
              ,min(a.slot_id) as slot_open_id	
			  ,max(a.slot_id) as slot_close_id
              ,timeslotID
        FROM slots a
		GROUP BY ceil(a.slot_id/timeslotLength);	

	
	COMMIT;

END$$

DELIMITER ;

-- -----------------------------------------------------
-- procedure bm_calendar_addtimeslot
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_calendar_addtimeslot`;

DELIMITER $$
CREATE PROCEDURE `bm_calendar_addtimeslot` (IN slotLength INT)
BEGIN
	DECLARE timeslotID INT;	

	START TRANSACTION;

	IF slotLength <= 1 AND slotLength > (60*24) THEN 
		 SELECT utl_raise_error('Slot must be between 1 minutes and 1440 (day) in length');
	END IF;
	
	-- unique index on length column stop duplicates
    -- trigger should fire that record this addition onto audit table
	INSERT INTO timeslots (timeslot_id,timeslot_length) values (NULL,slotLength);

    -- calculate this timeslots , slot groups. 
	SELECT LAST_INSERT_ID() INTO timeslotID;
	
	CALL bm_calendar_build_timeslot_slots(timeslotID,slotLength);

	COMMIT;


END$$

DELIMITER ;

-- -----------------------------------------------------
-- procedure bm_schedule_add_group
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_schedule_add_group`;

DELIMITER $$
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

DELIMITER ;

-- -----------------------------------------------------
-- function utl_is_valid_date_range
-- -----------------------------------------------------
DROP function IF EXISTS `utl_is_valid_date_range`;

DELIMITER $$
CREATE FUNCTION `utl_is_valid_date_range`(validFrom DATE,validTo DATE) 
RETURNS INTEGER DETERMINISTIC BEGIN
	DECLARE isValid INT DEFAULT 0;

	IF validFrom < validTo THEN
		SET isValid = 1;
	END IF;

	RETURN isValid;

END$$

DELIMITER ;

-- -----------------------------------------------------
-- procedure utl_create_rule_tmp_tables
-- -----------------------------------------------------
DROP procedure IF EXISTS `utl_create_rule_tmp_tables`;

DELIMITER $$
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
END;

$$

DELIMITER ;

-- -----------------------------------------------------
-- procedure bm_rules_parse_minute
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_parse_minute`;

DELIMITER $$
create procedure `bm_rules_parse_minute`(IN cron VARCHAR(100))
BEGIN

	DECLARE filteredCron VARCHAR(100) DEFAULT '';
	DECLARE rangeOccurances INT DEFAULT NULL;
	DECLARE i INT DEFAULT 0;
	DECLARE splitValue VARCHAR(10);
	DECLARE openValue  INT DEFAULT 0;
    DECLARE closeValue INT DEFAULT 0;
    DECLARE incrementValue INT DEFAULT 0;

	SET filteredCron = trim(cron);
	SET rangeOccurances = LENGTH(filteredCron) - LENGTH(REPLACE(filteredCron, ',', ''))+1;
	
	CALL util_debug_msg(@bm_debug,'executing parse minute cron');
    
	IF filteredCron = '*' THEN
		CALL util_debug_msg(@bm_debug,'filteredCron is eq *');	
	-- test if we  have default * only
	-- insert the default range into the parsed ranges table
		INSERT INTO bm_parsed_ranges (id,range_open,range_closed,mod_value,value_type) 
		VALUES (NULL,1,59,null,'minute');

	ELSE 
		-- split our set and parse each range declaration.
		SET i = 1;
		SET openValue = 0;
		SET closeValue = 0;
		SET incrementValue = 0;

		CALL util_debug_msg(@bm_debug,concat('rangeOccurances eq to ',rangeOccurances));	

		WHILE i < rangeOccurances DO
			SET splitValue = REPLACE(SUBSTRING(SUBSTRING_INDEX(filteredCron, ',', i),LENGTH(SUBSTRING_INDEX(filteredCron, ',', i - 1)) + 1), ',', '');
			
			CALL util_debug_msg(@bm_debug,concat('splitValue at ',i ,' is eq ',splitValue));	
			
			-- find which range type we have
			CASE
				-- test for range with increment e.g 01-59/39
				WHEN splitValue REGEXP '^([0-5][0-9]?|[0-9]?)-([0-5][0-9]?|[0-9]?)/([0-5][0-9]?|[0-9]?)$'  > 0 THEN
					CALL util_debug_msg(@bm_debug,'splitValue eq ##-##/##');	
					SET openValue = CAST(SUBSTRING_INDEX(splitValue, '-', 1) AS UNSIGNED);
					SET closeValue = CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(splitValue, '/', 1),'-',-1) AS UNSIGNED);				
					SET incrementValue = CAST(SUBSTRING_INDEX(splitValue, '/', -1) AS UNSIGNED);
				
				-- test for a scalar with increment e.g 6/3 this short for 6-59/3
				WHEN splitValue REGEXP '^([0-5][0-9]?|[0-9]?)/([0-5][0-9]?|[0-9]?)$' > 0 THEN
					CALL util_debug_msg(@bm_debug,'splitValue eq ##/##');	
					SET openValue = CAST(SUBSTRING_INDEX(splitValue, '/', 1) AS UNSIGNED);
					SET closeValue = 59;
					SET incrementValue = CAST(SUBSTRING_INDEX(splitValue, '/', -1) AS UNSIGNED);
				
				-- test a range with e.g 34-59
				WHEN splitValue REGEXP '^([0-5][0-9]?|[0-9]?)-([0-5][0-9]?|[0-9]?)$' > 0 THEN				
					CALL util_debug_msg(@bm_debug,'splitValue eq ##-##');	
					SET openValue = CAST(SUBSTRING_INDEX(splitValue, '-', 1) AS UNSIGNED);
					SET closeValue = CAST(SUBSTRING_INDEX(splitValue, '-', -1) AS UNSIGNED);				
					
				-- test for a scalar value
				WHEN splitValue REGEXP '^([0-5][0-9]?|[0-9]?)$' > 0 THEN				
					CALL util_debug_msg(@bm_debug,'splitValue eq ##');	
					SET openValue = CAST(splitValue AS UNSIGNED);
					SET closeValue = CAST(splitValue AS UNSIGNED);				
								
				-- test for a * with increment e.g */5
				WHEN splitValue REGEXP '^([*]?)/([0-5][0-9]?|[0-9]?)$' > 0 THEN
					CALL util_debug_msg(@bm_debug,'splitValue eq */##');	
					SET openValue = 1;
					SET closeValue = 59;
					SET incrementValue = CAST(SUBSTRING_INDEX(splitValue, '/', -1) AS UNSIGNED);

				ELSE SELECT utl_raise_error(concat(splitValue,' is not support cron minute format'));

			END CASE;
			
			-- validate opening occurse before closing. 
			
			IF(closeValue > openValue) THEN
				SELECT utl_raise_error(concat(splitValue,' format has invalid range once parsed'));
			END IF;


			-- insert the parsed range values into the tmp table
	
			CALL util_debug_msg(@bm_debug,concat('insert  bm_parsed_ranges'
												,' openValue:',openValue
												,' closeValue:',closeValue
												,' incrementValue:',incrementValue
												));	

			INSERT INTO bm_parsed_ranges (ID,range_open,range_closed,mod_value,value_type) 
			VALUES (null,openValue,closeValue,incrementValue,'minute');
			
			-- increment the loop
			SET i = i +1;

		END WHILE;
		
		CALL util_debug_msg(@bm_debug,'finished split value loop');	

	END IF;

END $$

DELIMITER ;

-- -----------------------------------------------------
-- procedure util_debug_msg
-- -----------------------------------------------------
DROP procedure IF EXISTS `util_debug_msg`;

DELIMITER $$


CREATE PROCEDURE util_debug_msg(enabled INTEGER, msg VARCHAR(255))
BEGIN
  IF enabled THEN BEGIN
    select concat("** ", msg) AS '** DEBUG:';
  END; END IF;
END $$

DELIMITER ;

-- -----------------------------------------------------
-- procedure bm_schedule_change_primary_group
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_schedule_change_primary_group`;

DELIMITER $$



create procedure `bm_schedule_change_primary_group` (IN scheduleID INT
													,IN scheduleGroupID INT)
BEGIN
	
	update schedule_group_relations set is_primary_group = 1
	where schedule_id = scheduleID 
	and schedule_group_id = scheduleGroupID;

END$$

DELIMITER ;

-- -----------------------------------------------------
-- procedure bm_schedule_find_primary_group
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_schedule_find_primary_group`;

DELIMITER $$


create procedure `bm_schedule_find_primary_group`(IN scheduleID INT)
BEGIN
	
	SELECT g.group_id, g.group_name , g.valid_from , g.valid_to
	FROM schedule_group_relations r 
	JOIN schedule_groups g on g.group_id = r.group_id
	WHERE is_primary_group = 1
	AND schedule_group = scheduleID; 	

END$$

DELIMITER ;

-- -----------------------------------------------------
-- procedure bm_schedule_retire_group
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_schedule_retire_group`;

DELIMITER $$


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

DELIMITER ;

-- -----------------------------------------------------
-- procedure bm_relate_group_to_schedule
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_relate_group_to_schedule`;

DELIMITER $$


CREATE PROCEDURE `bm_relate_group_to_schedule` (IN scheduleID INT
												,IN groupID INT
												,IN isPrimaryGroup TINYINT)
BEGIN
	
	-- Insert the relation record, the FK and PK will stop bad group and duplicates

	INSERT INTO schedule_group_relations (schedule_group_id, schedule_id,is_primary_group) 
		VALUES (groupID,scheduleID,isPrimaryGroup);
	

END$$

DELIMITER ;

-- -----------------------------------------------------
-- procedure bm_schedule_unlink_schedule_from_group
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_schedule_unlink_schedule_from_group`;

DELIMITER $$



CREATE PROCEDURE `bm_schedule_unlink_schedule_from_group` (IN scheduleID INT
															,IN groupID INT)
BEGIN
	
	-- check if the schedule is linked to any jobs


END$$

DELIMITER ;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `timeslots`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `timeslots` (`timeslot_id`, `timeslot_length`) VALUES (NULL, 15);
INSERT INTO `timeslots` (`timeslot_id`, `timeslot_length`) VALUES (NULL, 30);
INSERT INTO `timeslots` (`timeslot_id`, `timeslot_length`) VALUES (NULL, 45);
INSERT INTO `timeslots` (`timeslot_id`, `timeslot_length`) VALUES (NULL, 60);
INSERT INTO `timeslots` (`timeslot_id`, `timeslot_length`) VALUES (NULL, 90);
INSERT INTO `timeslots` (`timeslot_id`, `timeslot_length`) VALUES (NULL, 120);

COMMIT;


-- -----------------------------------------------------
-- Data for table `ints`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `ints` (`i`) VALUES (0);
INSERT INTO `ints` (`i`) VALUES (1);
INSERT INTO `ints` (`i`) VALUES (2);
INSERT INTO `ints` (`i`) VALUES (3);
INSERT INTO `ints` (`i`) VALUES (4);
INSERT INTO `ints` (`i`) VALUES (5);
INSERT INTO `ints` (`i`) VALUES (6);
INSERT INTO `ints` (`i`) VALUES (7);
INSERT INTO `ints` (`i`) VALUES (8);
INSERT INTO `ints` (`i`) VALUES (9);

COMMIT;

