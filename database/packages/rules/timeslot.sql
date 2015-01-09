-- -----------------------------------------------------
-- procedure for Rues/Timeslot Package
-- -----------------------------------------------------
DELIMITER $$


-- -----------------------------------------------------
-- RI Tree Left/Right node finders.
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `bm_rules_timeslot_left_nodes`$$

CREATE PROCEDURE `bm_rules_timeslot_left_nodes`(lower INT, upper INT)
BEGIN

  	DECLARE treeHeight INT DEFAULT 0;
	DECLARE treeRoot INT DEFAULT 0; 
	DECLARE treeNode INT DEFAULT 0; 
	DECLARE searchStep INT DEFAULT 0;
	DECLARE tmpTable VARCHAR(30)  DEFAULT 'timeslot_left_nodes_result';
	
	SET treeHeight  = ceil(LOG2((SELECT MAX(`slot_id`) FROM `slots`)+1));
	SET treeRoot    = power(2,(treeHeight-1)); 
	SET treeNode    = treeRoot;
	SET searchStep  =  treeNode / 2;

    -- holds a colletion of forkNodes 
   	DROP TEMPORARY TABLE IF EXISTS `timeslot_left_nodes_result`;
    CREATE TEMPORARY TABLE `timeslot_left_nodes_result` (`node` INT NOT NULL PRIMARY KEY)ENGINE=MEMORY; 
    
    
   -- descend from root node to lower
   myloop:WHILE searchStep >= 1 DO
  
    -- right node
    IF lower < treeNode THEN
      SET treeNode = treeNode - searchStep;
  
    -- left node
    ELSEIF lower > treeNode THEN
  
      INSERT INTO `timeslot_left_nodes_result`(node) VALUES(treeNode);
      SET treeNode = treeNode + searchStep;
  
    -- lower
    ELSE LEAVE myloop;
    END IF;  

    SET searchStep = searchStep / 2;
  
  END WHILE myloop;

END;
$$


DROP PROCEDURE IF EXISTS `bm_rules_timeslot_right_nodes`$$

CREATE PROCEDURE `bm_rules_timeslot_right_nodes`(lower INT, upper INT)
BEGIN

  	DECLARE treeHeight INT DEFAULT 0;
	DECLARE treeRoot INT DEFAULT 0; 
	DECLARE treeNode INT DEFAULT 0; 
	DECLARE searchStep INT DEFAULT 0;
	DECLARE tmpTable VARCHAR(30)  DEFAULT 'timeslot_left_nodes_result';
	
	SET treeHeight  = ceil(LOG2((SELECT MAX(`slot_id`) FROM `slots`)+1));
	SET treeRoot    = power(2,(treeHeight-1)); 
	SET treeNode    = treeRoot;
	SET searchStep  =  treeNode / 2;

    -- holds a colletion of forkNodes 
   	DROP TEMPORARY TABLE IF EXISTS `timeslot_right_nodes_result`;
    CREATE TEMPORARY TABLE `timeslot_right_nodes_result` (`node` INT NOT NULL PRIMARY KEY)ENGINE=MEMORY; 
    
    
   -- descend from root node to lower
   myloop:WHILE searchStep >= 1 DO
  
    -- right node
    IF upper > treeNode THEN
      SET treeNode = treeNode + searchStep;
  
    -- left node
    ELSEIF upper < treeNode THEN
  
      INSERT INTO `timeslot_right_nodes_result`(node) VALUES(treeNode);
      SET treeNode = treeNode - searchStep;
  
    -- lower
    ELSE LEAVE myloop;
    END IF;  

    SET searchStep = searchStep / 2;
  
  END WHILE myloop;

END;
$$

