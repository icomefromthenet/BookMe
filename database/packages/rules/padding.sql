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
										, IN afterSlots INT
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
	
	IF afterSlots = NULL OR afterSlots <= 0 THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'The number of padding slots must be gt 0';
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
	INSERT INTO rules_padding (`rule_id`,`rule_name`,`rule_type`,`rule_repeat`,`valid_from`,`valid_to`,`after_slots`)
	VALUES (newRuleID,ruleName,'padding','runtime',validFrom,validTo,afterSlots);
	IF ROW_COUNT() = 0 THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Unable to insert concrete padding rule';
	END IF;

	
	IF @bm_debug = true THEN
		CALL util_proc_log(concat('Inserted new rule at:: *',ifnull(newRuleID,'NULL')));
	
		CALL util_proc_cleanup('finished procedure bm_rules_padding_add_rule');
	END IF;


END$$

-- -----------------------------------------------------
-- procedure bm_rules_padding_create_tmp_table
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_padding_create_tmp_table`$$

CREATE PROCEDURE `bm_rules_padding_create_tmp_table`(IN openTimeslotSlotID INT,IN closeTimeslotSlotID INT)
BEGIN

	IF openTimeslotSlotID > closeTimeslotSlotID THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Clsoing timeslotSlot must proceed the opening slot id';
	END IF;


	DROP TEMPORARY TABLE IF EXISTS `schedule_padding_slots`;
	CREATE TEMPORARY TABLE `schedule_padding_slots` (
		`timeslot_slot_id` INT NOT NULL PRIMARY KEY,
		`open_slot_id` INT NOT NULL,
		`close_slot_id` INT NOT NULL,
		`is_pad` TINYINT DEFAULT 0,
		
		CONSTRAINT `fk_maxbook_slots_1`
    	FOREIGN KEY (`timeslot_slot_id`)
    	REFERENCES `timeslot_slots` (`timeslot_slot_id`)
	  	ON DELETE NO ACTION
    	ON UPDATE NO ACTION
    	
  	) ENGINE=MEMORY;
	
	-- build empty resuls table
	
	INSERT INTO `schedule_padding_slots` (`timeslot_slot_id`,`is_pad`,`open_slot_id`,`close_slot_id`)
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
-- procedure bm_rules_padding
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_padding`$$

CREATE PROCEDURE `bm_rules_padding`( IN scheduleID INT
 									,IN openTimeslotSlotID INT
                                    ,IN closetimeslotSlotID INT)
BEGIN
	
	DECLARE ruleID INT;
	DECLARE afterSlots INT;

	DECLARE l_last_row_fetched INT DEFAULT 0;
	
	DECLARE rulesCursor CURSOR FOR 
		SELECT `vw`.`rule_id`, `mb`.`after_slots`
		FROM `schedules_rules_vw` vw
		JOIN `rules_padding` mb ON `mb`.`rule_id` = `vw`.`rule_id`
		AND `vw`.`schedule_id` = scheduleID;
	
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET l_last_row_fetched=1;

	-- create the result table
	CALL bm_rules_padding_create_tmp_table(openTimeslotSlotID,closetimeslotSlotID);

	
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log('bm_rules_padding');
	END IF;
	
	SET l_last_row_fetched=0;
	OPEN rulesCursor;
		cursor_loop:LOOP

		FETCH rulesCursor INTO ruleID,afterSlots;
		
		IF l_last_row_fetched=1 THEN
			LEAVE cursor_loop;
		END IF;
		
		IF @bm_debug = true THEN	
				CALL util_proc_log(concat('Processing Padding rule for schedule::',scheduleID
										  , 'for rule::',ruleID, ' afterSlorts::',afterSlots));
		END IF;
		
		-- find which slots have a booking
		UPDATE schedule_padding_slots c 
		JOIN bookings b ON  `b`.`schedule_id`    = scheduleID
						-- tabes use closed:open the closing slot will be equal to next opening slot (gapless)
						AND  `c`.`open_slot_id` >=  `b`.`close_slot_id`  
		 				-- want to stop seleting slots that are after the booking closing slot + x number of padding slots
		 				AND  `c`.`close_slot_id` <  `b`.`close_slot_id` + afterSlots 
		SET is_pad = 1;
		

		END LOOP cursor_loop;
	CLOSE rulesCursor;
	SET l_last_row_fetched=0;
	
	
	IF @bm_debug = true THEN
		CALL util_proc_cleanup('bm_rules_padding');
	END IF;

END;
$$

