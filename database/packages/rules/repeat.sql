-- -----------------------------------------------------
-- procedure for Rules/Repeat Package
-- -----------------------------------------------------
DELIMITER $$

-- -----------------------------------------------------
-- procedure bm_rules_repeat_parse
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_repeat_parse`$$

CREATE PROCEDURE `bm_rules_repeat_parse`(IN cron VARCHAR(100), IN cronType VARCHAR(10))
BEGIN

	DECLARE filteredCron VARCHAR(100) DEFAULT '';
	DECLARE rangeOccurances INT DEFAULT NULL;
	DECLARE i INT DEFAULT 0;
	DECLARE splitValue VARCHAR(10);
	DECLARE openValue  INT DEFAULT 0;
    DECLARE closeValue INT DEFAULT 0;
    DECLARE incrementValue INT DEFAULT 0;
   	DECLARE minOpenValue INT;
	DECLARE maxCloseValue INT;
	DECLARE formatSTR VARCHAR(8) DEFAULT '*';

	-- fetch the default min and max range for this cron section
	SET minOpenValue = bm_rules_min(cronType);
	SET maxCloseValue = bm_rules_max(cronType);
	

	-- Create the debug table
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log(concat('Starting bm_rules_repeat_parse cron val::',ifnull(cron,'NULL'),' cronType::',ifnull(cronType,'NULL')));
		CALL util_proc_log(concat('Min::',minOpenValue,' max::',maxCloseValue));
	END IF;

	
	-- trim whitespace off cron val
	SET filteredCron = trim(cron);
	
	-- build tmp result tables if not done already
	CALL bm_rules_create_tmp_table(false);
	
	
	IF filteredCron = '*' THEN
	    IF @bm_debug = true THEN
			CALL util_proc_log('filteredCron is eq *');
		END IF;	

		-- test if we  have default * only
		-- insert the default range into the parsed ranges table
		INSERT INTO bm_parsed_ranges (id,range_open,range_closed,mod_value,value_type) 
		VALUES (NULL,minOpenValue, maxCloseValue ,1,cronType);

	ELSE 
		-- split our set and parse each range declaration.
		SET rangeOccurances = LENGTH(filteredCron) - LENGTH(REPLACE(filteredCron, ',', ''))+1;
		SET i = 1;
		SET incrementValue = 0;

		IF @bm_debug = true THEN
			CALL util_proc_log(concat('rangeOccurances eq to ',rangeOccurances));
		END IF;	

		WHILE i <= rangeOccurances DO
			SET splitValue = REPLACE(SUBSTRING(SUBSTRING_INDEX(filteredCron, ',', i),LENGTH(SUBSTRING_INDEX(filteredCron, ',', i - 1)) + 1), ',', '');
			
			
			IF @bm_debug = true THEN
				CALL util_proc_log(concat('splitValue at ',i ,' is eq ',splitValue));
			END IF;	
		
			
			-- find which range type we have
			CASE
				-- test for range with increment e.g 01-59/39
				WHEN splitValue REGEXP bm_rules_regex_pattern_a(cronType) THEN
			
					SET formatSTR =  '##-##/##';
					SET openValue = CAST(SUBSTRING_INDEX(splitValue, '-', 1) AS UNSIGNED);
					SET closeValue = CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(splitValue, '/', 1),'-',-1) AS UNSIGNED);				
					SET incrementValue = CAST(SUBSTRING_INDEX(splitValue, '/', -1) AS UNSIGNED);
				
				-- test for a scalar with increment e.g 6/3 (this short for 6-59/3)
				WHEN splitValue REGEXP bm_rules_regex_pattern_b(cronType) THEN
					
					SET formatSTR =  '##/##';
					SET openValue = CAST(SUBSTRING_INDEX(splitValue, '/', 1) AS UNSIGNED);
					SET closeValue = maxCloseValue;
					SET incrementValue = CAST(SUBSTRING_INDEX(splitValue, '/', -1) AS UNSIGNED);
				
				-- test a range with e.g 34-59
				WHEN splitValue REGEXP bm_rules_regex_pattern_c(cronType) > 0 THEN				
					
					SET formatSTR =  '##-##';
					SET openValue = CAST(SUBSTRING_INDEX(splitValue, '-', 1) AS UNSIGNED);
					SET closeValue = CAST(SUBSTRING_INDEX(splitValue, '-', -1) AS UNSIGNED);				
					SET incrementValue = 1;
					
				-- test for a scalar value
				WHEN splitValue REGEXP bm_rules_regex_pattern_d(cronType) > 0 THEN				
										
					SET formatSTR =  '##';
					SET openValue = CAST(splitValue AS UNSIGNED);
					SET closeValue = CAST(splitValue AS UNSIGNED);				
					SET incrementValue = 1;
					
				-- test for a * with increment e.g */5
				WHEN splitValue REGEXP bm_rules_regex_pattern_e(cronType) > 0 THEN
					
					
					SET formatSTR =  '*/##';
					SET openValue = minOpenValue;
					SET closeValue = maxCloseValue;
					SET incrementValue = CAST(SUBSTRING_INDEX(splitValue, '/', -1) AS UNSIGNED);

				ELSE 
					IF @bm_debug = true THEN
						CALL util_proc_log('not support cron format');
					END IF;	

					SIGNAL SQLSTATE '45000'
					SET MESSAGE_TEXT = 'not support cron format';	
			END CASE;
			
			
			IF @bm_debug = true THEN
				CALL util_proc_log(concat('splitValue  format is equal to ',ifnull(formatSTR,'UNKNOWN')));
			END IF;
			
			-- validate opening occurse before closing. 
			
			IF(closeValue < openValue) THEN
			
				IF @bm_debug = true THEN
					CALL util_proc_log(concat('openValue:',openValue
										,' closeValue:',closeValue
										,' incrementValue:',incrementValue ));
				END IF;	

				SIGNAL SQLSTATE '45000'
				SET MESSAGE_TEXT = 'format has invalid range once parsed';	
				
			END IF;


			-- insert the parsed range values into the tmp table
	
			IF @bm_debug = true THEN
				CALL util_proc_log(concat('insert  bm_parsed_ranges'
										,' openValue:',openValue
										,' closeValue:',closeValue
										,' incrementValue:',incrementValue));
			END IF;	


			INSERT INTO bm_parsed_ranges (ID,range_open,range_closed,mod_value,value_type) 
			VALUES (null,openValue,closeValue,incrementValue,cronType);
			
			-- increment the loop
			SET i = i +1;

		END WHILE;
		
		IF @bm_debug = true THEN
			CALL util_proc_cleanup('finished procedure bm_rules_parse');
		END IF;

	END IF;

END$$

-- -----------------------------------------------------
-- procedure bm_rules_repeat_create_tmp
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_repeat_create_tmp`$$