-- -----------------------------------------------------
-- procedure bm_rules_timeslot_details
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_timeslot_details`$$

CREATE PROCEDURE `bm_rules_timeslot_details` (IN timeslotSlotID INT
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
	FROM rules rr
	JOIN (SELECT `r`.`rule_id`
		FROM `timeslot_slots` ts
		-- as both table use closed:open interval format we need to match closing interval using 'lte' 
		JOIN `rule_slots` rs ON `ts`.`opening_slot_id` >= `rs`.`open_slot_id` 
								AND `ts`.`closing_slot_id` <= `rs`.`close_slot_id`
		JOIN `rules` r on `r`.`rule_id` = `rs`.`rule_id`
		WHERE `ts`.`timeslot_slot_id` = timeslotSlotID
		AND EXISTS (
			SELECT 1
			FROM `rules_relations` rl 
			WHERE `r`.`rule_id` = `rl`.`rule_id`
			AND (memberID IS NULL OR `rl`.`membership_id` = memberID)
			AND (groupID IS NULL OR `rl`.`schedule_group_id` = groupID)
		)
		AND (ruleType IS NULL OR `r`.`rule_type` = ruleType)
		AND `r`.`valid_from` <= CAST(NOW() AS DATE)
		AND `r`.`valid_to` > CAST(NOW() AS DATE)
		GROUP BY `r`.`rule_id`
	) fr ON `fr`.`rule_id` = `rr`.`rule_id`
	ORDER BY `rr`.`valid_from`;


END;
$$

-- -----------------------------------------------------
-- procedure bm_rules_timeslot_groups
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_timeslot_groups`$$

CREATE PROCEDURE `bm_rules_timeslot_groups`(IN openTimeslotSlotID INT
											,IN closingTimeslotSlotID INT
										    ,IN ruleID INT)
BEGIN 

	-- Returns a summary of the slots that a rule affects
	-- projected over a series of timeslots in the range openTimeslotSlotID to closingTimeslotSlotID

 	-- Assumes that timeslot_slot_id are sequential for a single slot
 	
	SELECT 
		`ts`.`timeslot_slot_id`
		, max(`ts`.`timeslot_id`) as timeslot_id
		, max(if(`rs`.`rule_id`>0,1,0)) as has_rule
		, max(if(bm_rules_is_exclusion(`r`.`rule_type`)=true, 1,0)) as has_exclusion
		, max(if(bm_rules_is_inclusion(`r`.`rule_type`)=true, 1,0)) as has_inclusion
		, max(if(bm_rules_is_priority(`r`.`rule_type`)=true, 1,0)) as has_priority
	FROM `timeslot_slots` ts
	-- where looking for rule slot intervals that intersect a given timeslot interval and as rule interval can easly 
	-- occur over many timeslots we only want join to work on slots that occur after start and before closing of our single timeslot
	-- and as both tables using closed:open interval format the opening never equal closing use the '<' in second comparison
	JOIN `rule_slots` rs ON `ts`.`opening_slot_id` >= `rs`.`open_slot_id` AND `ts`.`opening_slot_id` < `rs`.`close_slot_id`
	JOIN `rules` r ON `r`.`rule_id` = `rs`.`rule_id`
	WHERE `ts`.`timeslot_slot_id` >= openTimeslotSlotID  AND  `ts`.`timeslot_slot_id` <= closingTimeslotSlotID
	AND `r`.`rule_id` = ruleID
	GROUP BY `ts`.`timeslot_slot_id`;
	
END;
$$
          
          
-- -----------------------------------------------------
-- procedure bm_rules_timeslot_summary
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_timeslot_summary`$$

CREATE PROCEDURE `bm_rules_timeslot_summary`(IN openTimeslotSlotID INT
											, IN closingTimeslotSlotID INT
											, IN timeslotID INT
                                           	, IN groupID  INT
											, IN memberID INT 
                                           )
BEGIN 

	-- projected over a series of timeslots in the range openTimeslotSlotID to closingTimeslotSlotID
	-- Provides a high level summary if the slot is affected: 
	-- 			A member rule inclusion / exclusion rule 
	--          A group inclusion /exclusion rule 
	--          IS a priority slot

 	
 	SELECT 
		`ts`.`timeslot_slot_id`
		, max(`ts`.`timeslot_id`) as timeslot_id
		, max(if(`rs`.`rule_id`>0,1,0)) as has_rule
		, max(if(bm_rules_is_exclusion(`r`.`rule_type`)=true, 1,0)) as has_exclusion
		, max(if(bm_rules_is_inclusion(`r`.`rule_type`)=true, 1,0)) as has_inclusion
		, max(if(bm_rules_is_priority(`r`.`rule_type`)=true, 1,0)) as has_priority
	FROM `timeslot_slots` ts
	-- where looking for rule slot intervals that intersect a given timeslot interval and as rule interval can easly 
	-- occur over many timeslots we only want join to work on slots that occur after start and before closing of our single timeslot
	-- and as both tables using closed:open interval format the opening never equal closing use the '<' in second comparison
	JOIN `rule_slots` rs ON `ts`.`opening_slot_id` >= `rs`.`open_slot_id` AND `ts`.`opening_slot_id` < `rs`.`close_slot_id`
	JOIN `rules` r ON `r`.`rule_id` = `rs`.`rule_id`
	WHERE `ts`.`timeslot_slot_id` >= openTimeslotSlotID  
	AND  `ts`.`timeslot_slot_id` <= closingTimeslotSlotID
	AND `ts`.`timeslot_id` = timeslotID
	AND EXISTS (
		SELECT 1
		FROM `rules_relations` rl 
		WHERE `r`.`rule_id` = `rl`.`rule_id`
		AND (memberID IS NULL OR `rl`.`membership_id` = memberID)
		AND (groupID IS NULL OR `rl`.`schedule_group_id` = groupID)
	)
	AND `r`.`valid_from` <= CAST(NOW() AS DATE)
	AND `r`.`valid_to` > CAST(NOW() AS DATE)
	GROUP BY `ts`.`timeslot_slot_id`;

 	
 	
END;
$$      


-- -----------------------------------------------------
-- procedure bm_rules_timeslot_create_tmp_table
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_timeslot_create_tmp_table`$$

