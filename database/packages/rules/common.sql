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
			FROM rule_slots AS r1
			WHERE 1 < (
				-- return a count 1
				SELECT COUNT(*)
				FROM rule_slots AS r2
				WHERE r2.rule_id = r1.rule_id -- correlated subquery
				-- as the rule and slot table use closed:open interval format
				-- we only need to use '<' comparison as the previous closing slot is always equal to the next opening slot.
				AND r1.open_slot_id < r2.close_slot_id
				AND r2.open_slot_id < r1.close_slot_id
			)
			AND r1.rule_id = ruleID);
	
	IF isDuplicateFound = 1 THEN
		SET  duplciateFound = TRUE;
   END IF;
END$$



-- -----------------------------------------------------
-- procedure bm_rules_depreciate_rule
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `bm_rules_depreciate_rule`$$

CREATE PROCEDURE `bm_rules_depreciate_rule` (IN ruleID INT,IN validTo DATE)
BEGIN
	DECLARE validFrom DATE;
	DECLARE ruleRepeat VARCHAR(25);

	-- Create the debug table
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log(concat('Starting bm_rules_depreciate_rule'));
	END IF;
	
	SELECT `valid_from`,`rule_repeat` 
	FROM `rules` 
	WHERE `rule_id` = ruleID 
	INTO validFrom, ruleRepeat;
	
	IF @bm_debug = TRUE THEN
		CALL util_proc_log(concat('validFrom from is set too ',valid_from , ' ruleRepeat is ',ruleRepeat));
	END IF;

	-- verify the range is valid
	IF validFrom > validTo THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Depreciation date must be on or after today';
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
	ELSE 
		UPDATE `rules_repeat` SET valid_to = validTo WHERE rule_id = ruleID;
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
-- procedure bm_rules_timeslot_details
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_timeslot_details`$$

CREATE PROCEDURE `bm_rules_timeslot_details`(IN timeslotSlotID INT
                                            ,IN memberID INT
                                            ,IN groupID INT
                                            ,IN ruleType VARCHAR(20))
BEGIN 
	
	-- fetch a list of rules that affects the given timeslot.
	-- this is a detail view of a single timeslot and rules that interset it

    -- Using timeslot here and not slots as common use case to group slots into
    -- timeslots and display those to the user so summary and detail info should
    -- map to timelsots to make easier on library implementors.

	IF memberID IS NULL AND groupID IS NULL THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Either a member or a schedule group must be supplied';
	END IF;
	
	IF ruleType Is NOT NULL 
	   AND bm_rules_is_exclusion(ruleType) = false
	   AND bm_rules_is_inclusion(ruleType) = false 
	   AND bm_rules_is_priority(ruleType)  = false THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Either a valid rule type or none must be supplied';
	END IF;
	
	SELECT * 
	FROM rules r
	JOIN (SELECT `rl`.`rule_id` AS rule_id
		FROM timeslot_slots ts
		JOIN  timeslots t ON `t`.`timeslot_id` = `ts`.`timeslot_id`
		JOIN slots s ON `s`.`slot_id` BETWEEN `ts`.`opening_slot_id` and `ts`.`closing_slot_id`
		JOIN rule_slots rs ON `rs`.`slot_id` = `s`.`slot_id` 
		JOIN  rules rl ON `rl`.`rule_id` = `rs`.`rule_id`
		WHERE `ts`.`timeslot_slot_id` = timeslotSlotID
	    AND (memberID IS NULL OR `rl`.`membership_id` = memberID)
		AND  (groupID IS NULL OR `rl`.`schedule_group_id` = groupID)
		AND (ruleType IS NULL OR `rl`.`rule_type` = ruleType)
		AND `rl`.`valid_from` <= CAST(NOW() AS DATE)
		AND `rl`.`valid_to` >= CAST(NOW() AS DATE)
		GROUP BY `rl`.`rule_id`
		) fr ON `fr`.`rule_id` = `r`.`rule_id`
	ORDER BY `r`.`valid_from`;


END$$

-- -----------------------------------------------------
-- procedure bm_rules_rule_by_timeslot
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_rule_by_timeslot`$$

CREATE PROCEDURE `bm_rules_rule_by_timeslot`(IN openTimeslotSlotID INT
											,IN closingTimeslotSlotID INT
                                            ,IN ruleID INT)
