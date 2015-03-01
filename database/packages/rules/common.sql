-- -----------------------------------------------------
-- procedure for package
-- -----------------------------------------------------
DELIMITER $$

-- -----------------------------------------------------
-- procedure `bm_rules_check_sequence_duplicate`
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_check_sequence_duplicate`$$

CREATE PROCEDURE `bm_rules_check_sequence_duplicate` (IN ruleID INT, OUT duplciateFound BOOL)
BEGIN
	DECLARE isDuplicateFound BOOLEAN DEFAULT FALSE;
	
	SET isDuplicateFound = EXISTS (
			-- Select all periods where there is a second (overlapping / start / finish) period
			-- the unique key on table will stop 'equal periods' (same open and closing slot).
			SELECT *
			FROM `rule_slots` r1
			WHERE 1 < (
				-- return a count 1
				SELECT COUNT(*)
				FROM `rule_slots` r2
				WHERE r2.`rule_id` = r1.`rule_id` -- correlated subquery
				-- as the rule and slot table use closed:open interval format
				-- we only need to use '<' comparison as the previous closing slot is always equal to the next opening slot.
				AND r1.`open_slot_id` < r2.`close_slot_id`
				AND r2.`open_slot_id` < r1.`close_slot_id`
			)
			AND r1.`rule_id` = ruleID);
	
	IF isDuplicateFound = 1 THEN
		SET  duplciateFound = TRUE;
   END IF;
END$$



-- -----------------------------------------------------
-- procedure bm_rules_depreciate_rule
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `bm_rules_depreciate_rule`$$

CREATE PROCEDURE `bm_rules_depreciate_rule` (IN ruleID INT ,IN validTo DATE)
BEGIN
	DECLARE validFrom DATE;
	DECLARE ruleRepeat VARCHAR(25);
	DECLARE ruleType VARCHAR(25);

	-- Create the debug table
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log(concat('Starting bm_rules_depreciate_rule'));
	END IF;
	
	SELECT `valid_from`,`rule_repeat`,`rule_type`
	FROM `rules` WHERE `rule_id` = ruleID 
	INTO validFrom, ruleRepeat, ruleType;
	
	IF @bm_debug = TRUE THEN
		CALL util_proc_log(concat('validFrom from is set too ',valid_from , ' ruleRepeat is ',ruleRepeat));
	END IF;

	-- verify the range is valid
	IF validFrom > validTo THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Depreciation date must be on or after today';
	ELSE 
		SET validTo = validTo + INTERVAL 1 DAY;
	END IF;

	
	-- do operation on common table
	UPDATE `rules` SET valid_to = validTo WHERE rule_id = ruleID;
	IF ROW_COUNT() = 0 THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Unable to set a depreciation date on a rule for common table';
	END IF;

	-- do operation on concrete table
	IF ruleRepeat = 'adhoc' THEN
	
		UPDATE `rules_adhoc` SET valid_to = validTo WHERE rule_id = ruleID;
		
	ELSEIF ruleRepeat = 'repeat' THEN
		
		UPDATE `rules_repeat` SET valid_to = validTo WHERE rule_id = ruleID;
		
	ELSEIF ruleRepeat = 'runtime' THEN
		
		IF ruleType = 'padding' THEN 
			
			UPDATE `rules_padding` SET valid_to = validTo WHERE rule_id = ruleID;
			
		ELSEIF ruleType = 'maxbook' THEN
			
			UPDATE `rules_maxbook` SET valid_to = validTo WHERE rule_id = ruleID;
			
		END IF;
	
	END IF;
	
	IF ROW_COUNT() = 0 THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Unable to set a depreciation date on a rule for concrete table';
	END IF;

	IF @bm_debug = true THEN
		CALL util_proc_cleanup('finished procedure bm_rules_depreciate_rule');
	END IF;

END$$


-- -----------------------------------------------------
-- procedure bm_rules_relate_member
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_relate_member`$$

CREATE PROCEDURE `bm_rules_relate_member`(IN ruleID INT, IN memberID INT)
BEGIN
	
	-- Create the debug table
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log(concat('Starting bm_rules_relate_member'));
	END IF;
	
	-- fk stop a bad insert
	INSERT INTO `rules_relations` (`rule_relation_id`,`rule_id`,`membership_id`) VALUES (NULL,ruleID,memberID);
	
	IF @bm_debug = true THEN
		CALL util_proc_cleanup(concat('Inserted member relation for rule at ID::',ruleID,' for member:: ',ifnull(memberID,'NULL')));
	END IF;

END$$

-- -----------------------------------------------------
-- procedure bm_rules_relate_group
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_relate_group`$$

CREATE PROCEDURE `bm_rules_relate_group`(IN ruleID INT, IN scheduleGroupID INT)
BEGIN

	-- Create the debug table
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log(concat('Starting bm_rules_relate_group'));
	END IF;

	-- Need to do a validity date check, the FK relation won't stop the relations between
	-- a active rule and an in-active group. 
	INSERT INTO `rules_relations` (`rule_relation_id`,`rule_id`,`schedule_group_id`)
	SELECT NULL,ruleID,`g`.`group_id`
	FROM schedule_groups `g`
	WHERE `g`.`valid_from` <= NOW()
	AND `g`.`valid_to` > NOW()
	AND `g`.`group_id` = scheduleGroupID;
	
	IF ROW_COUNT() = 0 THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Unable to relate rule to schedule group as rule may have a bad validity range';
	END IF;
	
	IF @bm_debug = true THEN
		CALL util_proc_cleanup(concat('Inserted member relation for rule at ID::',ruleID,' for schedule group:: ',ifnull(scheduleGroupID,'NULL')));
	END IF;

END$$

          

-- -----------------------------------------------------
-- procedure bm_rules_find_affected_schedules
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_find_affected_schedules` $$