CREATE procedure `bm_rules_repeat_create_tmp` (IN expand TINYINT)
BEGIN
	
	IF expand IS NULL OR EXPAND = FALSE THEN
		CREATE TEMPORARY TABLE IF NOT EXISTS bm_parsed_ranges (
			id INT PRIMARY KEY AUTO_INCREMENT,
			range_open INT NOT NULL,
			range_closed INT NOT NULL,
			mod_value INT NULL,
			value_type ENUM('minute','hour','dayofmonth','dayofweek','month') NOT NULL
		) ENGINE=MEMORY;
		
	ELSE 
		
		CREATE TEMPORARY  TABLE IF NOT EXISTS bm_parsed_minute AS (SELECT * FROM bm_parsed_ranges WHERE value_type = 'minute');
		CREATE TEMPORARY  TABLE IF NOT EXISTS bm_parsed_hour AS (SELECT * FROM bm_parsed_ranges WHERE value_type = 'hour');
		CREATE TEMPORARY  TABLE IF NOT EXISTS bm_parsed_dayofmonth AS (SELECT * FROM bm_parsed_ranges WHERE value_type = 'dayofmonth');
		CREATE TEMPORARY  TABLE IF NOT EXISTS bm_parsed_dayofweek AS (SELECT * FROM bm_parsed_ranges WHERE value_type = 'dayofweek');
		CREATE TEMPORARY  TABLE IF NOT EXISTS bm_parsed_month AS (SELECT * FROM bm_parsed_ranges WHERE value_type = 'month');

	END IF;

END$$

-- -----------------------------------------------------
-- procedure bm_rules_repeat_cleanup_tmp
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_repeat_cleanup_tmp`$$

CREATE procedure `bm_rules_repeat_cleanup_tmp` ()
BEGIN

	DROP TEMPORARY TABLE IF EXISTS bm_parsed_ranges ;
	DROP TEMPORARY TABLE IF EXISTS bm_parsed_minute;
	DROP TEMPORARY TABLE IF EXISTS bm_parsed_hour;
	DROP TEMPORARY TABLE IF EXISTS bm_parsed_dayofmonth;
	DROP TEMPORARY TABLE IF EXISTS bm_parsed_dayofweek;
	DROP TEMPORARY TABLE IF EXISTS bm_parsed_month;
END$$

-- -----------------------------------------------------
-- procedure bm_rules_repeat_save_slots
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_repeat_save_slots`$$

