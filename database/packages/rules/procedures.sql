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


	-- Create the debug table
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log(concat('Starting bm_rules_parse cron val::',ifnull(cron,'NULL'),' cronType::',ifnull(cronType,'NULL')));
	END IF;

	-- fetch the default min and max range for this cron section
	SET minOpenValue = bm_rules_min(cronType);
	SET maxCloseValue = bm_rules_max(cronType);
	
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
-- procedure bm_rules_parse_hour
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_parse_hour`$$

CREATE PROCEDURE `bm_rules_parse_hour`(IN cron VARCHAR(100))
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
	
	-- build tmp result tables if not done already
	CALL utl_create_rule_tmp_tables();
	
    
	IF filteredCron = '*' THEN
	    IF @bm_debug = true THEN
			CALL util_proc_log('filteredCron is eq *');
		END IF;	

		-- test if we  have default * only
		-- insert the default range into the parsed ranges table
		INSERT INTO bm_parsed_ranges (id,range_open,range_closed,mod_value,value_type) 
		VALUES (NULL,0,23,0,'minute');

	ELSE 
		-- split our set and parse each range declaration.
		SET i = 1;
		SET openValue = 0;
		SET closeValue = 0;
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
				-- test for range with increment e.g 01-23/5
				WHEN splitValue REGEXP '^([0-1][0-9]|[2][0-3]{1}|[0-9]{1})-([0-1][0-9]|[2][0-3]{1}|[0-9]{1})/([0-1][0-9]|[2][0-3]{1}|[0-9]{1})$'  > 0 THEN
				
					IF @bm_debug = true THEN
						CALL util_proc_log('splitValue eq ##-##/##');
					END IF;	

					SET openValue = CAST(SUBSTRING_INDEX(splitValue, '-', 1) AS UNSIGNED);
					SET closeValue = CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(splitValue, '/', 1),'-',-1) AS UNSIGNED);				
					SET incrementValue = CAST(SUBSTRING_INDEX(splitValue, '/', -1) AS UNSIGNED);
				
				-- test for a scalar with increment e.g 23/3 this short for 0-23/3
				WHEN splitValue REGEXP '^([0-1][0-9]|[2][0-3]{1}|[0-9]{1})/([0-1][0-9]|[2][0-3]{1}|[0-9]{1})$' > 0 THEN
					
					IF @bm_debug = true THEN
						CALL util_proc_log('splitValue eq ##/##');
					END IF;	

					
					SET openValue = CAST(SUBSTRING_INDEX(splitValue, '/', 1) AS UNSIGNED);
					SET closeValue = 59;
					SET incrementValue = CAST(SUBSTRING_INDEX(splitValue, '/', -1) AS UNSIGNED);
				
				-- test a range with e.g 34-59
				WHEN splitValue REGEXP '^([0-1][0-9]|[2][0-3]{1}|[0-9]{1})-([0-1][0-9]|[2][0-3]{1}|[0-9]{1})$' > 0 THEN				
					
					IF @bm_debug = true THEN
						CALL util_proc_log('splitValue eq ##-##');
					END IF;	

					SET openValue = CAST(SUBSTRING_INDEX(splitValue, '-', 1) AS UNSIGNED);
					SET closeValue = CAST(SUBSTRING_INDEX(splitValue, '-', -1) AS UNSIGNED);				
					
				-- test for a scalar value
				WHEN splitValue REGEXP '^([0-1][0-9]|[2][0-3]{1}|[0-9]{1})$' > 0 THEN				
										
					IF @bm_debug = true THEN
						CALL util_proc_log('splitValue eq ##');
					END IF;	

					
					SET openValue = CAST(splitValue AS UNSIGNED);
					SET closeValue = CAST(splitValue AS UNSIGNED);				
								
				-- test for a * with increment e.g */5
				WHEN splitValue REGEXP '^([*]{1})/([0-1][0-9]|[2][0-3]{1}|[0-9]{1})$' > 0 THEN
					
					IF @bm_debug = true THEN
						CALL util_proc_log('splitValue eq */##');
					END IF;	

					SET openValue = 1;
					SET closeValue = 59;
					SET incrementValue = CAST(SUBSTRING_INDEX(splitValue, '/', -1) AS UNSIGNED);

				ELSE 
					IF @bm_debug = true THEN
						CALL util_proc_log('not support cron hour format');
					END IF;	

					SIGNAL SQLSTATE '45000'
					SET MESSAGE_TEXT = 'not support cron hour format';	
			END CASE;
			
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
			VALUES (null,openValue,closeValue,incrementValue,'hour');
			
			-- increment the loop
			SET i = i +1;

		END WHILE;
		
		IF @bm_debug = true THEN
			CALL util_proc_cleanup('finished procedure bm_rules_parse_hour');
		END IF;

	END IF;

END$$

-- -----------------------------------------------------
-- procedure bm_rules_parse_monthday
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_parse_monthday`$$

CREATE PROCEDURE `bm_rules_parse_monthday`(IN cron VARCHAR(100))
BEGIN


END$$

-- -----------------------------------------------------
-- procedure bm_rules_parse_weekday
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_parse_weekday`$$

CREATE PROCEDURE `bm_rules_parse_weekday`(IN cron VARCHAR(100))
BEGIN


END$$


-- -----------------------------------------------------
-- procedure bm_rules_parse_month
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_parse_month`$$

CREATE PROCEDURE `bm_rules_parse_month`(IN cron VARCHAR(100))
BEGIN


END$$


-- -----------------------------------------------------
-- procedure bm_rules_parse_year
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_parse_year`$$

CREATE PROCEDURE `bm_rules_parse_year`(IN cron VARCHAR(100))
BEGIN


END$$