CREATE PROCEDURE `bm_rules_find_affected_schedules`(IN afterDate DATETIME)
BEGIN

	DECLARE l_last_row_fetched INT DEFAULT 0;
	DECLARE ruleID INT DEFAULT NULL;	
	DECLARE changed_rules_cursor CURSOR FOR SELECT rule_id FROM bm_changed_rules;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET l_last_row_fetched=1;
	
	-- Create the debug table
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log(concat('starting bm_rules_find_affected_schedules'));
	END IF;

	IF CAST(NOW() AS DATE) < afterDate THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'The date param must be before NOW';
	END IF;

	-- table will hold a list of rules changed
	DROP TEMPORARY TABLE IF EXISTS `bm_changed_rules`;
	CREATE TEMPORARY TABLE `bm_changed_rules` (
		rule_id INT NOT NULL PRIMARY KEY
	) ENGINE=MEMORY;

	-- clear the result table
	TRUNCATE `schedules_affected_by_changes`;
	
	
	-- find all rules that have changed since datetime
	INSERT INTO `bm_changed_rules` (`rule_id`) 
	-- find repeat rules that have been insert/updated/deleted
		SELECT DISTINCT `rr`.`rule_id` 
		FROM audit_rules_repeat rr
		WHERE `rr`.`rule_type` IN ('inclusion','exclusion')
		AND `rr`.`change_time` >= afterDate
		UNION ALL
		-- find adhoc rules that been updated/inserted/deleted
		SELECT DISTINCT `ar`.`rule_id`
		FROM audit_rules_adhoc `ar`
		WHERE `ar`.`rule_type` IN ('inclusion','exclusion')
		AND `ar`.`change_time` >= afterDate
		UNION ALL
		-- find padding rules changed
		SELECT DISTINCT `ap`.`rule_id`
		FROM audit_rules_padding `ap`
		WHERE `ap`.`change_time` >= afterDate
		UNION ALL
		-- find maxbook rules changed
		SELECT DISTINCT `amb`.`rule_id`
		FROM audit_rules_maxbook `amb`
		WHERE `amb`.`change_time` >= afterDate
		UNION 
		-- find rules that have had slot operations
		SELECT DISTINCT `op`.`rule_id`
		FROM rule_slots_operations op
		JOIN rules r ON `r`.rule_id = `op`.`rule_id`
		WHERE `r`.`rule_type` IN ('inclusion','exclusion')
		AND `op`.`change_time` >= afterDate
		-- find rules that been related to new members and schedules
		UNION
		SELECT DISTINCT `rel`.`rule_id`
		FROM `audit_rules_relations` rel
		JOIN rules r ON `rel`.rule_id = `r`.`rule_id`
		WHERE `r`.`rule_type` IN ('inclusion','exclusion')
		AND `rel`.`change_time` >= afterDate;
	
	
	-- find schedules that are linked to those rules, check if we have
	-- inserted them already
	
	
	SET l_last_row_fetched=0;
	OPEN changed_rules_cursor;
		cursor_loop:LOOP

		FETCH changed_rules_cursor INTO ruleID;
		IF l_last_row_fetched=1 THEN
			LEAVE cursor_loop;
		END IF;

		IF @bm_debug = true THEN
			CALL util_proc_log(concat('finding schedules for changed rule at::',ifnull(ruleID,'null')));
		END IF;

		-- find schedules that rule relates too.
		-- A schedule can be related either through the schedule group or a member.
		-- We also find any removed relationships from the rule relations audit table, for example removing 
		-- an inclusion rule would count as a change to a schedule
		
		
		INSERT INTO `schedules_affected_by_changes` (`schedule_id`,`date_known`)
		SELECT DISTINCT `s`.`schedule_id`,NOW()
		FROM schedules s
		JOIN (SELECT `membership_id`,`schedule_group_id` 
			  FROM rules_relations 
			  WHERE `rule_id` = ruleID
			  UNION
			  SELECT membership_id,schedule_group_id 
			  FROM audit_rules_relations
			  WHERE `rule_id` = ruleID 
			  AND `action` = 'D'
			  AND `change_time` >= afterDate
			  GROUP BY `rule_id`,`membership_id`,`schedule_group_id`
			  
		) 
		-- a rule can be related to a member OR schedule group while schedule must be related to both.
		a ON `a`.`membership_id` = `s`.`membership_id` OR `a`.`schedule_group_id` = `s`.`schedule_group_id`
		-- schedule table using closed:open interval format
		-- Filter out schedules that are closed and whould not be affected by this change, as they could not
		-- have bookings after the close date
		WHERE `s`.`closed_on` >= afterDate
		AND NOT EXISTS (SELECT 1 
		               FROM schedules_affected_by_changes 
		               WHERE `schedule_id`=`s`.`schedule_id`);
		

		END LOOP cursor_loop;
	CLOSE changed_rules_cursor;
	SET l_last_row_fetched=0;

	-- cleanup internal tmp table
	DROP TABLE IF EXISTS `bm_changed_rules`;


	IF @bm_debug = true THEN
		CALL util_proc_cleanup('finished procedure bm_rules_find_affected_schedules');
	END IF;

END$$
                                            