CREATE PROCEDURE `bm_rules_timeslot_create_tmp_table`(IN openTimeslotSlotID INT,IN closeTimeslotSlotID INT)
BEGIN

	IF openTimeslotSlotID > closeTimeslotSlotID THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Clsoing timeslotSlot must proceed the opening slot id';
	END IF;


	DROP TEMPORARY TABLE IF EXISTS `schedule_timeslot_details`;
	CREATE TEMPORARY TABLE `schedule_timeslot_details` (
		`timeslot_slot_id` INT NOT NULL PRIMARY KEY,
		`open_slot_id` INT NOT NULL,
		`close_slot_id` INT NOT NULL,
		
		-- slot detail
		`is_member_exclusion` INT NOT NULL DEFAULT 0,
		`is_member_inclusion` INT NOT NULL DEFAULT 0,
		`is_group_exclusion` INT NOT NULL DEFAULT 0,
		`is_group_inclusion` INT NOT NULL DEFAULT 0,
		`is_member_priority` INT NOT NULL DEFAULT 0,
		`is_group_priority` INT NOT NULL DEFAULT 0,
		
		
		CONSTRAINT `fk_maxbook_slots_1`
    	FOREIGN KEY (`timeslot_slot_id`)
    	REFERENCES `timeslot_slots` (`timeslot_slot_id`)
	  	ON DELETE NO ACTION
    	ON UPDATE NO ACTION
    	
  	) ENGINE=MEMORY;
	
	-- build empty resuls table
	INSERT INTO `schedule_timeslot_details` (`timeslot_slot_id`,`is_pad`,`open_slot_id`,`close_slot_id`)
	SELECT   `s`.`timeslot_slot_id`
			, 0
			,`s`.`opening_slot_id`
			,`s`.`close_slot_id`
	FROM `timeslot_slots` s
	WHERE `s`.`timeslot_slot_id` >= openTimeslotSlotID
	AND `s`.`timeslot_slot_id` <= closeTimeslotSlotID;

END;
$$


