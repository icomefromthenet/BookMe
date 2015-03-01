
-- -----------------------------------------------------
-- Table `timeslots`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `timeslots` ;

CREATE TABLE IF NOT EXISTS `timeslots` (
  `timeslot_id` INT NOT NULL AUTO_INCREMENT COMMENT 'Table Primary key\n',
  `timeslot_length` INT NOT NULL COMMENT 'Number of minutes in the slot',
  PRIMARY KEY (`timeslot_id`),
  UNIQUE INDEX `timeslot_length_UNIQUE` (`timeslot_length` ASC))
ENGINE = InnoDB
COMMENT = 'This describes the intervals lengths ie timeslots that used  /* comment truncated */ /*by schedules*/';






-- -----------------------------------------------------
-- Table `timeslot_slots`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `timeslot_slots` ;

CREATE TABLE IF NOT EXISTS `timeslot_slots` (
  -- uses a closed:open interval format
  -- RI Tree

  `timeslot_slot_id` INT NOT NULL AUTO_INCREMENT,
  `opening_slot_id` INT NOT NULL,
  `closing_slot_id` INT NOT NULL,
  `timeslot_id` INT NOT NULL,
  
  -- Value of the fork node, used to part of the RI Tree
  `node` INT NOT NULL,
  
  -- Constraints and indexes
  PRIMARY KEY (`timeslot_slot_id`),
  
  -- RI Indexes
  INDEX `idx_timeslot_slots_ri_lowerUpper` (`timeslot_id`,`node`,`opening_slot_id`,`closing_slot_id`),
  INDEX `idx_timeslot_slots_ri_upperLower` (`timeslot_id`,`node`,`closing_slot_id`,`opening_slot_id`),
  
  -- Normal Constraints
  UNIQUE INDEX `timeslot_slots_uk1` (`timeslot_id` ASC,`opening_slot_id` ASC, `closing_slot_id` ASC),
  CONSTRAINT `fk_timeslot_slots_1`
    FOREIGN KEY (`timeslot_id`)
    REFERENCES `timeslots` (`timeslot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_timeslot_slots_2`
    FOREIGN KEY (`opening_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_timeslot_slots_3`
    FOREIGN KEY (`closing_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE = InnoDB
COMMENT = 'Groups our timeslots into slot groups.';
