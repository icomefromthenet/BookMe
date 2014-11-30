-- -----------------------------------------------------
-- procedure for package
-- -----------------------------------------------------
DELIMITER $$

-- -----------------------------------------------------
-- procedure bm_rules_parse
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_parse`$$

CREATE PROCEDURE `bm_rules_parse`(IN cron VARCHAR(100)
								 ,IN cronType VARCHAR(10))
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
		CALL util_proc_log(concat('Starting bm_rules_parse cron val::',ifnull(cron,'NULL'),' cronType::',ifnull(cronType,'NULL')));
		CALL util_proc_log(concat('Min::',minOpenValue,' max::',maxCloseValue));
	END IF;

	
	-- trim whitespace off cron val
	SET filteredCron = trim(cron);
	
	-- build tmp result tables if not done already
	CALL utl_create_rule_tmp_tables();
	
	
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
						CALL util_proc_log('not support cron minute format');
					END IF;	

					SIGNAL SQLSTATE '45000'
					SET MESSAGE_TEXT = 'not support cron minute format';	
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
										,' incrementValue:',incrementValue ));
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
-- procedure bm_rules_add_schedule_rule
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_add_schedule_rule`$$

CREATE PROCEDURE `bm_rules_add_schedule_rule`( 
										  IN ruleName VARCHAR(45)
										, IN ruleType VARCHAR(45)
										, IN repeatMinute VARCHAR(45)
										, IN repeatHour VARCHAR(45)
										, IN repeatDayofweek VARCHAR(45)
										, IN repeatDayofmonth VARCHAR(45)
										, IN repeatMonth VARCHAR(45)
										, IN repeatYear VARCHAR(45)
										, IN scheduleGroupID INT
										, OUT newRuleID INT )
BEGIN
	
	DECLARE repeatValue VARCHAR(10) DEFAULT 'repeat'; -- schedule group rules are always repeat
	
	-- Create the debug table
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log('Starting bm_rules_add_schedule_rule');
	END IF;

	IF bm_rules_valid_rule_type(ruleType) = false THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Given ruleType is invalid';	
	END IF;


	-- execute the parse fill tmp table
	CALL bm_rules_parse(repeatMinute,'minute');
	CALL bm_rules_parse(repeatHour,'hour');
	CALL bm_rules_parse(repeatDayofmonth,'dayofmonth');
	CALL bm_rules_parse(repeatDayofweek,'dayofweek');
	CALL bm_rules_parse(repeatMonth,'month');
	CALL bm_rules_parse(repeatYear,'year');


	-- insert schedule group rule into rules table
	-- the audit trigger will log it after insert

	INSERT INTO rules (
		 `rule_id`
		,`rule_name`
		,`rule_type`
		,`rule_repeat`
		,`created_date`
		,`updated_date`
		,`repeat_minute`
		,`repeat_hour`
		,`repeat_dayofweek`
		,`repeat_dayofmonth`
		,`repeat_month`
		,`repeat_year`
		,`schedule_group_id`
	)
	VALUES (
		 NULL
		,ruleName
		,ruleType
		,repeatValue
		,NOW()
		,NOW()
		,repeatMinute
		,repeatHour
		,repeatDayofweek
		,repeatDayofmonth
		,repeatMonth
		,repeatYear
		,scheduleGroupID
	);

	SET newRuleID = LAST_INSERT_ID();


	-- record operation in slot log
	INSERT INTO rule_slots_operations (
		`change_seq`
		,`operation`
		,`change_time`
		,`changed_by`
		,`rule_id`
	) 
	VALUES (
		NULL
		,'addition'
		,NOW()
		,USER()
		,newRuleID
	);
	
	
	-- insert slots calculated for this rule

	



	IF @bm_debug = true THEN
		CALL util_proc_cleanup('finished procedure bm_rules_add_schedule_rule');
	END IF;


END$$

-- -----------------------------------------------------
-- procedure bm_rules_add_member_rule
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_add_member_rule`$$

CREATE PROCEDURE `bm_rules_add_member_rule`(IN ruleName VARCHAR(45)
										, IN ruleType VARCHAR(45)
										, IN memberID INT
										, IN openingSlotID INT
										, IN closingSlotID INT
										, OUT newRuleID INT)
BEGIN
	
	DECLARE repeatRepeat VARCHAR(10) DEFAULT 'adhoc'; -- member rules are always adhoc


	-- Create the debug table
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log(concat('Starting bm_rules_add_member_rule'));
	END IF;

	
	IF bm_rules_valid_rule_type(ruleType) = false THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Given ruleType is invalid';	
	END IF;

	-- insert member rule into the rules table  the
	-- audit insert trigger will record the operation

		

	-- record operation in slot log if opening and closing slot been provided
	
	
	-- insert slots if opening and closing slot been provided

	IF @bm_debug = true THEN
		CALL util_proc_cleanup('finished procedure bm_rules_add_member_rule');
	END IF;


END$$

-- -----------------------------------------------------
-- procedure bm_rules_cleanup_slots
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_cleanup_slots`$$
CREATE PROCEDURE `bm_rules_cleanup_slots`(IN ruleID INT)
BEGIN

	-- Create the debug table
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log(concat('Starting bm_rules_cleanup_slots'));
	END IF;


	-- record operation in log


	-- do operation

	IF @bm_debug = true THEN
		CALL util_proc_cleanup('finished procedure bm_rules_cleanup_slots');
	END IF;

END$$

-- -----------------------------------------------------
-- procedure bm_rules_remove_slots
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_remove_slots`$$
CREATE PROCEDURE `bm_rules_remove_slots`( IN ruleID INT
                                         ,IN openingSlotID INT
                                         ,IN closingSlotID INT )
BEGIN
	
	-- Create the debug table
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log(concat('Starting bm_rules_remove_slots'));
	END IF;

		
	-- record operation in log
	
	-- do operation


	IF @bm_debug = true THEN
		CALL util_proc_cleanup('finished procedure bm_rules_remove_slots');
	END IF;

END$$

-- -----------------------------------------------------
-- procedure bm_rules_add_slots
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_add_slots`$$
CREATE PROCEDURE `bm_rules_add_slots`(IN ruleID INT
                                     ,IN openingSlotID INT
                                     ,IN closingSlotID INT)
BEGIN
	
	-- Create the debug table
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log(concat('Starting bm_rules_add_slots'));
	END IF;

	
	-- record operation in log


	-- do operation


	IF @bm_debug = true THEN
		CALL util_proc_cleanup('finished procedure bm_rules_add_slots');
	END IF;


END$$

