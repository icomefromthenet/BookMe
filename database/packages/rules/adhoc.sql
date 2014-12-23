-- -----------------------------------------------------
-- procedure for Rules/Adhoc Package
-- -----------------------------------------------------
DELIMITER $$


-- -----------------------------------------------------
-- procedure bm_rules_adhoc_add_rule
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_adhoc_add_rule`$$

CREATE PROCEDURE `bm_rules_adhoc_add_rule`(IN ruleName VARCHAR(45), IN ruleType VARCHAR(45),  IN validFrom DATE,IN validTo DATE,IN ruleDuration INT, OUT newRuleID INT)
BEGIN
	
	DECLARE repeatValue VARCHAR(10) DEFAULT 'adhoc';
	DECLARE rowsSlotsAdded INT DEFAULT 0;

	-- Create the debug table
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log(concat('Starting bm_rules_adhoc_add_rule'));
	END IF;

	
	IF bm_rules_valid_rule_type(ruleType) = false THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Given ruleType is invalid';	
	END IF;
	
	-- Assign defaults and check validity range
	
	IF validTo IS NULL THEN
		SET validTo = DATE('3000-01-01');
	ELSE 
	
		IF utl_is_valid_date_range(validFrom,validTo) = 0 THEN 
			SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'Validity period is and invalid range';
		END IF;
		
		SET validTo = (validTo + INTERVAL 1 DAY);
		
	END IF;

	

	-- insert member rule into the common rules table 
	INSERT INTO rules (`rule_id`,`rule_name`,`rule_type`,`rule_repeat`,`valid_from`,`valid_to`,`rule_duration`)
	VALUES (NULL,ruleName,ruleType,repeatValue,validFrom,validTo,ruleDuration);
	SET newRuleID = LAST_INSERT_ID();
	IF newRuleID = 0 OR newRuleID IS NULL THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Unable to insert common adhoc rule';
	END IF;
	
	-- insert into concret table
	INSERT INTO rules_adhoc (`rule_id`,`rule_name`,`rule_type`,`rule_repeat`,`valid_from`,`valid_to`,`rule_duration`)
	VALUES (newRuleID,ruleName,ruleType,repeatValue,validFrom,validTo,ruleDuration);


	IF @bm_debug = true THEN
		CALL util_proc_log(concat('Inserted new rule at:: *',ifnull(newRuleID,'NULL')));
		CALL util_proc_cleanup('finished procedure bm_rules_add_adhoc_rule');
	END IF;

END$$