-- -----------------------------------------------------
-- procedure bm_rules_timeslot
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_timeslot`$$

CREATE PROCEDURE `bm_rules_timeslot`(IN scheduleID INT
									,IN openTimeslotSlotID INT
									,IN closeTimeslotSlotID INT)
BEGIN
	DECLARE ruleID INT;
	DECLARE isMember BOOL;
	DECLARE isScheduleGroup BOOL;
	DECLARE isInclusion  BOOL;
	DECLARE isExclusion BOOL;
	DECLARE isPriority BOOL;

	DECLARE l_last_row_fetched INT DEFAULT 0;
	
	DECLARE rulesCursor CURSOR FOR 
		SELECT    `vw`.`rule_id`
				, bm_rules_is_member(`vw`.`membership_id`,`vw`.`schedule_group_id`) AS is_member
				, bm_rules_is_schedule_group(`vw`.`membership_id`,`vw`.`schedule_group_id`) AS is_schedule_group
				, bm_rules_is_inclusion(`vw`.`rule_type`) AS is_inclusion
				, bm_rules_is_exclusion(`vw`.`rule_type`) AS is_exclusion
				, bm_rules_is_priority (`vw`.`rule_type`) AS is_priority
		FROM `schedules_rules_vw` vw
		LEFT JOIN `rules_adhoc` mba ON `mba`.`rule_id` = `vw`.`rule_id`
		LEFT JOIN `rules_repeat` mbb ON `mbb`.`rule_id` = `vw`.`rule_id`
		AND `vw`.`schedule_id` = scheduleID
		AND (`mbb`.`rule_id` IS NOT NULL OR `mba`.`rule_id` IS NOT NULL);
		
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET l_last_row_fetched=1;

	-- create the result table
	CALL bm_rules_timeslot_create_tmp_table(openTimeslotSlotID,closetimeslotSlotID);

	
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log('bm_rules_timeslot');
	END IF;
	
	-- update the detail columns
	
	SET l_last_row_fetched=0;
	OPEN rulesCursor;
		cursor_loop:LOOP

		FETCH rulesCursor INTO ruleID,isMember,isScheduleGroup,isInclusion,isExclusion,isPriority;
		
		IF l_last_row_fetched=1 THEN
			LEAVE cursor_loop;
		END IF;
		
		IF @bm_debug = true THEN	
				CALL util_proc_log(concat('Processing Padding rule for schedule::',scheduleID
										  , 'for rule::',ruleID));
		END IF;
		
		-- find timeslot slots interset the rule slots.
	/*
		UPDATE schedule_timeslot_details p
		WHERE EXISTS (SELECT 1 
		              FROM `rule_slots` q
		              WHERE `q`.`rule_id` = ruleID 
			  		  -- BOTH TABLES USING [closed:open)
			  		  -- TimeslotSlots: [p1 = `p`.`open_slot_id`, p2 = `p`.`close_slot_id`)
			  		  -- Ruleslots    : [q1 = `q`.`open_slot_id`, q2 = `q`.`close_slot_id`) 
			  		  
			  		  -- 1. timeslot slot START same time as rule period (finished within).
			  		  -- p1 = q1 AND p2 < q2
			  		  OR (`p`.`open_slot_id` = `q`.`open_slot_id` AND `p`.`close_slot_id` < `q`.`close_slot_id`)
			  		  
			  		  -- 2. timeslot slot FINISH same time as the rule period (starts within).
			  		  -- q1 < p1 AND p2 =q2
					  OR (`q`.`open_slot_id` < `p`.`open_slot_id`  AND `p`.`close_slot_id` = `q`.`close_slot_id`)	
					
					  -- 3. timeslot slot occurs (starts and finishes) DURING the rule period.
					  -- q1 < p1 AND p2 < q2 
					  OR (`q`.`open_slot_id` < `p`.`open_slot_id` AND  `p`.`close_slot_id` < `q`.`close_slot_id`)	
		
		 			  -- 4. timeslot slot EQUALS the rule.
					  -- p1 = q1 AND p2 = q2
					  OR (`p`.`open_slot_id` =  `q`.`open_slot_id` AND `p`.`close_slot_id` = `q`.`close_slot_id`)
					  
					  
					  -- 5. timeslot slot OVERLAPS with rule.
					  -- (p1 < q1 AND q1 < p2 OR q1 < p1 AND p1 < q2)
					  OR (`p`.`open_slot_id` < `q`.`open_slot_id` AND `q`.`open_slot_id` < `p`.`close_slot_id`
					        OR `q`.`open_slot_id` < `p`.`open_slot_id` AND `p`.`open_slot_id` < `q`.`close_slot_id`)
		
					 
		SET 
		 `is_member_exclusion`  = CAST((isExclusion && isMember) AS INTEGER) 
		,`is_member_inclusion` =  CAST((isInclusion && isMember) AS INTEGER) 
		
		,`is_group_exclusion`  = CAST((isExclusion && isScheduleGroup) AS INTEGER)
		,`is_group_inclusion`  = CAST((isInclusion && isScheduleGroup) AS INTEGER) 
		
		,`is_member_priority`  = CAST((isPriority && isMember) AS INTEGER)
		,`is_group_priority`   = CAST((isPriority && isScheduleGroup) AS INTEGER); 
		*/
	
		END LOOP cursor_loop;
	CLOSE rulesCursor;
	SET l_last_row_fetched=0;
	
	
	-- update the summary columns
	
	
	IF @bm_debug = true THEN
		CALL util_proc_cleanup('bm_rules_timeslot');
	END IF;


END;
$$