CREATE PROCEDURE `bm_rules_repeat_save_slots`(IN ruleID INT
									 , OUT numberSlots INT
									 , IN repeatMinute VARCHAR(45)
									 , IN repeatHour VARCHAR(45)
									 , IN repeatDayofweek VARCHAR(45)
									 , IN repeatDayofmonth VARCHAR(45)
									 , IN repeatMonth VARCHAR(45)
									 , IN ruleDuration INT
									 , IN startFrom DATE
									 , IN endAt DATE)
BEGIN
	DECLARE isInsertError BOOL DEFAULT false;
	DECLARE duplciateFound BOOL DEFAULT false;
	DECLARE maxSlotID INT DEFAULT 0;
	DECLARE CONTINUE HANDLER FOR SQLSTATE '23000' SET isInsertError = true;
	
	
	-- Create the debug table
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log(concat('Starting bm_rules_repeat_save_slots'));
	END IF;
	
	-- Create Primary tmp table
	CALL bm_rules_repeat_create_tmp(false);
	
	CALL bm_rules_repeat_parse(repeatMinute,'minute');
	
	CALL bm_rules_repeat_parse(repeatHour,'hour');
	
	CALL bm_rules_repeat_parse(repeatDayofmonth,'dayofmonth');
	
	CALL bm_rules_repeat_parse(repeatDayofweek,'dayofweek');
	
	CALL bm_rules_repeat_parse(repeatMonth,'month');
	
	
	-- As Waround for MYSQL being unable to access a tmp table more
	-- than once in same query, call create again and ask
	-- that frist tmp table be split
	CALL bm_rules_repeat_create_tmp(true);
	
	-- insert the requird slots, internally MSQL will create a tmp table to hold
	-- values from select list
	
	SET maxSlotID = (SELECT MAX(slot_id) FROM slots);
	
	
	INSERT INTO rule_slots (rule_slot_id,rule_id,open_slot_id,close_slot_id) 	
	-- Each join on this query will filter the slots into smaller sets, for example
	-- the first join find all slots that are in the minute range 
	-- this set is reduced by 2nd join which filter frist set down to those 
	-- slots that meet the hour requirements And so on for each cron rule
	SELECT NULL
		  ,ruleID
		  , `sl`.`slot_id` 
		  -- using int match to fast cal on closing slot
		  ,if((sl.`slot_id` + ruleDuration) > maxSlotID, maxSlotID ,(sl.`slot_id` + ruleDuration)) AS close_slot
	FROM slots s
	RIGHT JOIN calendar c ON c.calendar_date = s.cal_date
	RIGHT JOIN bm_parsed_minute mr 
		    ON  EXTRACT(MINUTE FROM `s`.`slot_open`) >= `mr`.`range_open` 
			AND  EXTRACT(MINUTE FROM `s`.`slot_open`)   <= `mr`.`range_closed`
			AND  MOD(EXTRACT(MINUTE FROM `s`.`slot_open`),`mr`.`mod_value`) = 0
	RIGHT JOIN bm_parsed_hour hr 
			ON  EXTRACT(HOUR FROM `s`.`slot_open`) >= `hr`.`range_open` 
			AND  EXTRACT(HOUR FROM `s`.`slot_open`)   <= `hr`.`range_closed`
			AND  MOD(EXTRACT(HOUR FROM `s`.`slot_open`),`hr`.`mod_value`) = 0
	RIGHT JOIN bm_parsed_dayofmonth domr 
			ON  `c`.`d` >= `domr`.`range_open` 
			AND  `c`.`d`   <= `domr`.`range_closed`
			AND  MOD(`c`.`d`,`domr`.`mod_value`) = 0
	RIGHT JOIN bm_parsed_dayofweek dowr 
			ON  (`c`.`dw`-1) >= `dowr`.`range_open` 
			AND  (`c`.`dw`-1)   <= `dowr`.`range_closed`
			AND  MOD((`c`.`dw`-1),`dowr`.`mod_value`) = 0
	RIGHT JOIN bm_parsed_month monr 
			ON  `c`.`m` >= `monr`.`range_open` 
			AND  `c`.`m`   <= `monr`.`range_closed`
			AND  MOD(`c`.`m`,`monr`.`mod_value`) = 0
	-- faster to cast the endAt and startFrom to same type a date cast to dattime have 00:00:00 hours
	WHERE `sl`.`slot_open` >= CAST(startFrom AS DATETIME) AND `sl`.`slot_close` <= CAST(endAt as DATETIME);
 	
	-- assign insert rows to out param
	IF isInsertError = true THEN
		SET numberSlots  = 0;
	ELSE 
		SET numberSlots  = ROW_COUNT();
	END IF;
	
	-- verify overlaps
	CALL bm_rules_check_sequence_duplicate(ruleID,duplciateFound);
	IF duplciateFound = true THEN
			SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'A duplicate period has been found while setting up a repeating rule';
	END IF;
	
	
	IF @bm_debug = true THEN
		CALL util_proc_cleanup(concat('Inserted ',numberSlots,' slots for new rule at ID::',ruleID
		                             , 'with start date ',ifnull(startFrom,'NULL')
		                             ,' with end date of ',ifnull(endAt,'NULL')));
	END IF;

	
	-- drop tmp tables
	CALL bm_rules_repeat_cleanup_tmp();
	