BEGIN 

	-- Returns a summary of the slots that a rule affects
	-- projected over a series of timeslots in the range openTimeslotSlotID to closingTimeslotSlotID

 	-- Assumes that timeslot_slot_id are sequential for a single slot
 	
 	-- if you wanted to looup by date you first need to find the actual timeslot slots for that date fetch their ids
 	-- and use them as params for this procedure

	SELECT`sl`.`timeslot_slot_id`
	       , max(`sl`.`timeslot_id`) as timeslot_id
	       , if(ifnull(`rs`.`rule_id`,0)>0,1,0) as has_rule
	       , min(`s`.`slot_open`) as slot_open
	       , max(`s`.`slot_close`) as slot_close 
	FROM slots s
	-- expand out the timeslot range and fetch our slots to allow comparison with rules
	JOIN timeslot_slots sl ON (`s`.`slot_id` BETWEEN `sl`.`opening_slot_id` AND `sl`.`closing_slot_id`) 
	                       AND `sl`.`timeslot_slot_id` BETWEEN openTimeslotSlotID AND closingTimeslotSlotID
	-- match where timeslots slots intersect with slots allocated to the rule
	LEFT JOIN rule_slots rs ON `rs`.`slot_id` = `s`.`slot_id` 
	                        AND `rs`.`rule_id` = ruleID
	-- group them back again into timeslot groups
	GROUP BY `sl`.`timeslot_slot_id`;
	
	
	-- This more efficent query if your not looking for slot dates in the result sets
	--
	-- SELECT`sl`.`timeslot_slot_id`
    --   , max(`sl`.`timeslot_id`) as timeslot_id
    --   , if(ifnull(`rs`.`rule_id`,0)>0,1,0) as has_rule
	-- FROM timeslot_slots sl 
	-- LEFT JOIN rule_slots rs  on `rs`.`slot_id` BETWEEN `sl`.`opening_slot_id` AND `sl`.`closing_slot_id` AND `rs`.`rule_id` = ruleID
	-- WHERE `sl`.`timeslot_slot_id` between openTimeslotSlotID  and closingTimeslotSlotID
	-- GROUP BY `sl`.`timeslot_slot_id`;

END$$
          
          
-- -----------------------------------------------------
-- procedure bm_rules_by_timeslot_summary
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_by_timeslot_summary`$$

CREATE PROCEDURE `bm_rules_by_timeslot_summary`(IN openTimeslotSlotID INT
											   ,IN closingTimeslotSlotID INT
                                              )
BEGIN 

	-- projected over a series of timeslots in the range openTimeslotSlotID to closingTimeslotSlotID
	-- Provides a high level summary if the slot is affected: 
	-- 			A member rule inclusion / exclusion rule 
	--          A group inclusion /exclusion rule 
	--          IS a priority slot

 	
 	
 	
 	SELECT`sl`.`timeslot_slot_id`
       , max(`sl`.`timeslot_id`) as timeslot_id
       , if(ifnull(`rs`.`rule_id`,0)>0,1,0) as has_rule
	FROM timeslot_slots sl 
	LEFT JOIN rule_slots rs  on `rs`.`slot_id` BETWEEN `sl`.`opening_slot_id` AND `sl`.`closing_slot_id` 
	WHERE `sl`.`timeslot_slot_id` between 1  and 100
	GROUP BY `sl`.`timeslot_slot_id`;

 	
 	
END$$                                  

-- -----------------------------------------------------
-- procedure bm_rules_relate_member
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
		SELECT `rr`.`rule_id` 
		FROM audit_rules_repeat rr
		WHERE `rr`.`rule_type` IN ('inclusion','exclusion')
		AND `rr`.`change_time` >= afterDate
		UNION
		-- find adhoc rules that been updated/inserted/deleted
		SELECT `ar`.`rule_id`
		FROM audit_rules_adhoc `ar`
		WHERE `ar`.`rule_type` IN ('inclusion','exclusion')
		AND `ar`.`change_time` >= afterDate
		UNION
		-- find rules that have had slot operations
		SELECT `op`.`rule_id`
		FROM rule_slots_operations op
		JOIN rules r ON `r`.rule_id = `op`.`rule_id`
		WHERE `r`.`rule_type` IN ('inclusion','exclusion')
		AND `op`.`change_time` >= afterDate
		-- find rules that been related to new members and schedules
		UNION
		SELECT `rel`.`rule_id`
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
		
		
		INSERT INTO `schedules_affected_by_changes`
		SELECT `s`.`schedule_id`
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
		-- schedule table using open:close interval format
		WHERE `s`.`open_from` <= NOW() 
		AND `s`.`closed_on` > NOW() 
		AND NOT EXISTS (SELECT 1 
		              FROM schedules_affected_by_changes af 
		              WHERE `s`.`schedule_id` = `af`.`schedule_id`);

		END LOOP cursor_loop;
	CLOSE changed_rules_cursor;
	SET l_last_row_fetched=0;

	-- cleanup internal tmp table
	DROP TABLE IF EXISTS `bm_changed_rules`;


	IF @bm_debug = true THEN
		CALL util_proc_cleanup('finished procedure bm_rules_find_affected_schedules');
	END IF;

END$$
                                            
                                            