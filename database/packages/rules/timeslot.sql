-- -----------------------------------------------------
-- procedure for Rues/Timeslot Package
-- -----------------------------------------------------
DELIMITER $$


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


END$$

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
	
END$$
          
          
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

 	
 	
END$$      