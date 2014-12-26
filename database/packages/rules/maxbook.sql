-- -----------------------------------------------------
-- procedures for maxbook rule package
-- -----------------------------------------------------
DELIMITER $$

-- -----------------------------------------------------
-- procedure bm_rules_maxbook_add_rule
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_maxbook_add_rule`$$

CREATE PROCEDURE `bm_rules_maxbook_add_rule`( IN ruleName VARCHAR(45)
	                                    , IN validFrom DATE
										, IN validTo DATE
										, IN calendarType VARCHAR(45)
										, IN maxBookingNumber INT
										, OUT newRuleID INT )
BEGIN
	-- Create the debug table
	
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log('bm_rules_maxbook_add_rule');
	END IF;



	-- Assign defaults and check validity range
	
	IF validTo IS NULL THEN
		SET validTo = DATE('3000-01-01');
	ELSE 
		
		IF utl_is_valid_date_range(validFrom,validTo) = 0 THEN 
			SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'Validity period is and invalid range';
		END IF;
		
		SET validTo = validTo + INTERVAL 1 DAY;
		
	END IF;
	
	IF maxBookingNumber < 0 OR maxBookingNumber = 0 THEN
	    SIGNAL SQLSTATE '45000'
	    SET MESSAGE_TEXT = 'Max Booking Number must be gt 0';
	END IF;
	
	IF bm_rules_is_valid_calendar_type(calendarType) = false THEN
	    SIGNAL SQLSTATE '45000'
	    SET MESSAGE_TEXT = 'Calendar Type must be one of the following::day,week,month,year';
	END IF;
	
	
	-- insert into common rules table
	INSERT INTO `rules` (`rule_id`,`rule_name`,`rule_type`,`rule_repeat`,`valid_from`,`valid_to`,`rule_duration`)
	VALUES (NULL,ruleName,'maxbook','runtime',validFrom,validTo,0);
	SET newRuleID = LAST_INSERT_ID();
	IF newRuleID = 0 OR newRuleID IS NULL THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Unable to insert common padding rule';
	END IF;
	
	-- insert rule into concrete table
	INSERT INTO `rules_maxbook` (`rule_id`,`rule_name`,`rule_type`,`rule_repeat`,`valid_from`,`valid_to`,`max_bookings`,`calendar_period`)
	VALUES (newRuleID,ruleName,'maxbook','runtime',validFrom,validTo,maxBookingNumber,calendarType);
    IF ROW_COUNT() = 0 THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Unable to insert concrete maxbook rule';
	END IF;

	IF @bm_debug = true THEN
		CALL util_proc_log(concat('Inserted new rule at:: *',ifnull(newRuleID,'NULL')));
	
		CALL util_proc_cleanup('finished procedure bm_rules_maxbook_add_rule');
	END IF;


END$$
