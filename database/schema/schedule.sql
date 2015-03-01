-- -----------------------------------------------------
-- Table `schedule_groups`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `schedule_groups` ;

CREATE TABLE IF NOT EXISTS `schedule_groups` (
  `group_id` INT NOT NULL AUTO_INCREMENT,
  `group_name` VARCHAR(45) NULL,
  `valid_from` DATE NOT NULL COMMENT 'frist date this group valid from',
  `valid_to` DATE NOT NULL COMMENT 'Last day group valid too',
  PRIMARY KEY (`group_id`),
  UNIQUE INDEX `tag_name_UNIQUE` (`group_name` ASC))
ENGINE = InnoDB
COMMENT = 'Ways to group schedules.';

-- -----------------------------------------------------
-- Table `audit_schedule_groups`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `audit_schedule_groups`;

CREATE TABLE IF NOT EXISTS `audit_schedule_groups` (
  -- audit fields
  `change_seq` INT NOT NULL AUTO_INCREMENT COMMENT 'Table Primary key\n',
  `action` CHAR(1) DEFAULT '',
  `change_time` TIMESTAMP NOT NULL,
  `changed_by` VARCHAR(100) NOT NULL COMMENT 'Database user not application user',

  -- group fields
  `group_id` INT NOT NULL,
  `group_name` VARCHAR(45) NULL,
  `valid_from` DATE NOT NULL COMMENT 'frist date this group valid from',
  `valid_to` DATE NOT NULL COMMENT 'Last day group valid too',
  PRIMARY KEY (`change_seq`))
ENGINE = InnoDB
COMMENT = 'Tracking log of the schedule groups table';

-- -----------------------------------------------------
-- Table `schedule_membership`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `schedule_membership` ;

CREATE TABLE IF NOT EXISTS `schedule_membership` (
  `membership_id` INT NOT NULL AUTO_INCREMENT,
  `registered_date` DATETIME NOT NULL,
  PRIMARY KEY (`membership_id`))
ENGINE = InnoDB
COMMENT = 'Used to group schedules by externel membership entity';




-- -----------------------------------------------------
-- Table `schedules`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `schedules` ;

CREATE TABLE IF NOT EXISTS `schedules` (
  `schedule_id` INT NOT NULL AUTO_INCREMENT COMMENT 'Table Primary key\n',
  `open_from` DATE NOT NULL COMMENT 'Date to start schedule on',
  `closed_on` DATE NOT NULL DEFAULT '3000-01-01' COMMENT 'Date schedule is not available',
  `schedule_group_id` INT NOT NULL,
  `membership_id` INT NOT NULL,
  PRIMARY KEY (`schedule_id`),
  INDEX `fk_schedules_1_idx` (`schedule_group_id` ASC),
  INDEX `fk_schedules_2_idx` (`membership_id` ASC),
  CONSTRAINT `fk_schedules_1`
    FOREIGN KEY (`schedule_group_id`)
    REFERENCES `schedule_groups` (`group_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_schedules_2`
    FOREIGN KEY (`membership_id`)
    REFERENCES `schedule_membership` (`membership_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'This contains a list of schedules that can have bookings';



-- -----------------------------------------------------
-- Table `schedules_rules_vw`
-- -----------------------------------------------------

DROP VIEW IF EXISTS `schedules_rules_vw`;

CREATE VIEW `schedules_rules_vw` AS
SELECT `s`.`schedule_id`, `r`.`rule_id`, `r`.`rule_type`, `r`.`rule_repeat`, `r`.`rule_name`, `r`.`rule_duration`
      ,`rs`.`schedule_group_id`, `rs`.`membership_id`
FROM `schedules` s 
JOIN `rules_relations` rs on (`rs`.`schedule_group_id` = `s`.`schedule_group_id` OR `rs`.`membership_id` = `s`.`membership_id`)
JOIN `rules` r ON `r`.`rule_id` = `rs`.`rule_id`	
WHERE `s`.`open_from` <= CAST(NOW() AS DATE) AND `s`.`closed_on` > CAST(NOW() AS DATE)
AND `r`.`valid_from` <= CAST(NOW() AS DATE) AND `r`.`valid_to`> CAST(NOW() AS DATE);
	
-- -----------------------------------------------------
-- Table `schedules_affected_by_changes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `schedules_affected_by_changes`;

CREATE TABLE IF NOT EXISTS `schedules_affected_by_changes`(
  `schedule_id` INT NOT NULL,
  `date_known` DATETIME NOT NULL,
  
  PRIMARY KEY (`schedule_id`),
  CONSTRAINT `fk_affected_schedules`
    FOREIGN KEY (`schedule_id`)
    REFERENCES `schedules` (`schedule_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
ENGINE = InnoDB
COMMENT = 'Schedules affected by last set of rule changes';
