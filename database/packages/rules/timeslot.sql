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
	   AND bm_rules_is_priority(ruleType)  = false 
	   AND bm_rules_is_maxbook(ruleType)  = false 
	   AND bm_rules_is_padding(ruleType)  = false THEN
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
-- procedure bm_rules_timeslot_summary
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_timeslot_summary`$$

CREATE PROCEDURE `bm_rules_timeslot_summary`(IN openTimeslotSlotID INT
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