END$$

-- -----------------------------------------------------
-- procedure bm_rules_repeat_add_rule
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_repeat_add_rule`$$

CREATE PROCEDURE `bm_rules_repeat_add_rule`( 
										  IN ruleName VARCHAR(45)
										, IN ruleType VARCHAR(45)
										, IN repeatMinute VARCHAR(45)
										, IN repeatHour VARCHAR(45)
										, IN repeatDayofweek VARCHAR(45)
										, IN repeatDayofmonth VARCHAR(45)
										, IN repeatMonth VARCHAR(45)
										, IN startFrom DATE
										, IN endAt DATE
										, IN ruleDuration INT
										, IN validFrom DATE
										, IN validTo DATE
										, OUT newRuleID INT )
BEGIN
	
	DECLARE repeatValue VARCHAR(10) DEFAULT 'repeat';
	DECLARE numberSlots INT DEFAULT 0;
	
	-- Create the debug table
	
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log('Starting bm_rules_repeat_add_rule');
	END IF;

	-- check if the rule type is in valid list

	IF bm_rules_valid_rule_type(ruleType) = false THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Given ruleType is invalid must be inclusion or exclusion or priority';	
	END IF;
	
	-- Assign defaults and check validity range
	
	IF validTo IS NULL THEN
		SET validTo = DATE('3000-01-01');
	END IF;

	IF validFrom < CAST(NOW() AS DATE) THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Valid from date must be gte NOW';
	END IF;
	
	IF validTo < validFrom THEN 
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Validity period is and invalid range';
	END IF;

	-- check the duration is valid
	
	IF bm_rules_valid_duration(ruleDuration) = false THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'The rule duration is not in valid range between 1minute and 1 year';
	END IF;
	
	-- check end date precesds the start date
	IF startFrom > endAt THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT ='The rule start date must occur before then given end date';
	END IF;

	-- insert into common rules table
	INSERT INTO rules (`rule_id`,`rule_name`,`rule_type`,`rule_repeat`,`valid_from`,`valid_to`,`rule_duration`)
	VALUES (NULL,ruleName,ruleType,repeatValue,validFrom,validTo,ruleDuration);
	SET newRuleID = LAST_INSERT_ID();
	IF newRuleID = 0 OR newRuleID IS NULL THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Unable to insert common adhoc rule';
	END IF;
	
	-- insert rule into concrete table
	INSERT INTO rules (`rule_id`,`rule_name`,`rule_type`,`rule_repeat`,`repeat_minute`,`repeat_hour`,`repeat_dayofweek`
		,`repeat_dayofmonth`,`repeat_month`,`valid_from`,`valid_to`,`rule_duration`,`start_from`,`end_at`)
	VALUES (newRuleID,ruleName,ruleType,repeatValue,repeatMinute,repeatHour,repeatDayofweek,repeatDayofmonth
		,repeatMonth,validFrom,validTo,ruleDuration,startFrom,endAt);
	
	IF @bm_debug = true THEN
		CALL util_proc_log(concat('Inserted new rule at:: *',ifnull(newRuleID,'NULL')));
	END IF;	

	-- record operation in slot log
	INSERT INTO rule_slots_operations (`change_seq`,`operation`,`change_time`,`changed_by`,`rule_id`) 
	VALUES (NULL,'addition',NOW(),USER(),newRuleID);
	
	IF @bm_debug = true THEN
		CALL util_proc_log(concat('Inserted new rule slot operation at::',ifnull(LAST_INSERT_ID(),'NULL')));
	END IF;	
	
	
	-- save slots for the new rule
	CALL bm_rules_repeat_save_slots(newRuleID,numberSlots,repeatMinute,repeatHour,repeatDayofweek,repeatDayofmonth,repeatMonth,ruleDuration,startFrom,endAt);
	
	
	IF @bm_debug = true THEN
		CALL util_proc_log(concat('Inserted ',ifnull(numberSlots,'NULL'),' rule slots for rule *',ifnull(newRuleID,'NULL')));
	END IF;	
	
	
	IF numberSlots = 0 THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'The new Rule did not have any slots to insert';
	END IF;
	

	IF @bm_debug = true THEN
		CALL util_proc_cleanup('finished procedure bm_rules_add_repeat_rule');
	END IF;


END$$
