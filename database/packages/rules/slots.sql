-- -----------------------------------------------------
-- procedure for Rules/Slots Package
-- -----------------------------------------------------
DELIMITER $$


-- -----------------------------------------------------
-- procedure bm_rules_slots_cleanup
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_slots_cleanup`$$

CREATE PROCEDURE `bm_rules_slots_cleanup`(IN ruleID INT, OUT rowsAffected INT)

BEGIN

	-- Create the debug table
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log('Starting bm_rules_cleanup_slots');
	END IF;


	-- record operation in log
	INSERT INTO rule_slots_operations (`change_seq`,`operation`,`change_time`,`changed_by`,`rule_id`) 
	VALUES (NULL,'clean',NOW(),USER(),ruleID);

	-- remove all slots for this rule
	DELETE FROM rule_slots WHERE rule_id = ruleID;
	
	-- Where not going to throw and error as this method could be used
	-- to remove an invalid rule, leave up to calling code to decide.
	SET rowsAffected = ROW_COUNT();
	

	IF @bm_debug = true THEN
		CALL util_proc_cleanup(concat('finished procedure bm_rules_cleanup_slots for rule at::',ruleID,' removed ',rowsAffected));
	END IF;

END$$



-- -----------------------------------------------------
-- procedure bm_rules_slots_remove
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_slots_remove`$$

CREATE PROCEDURE `bm_rules_slots_remove` (IN ruleID INT, IN openingSlotID INT, IN closingSlotID INT, OUT rowsAffected INT)
BEGIN
	DECLARE minSlotID INT;
	DECLARE maxSlotID INT;

	-- Create the debug table
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log(concat('Starting bm_rules_remove_slots' 
								  ,' with openslot::',ifnull(openingSlotID,0)
		                          ,' and closingslot::',ifnull(closingSlotID,0)));
	END IF;


	-- find the matching period(s) from rules table and get opending and closing slots.
	-- the given slot params could be values that (overlap | equal |start | finish ) within other periods in the table.
	SELECT min(`open_slot_id`),max(`close_slot_id`) FROM rule_slots 
	WHERE `rule_id` = ruleID
	-- rows that begin before deletion period and end after it
	OR (`open_slot_id` < openingSlotID AND `close_slot_id` > closingSlotID);
	-- rows that begin before deletion period and end within it
	OR (`open_slot_id` < openingSlotID  AND `close_slot_id` <= closingSlotID)
	-- rows that begin within deletion period and end after it
	OR(`open_slot_id` >= openingSlotID  AND `close_slot_id` >= closingSlotID)
	-- rows within the deletion period
	OR (`open_slot_id` >= openingSlotID AND `close_slot_id` <= closingSlotID)
	-- but selected values into variables
	INTO minSlotID,maxSlotID;
	
	
	-- record operation in slot log 
	INSERT INTO rule_slots_operations (`opening_slot_id`,`closing_slot_id`,`rule_id`,`change_seq`,`operation`,`change_time`,`changed_by`) 
	VALUES (openingSlotID,closingSlotID,ruleID,NULL,'subtraction',NOW(),USER());

	-- remove all rule intervals that are between these slots.
	-- since rules are not allowed to overlap if speciify and exact open/close it only remove single interval for rule X
	DELETE FROM rule_slots 
	WHERE `open_slot_id` >= openingSlotID 
	AND `close_slot_id` <= closingSlotID
	AND `rule_id` = ruleID;
	
	-- Where not going to throw and error leave up to calling code to decide.
	-- this is to keep it consistent wil cleanup slots method
	SET rowsAffected = ROW_COUNT();
	
	IF @bm_debug = true THEN
		CALL util_proc_log(concat('Removed ',ifnull(rowsAffected,0),' number of slots to rule id:: ',ruleID));
	END IF;
	
	IF @bm_debug = true THEN
		CALL util_proc_cleanup('finished procedure bm_rules_remove_slots');
	END IF;

END$$

-- -----------------------------------------------------
-- procedure bm_rules_slots_add
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_rules_slots_add`$$

CREATE PROCEDURE `bm_rules_slots_add` (IN ruleID INT
                                      ,IN openingSlotID INT
                                      ,IN closingSlotID INT
                                      ,OUT rowsAffected INT)
BEGIN
	DECLARE duplciateFound BOOL DEFAULT FALSE;
	
	-- Create the debug table
	IF @bm_debug = true THEN
		CALL util_proc_setup();
		CALL util_proc_log(concat('Starting bm_rules_add_slots with openslot::'
		                          ,ifnull(openingSlotID,0)
		                          ,' and closingslot::'
		                          ,ifnull(closingSlotID,0)
		                  ));
	END IF;

	
	-- record operation in slot log if opening and closing slot been provided
	INSERT INTO rule_slots_operations (`opening_slot_id`,`closing_slot_id`,`rule_id`,`change_seq`,`operation`,`change_time`,`changed_by`) 
	VALUES (openingSlotID,closingSlotID,ruleID,NULL,'addition',NOW(),USER());


	-- If this is a duplicate set, the unique index `uk_rule_slots` will stop the insert
	-- this is an inclusive operation (min <= expr AND expr <= max) so total record ([max-min]+1)
	INSERT INTO rule_slots (`rule_slot_id`,`rule_id`,`open_slot_id`,`close_slot_id`) 
	VALUES(NULL,ruleID,openingSlotID,closingSlotID);
	
	-- Verify that no overlaps of periods, the unique key only stop rule equal periods.
	-- We still need to catch periods that (start / finish / overlap), thus making them SEQUENCED DUPLICATES
	CALL bm_rules_check_sequence_duplicate(ruleID,duplciateFound);
	IF duplciateFound = true THEN
		-- be up to the user to handle this error and do cleanup
   		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Overlapping Rule Interval found, make sure to cleanup this table by either rolling back the transaction or execute a slot clean';
   END IF;
	
	
	-- Where not going to throw and error leave up to calling code to decide.
	-- this is to keep it consistent wil cleanup slots method
	SET rowsAffected = ROW_COUNT();
	
	IF @bm_debug = true THEN
		CALL util_proc_log(concat('Added ',ifnull(rowsAffected,0),' number of slots to rule id:: ',ruleID));
	END IF;
	
	IF @bm_debug = true THEN
		CALL util_proc_cleanup('finished procedure bm_rules_add_slots');
	END IF;

END$$

