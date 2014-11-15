-- -----------------------------------------------------
-- procedure bm_rules_parse_minute
-- -----------------------------------------------------
DELIMITER $$
DROP procedure IF EXISTS `bm_rules_parse_minute`$$

CREATE PROCEDURE `bm_rules_parse_minute`(IN cron VARCHAR(100))
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