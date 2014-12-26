-- -----------------------------------------------------
-- procedures for padding rule package
-- -----------------------------------------------------
DELIMITER $$

-- -----------------------------------------------------
-- procedure bm_rules_padding_add_rule
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_padding_add_rule`$$

CREATE PROCEDURE `bm_rules_padding_add_rule`( IN ruleName VARCHAR(45)
										, IN validFrom DATE
										, IN validTo DATE
										, IN durationBefore INT
										, IN durationAfter INT
										, OUT newRuleID INT )
BEGIN
	-- Create the debug table
	
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log('bm_rules_padding_add_rule');
	END IF;

	-- check if the rule type is in valid list

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

	-- check the duration is valid
	
	IF bm_rules_valid_duration(durationAfter) = false THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'The duration after is not in valid range between 1 minute and 1 year';
	END IF;
	
	IF bm_rules_valid_duration(durationBefore) = false THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'The duration before is not in valid range between 1 minute and 1 year';
	END IF;
	
	IF durationBefore = 0 AND durationAfter = 0 THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'No padding time has been specified both durations are eq to 0';
	END IF;
	
	-- insert into common rules table
	INSERT INTO rules (`rule_id`,`rule_name`,`rule_type`,`rule_repeat`,`valid_from`,`valid_to`,`rule_duration`)
	VALUES (NULL,ruleName,'padding','runtime',validFrom,validTo,0);
	SET newRuleID = LAST_INSERT_ID();
	IF newRuleID = 0 OR newRuleID IS NULL THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Unable to insert common padding rule';
	END IF;
	
	-- insert rule into concrete table
	INSERT INTO rules_padding (`rule_id`,`rule_name`,`rule_type`,`rule_repeat`,`valid_from`,`valid_to`,`before_duration`,`after_duration`)
	VALUES (newRuleID,ruleName,'padding','runtime',validFrom,validTo,durationBefore,durationAfter);
	IF ROW_COUNT() = 0 THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Unable to insert concrete padding rule';
	END IF;

	
	IF @bm_debug = true THEN
		CALL util_proc_log(concat('Inserted new rule at:: *',ifnull(newRuleID,'NULL')));
	
		CALL util_proc_cleanup('finished procedure bm_rules_padding_add_rule');
	END IF;


END$$
