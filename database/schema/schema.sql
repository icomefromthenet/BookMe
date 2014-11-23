SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


-- -----------------------------------------------------
-- Table `calendar`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `calendar` ;

CREATE TABLE IF NOT EXISTS `calendar` (
  `calendar_date` DATE NOT NULL COMMENT 'date and table key',
  `y` SMALLINT NULL COMMENT 'year where date occurs',
  `q` TINYTEXT NULL COMMENT 'quarter of the year date belongs',
  `m` TINYINT NULL COMMENT 'month of the year',
  `d` TINYINT NULL COMMENT 'numeric date part',
  `dw` TINYINT NULL COMMENT 'day number of the date in a week',
  `month_name` VARCHAR(9) NULL COMMENT 'text name of the month',
  `day_name` VARCHAR(9) NULL COMMENT 'text name of the day\n',
  `w` TINYINT NULL COMMENT 'week number in the year',
  `is_week_day` TINYINT NULL COMMENT 'true value if current date falls between monday-friday\n',
  PRIMARY KEY (`calendar_date`))
ENGINE = InnoDB
COMMENT = 'Calender table that store the next 10 years of dates';


-- -----------------------------------------------------
-- Table `slots`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `slots` ;

CREATE TABLE IF NOT EXISTS `slots` (
  `slot_id` INT NOT NULL AUTO_INCREMENT COMMENT 'Table primary key',
  `cal_date` DATE NOT NULL COMMENT 'Date this slot occurs on used to join date time',
  `slot_open` DATETIME NOT NULL COMMENT 'Opending Interval of this slot',
  `slot_close` DATETIME NOT NULL COMMENT 'closing internal of slot',
  PRIMARY KEY (`slot_id`),
  INDEX `fk_slots_1_idx` (`cal_date` ASC),
  CONSTRAINT `fk_slots_1`
    FOREIGN KEY (`cal_date`)
    REFERENCES `calendar` (`calendar_date`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'The common slots table, each slot is the minium slot duratio /* comment truncated */ /*n of 1 minute. */';


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
  `change_seq` INT NOT NULL AUTO_INCREMENT COMMENT 'Table Primary key\n',
  `action` CHAR(1) DEFAULT '',
  `change_time` TIMESTAMP NOT NULL,
  `changed_by` VARCHAR(100) NOT NULL COMMENT 'Database user not application user',
  `group_id` INT NOT NULL,
  `group_name` VARCHAR(45) NULL,
  `valid_from` DATE NOT NULL COMMENT 'frist date this group valid from',
  `valid_to` DATE NOT NULL COMMENT 'Last day group valid too',
  PRIMARY KEY (`change_seq`))
ENGINE = InnoDB
COMMENT = 'Tracking log of the schedule groups table';


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
  `timeslot_id` INT NULL,
  `open_from` DATE NOT NULL COMMENT 'Date to start schedule on',
  `closed_on` DATE NOT NULL DEFAULT '3000-01-01' COMMENT 'Date schedule is not available',
  `schedule_group_id` INT NOT NULL,
  `membership_id` INT NOT NULL,
  PRIMARY KEY (`schedule_id`),
  INDEX `fk_schedules_1_idx` (`timeslot_id` ASC),
  INDEX `fk_schedules_2_idx` (`schedule_group_id` ASC),
  INDEX `fk_schedules_3_idx` (`membership_id` ASC),
  CONSTRAINT `fk_schedules_1`
    FOREIGN KEY (`timeslot_id`)
    REFERENCES `timeslots` (`timeslot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_schedules_2`
    FOREIGN KEY (`schedule_group_id`)
    REFERENCES `schedule_groups` (`group_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_schedules_3`
    FOREIGN KEY (`membership_id`)
    REFERENCES `schedule_membership` (`membership_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'This contains a list of schedules that can have bookings';


-- -----------------------------------------------------
-- Table `timeslot_slots`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `timeslot_slots` ;

CREATE TABLE IF NOT EXISTS `timeslot_slots` (
  `timeslot_slot_id` INT NOT NULL AUTO_INCREMENT,
  `opening_slot_id` INT NOT NULL,
  `closing_slot_id` INT NOT NULL,
  `timeslot_id` INT NOT NULL,
  PRIMARY KEY (`timeslot_slot_id`),
  INDEX `fk_timeslot_slots_1_idx` (`timeslot_id` ASC),
  INDEX `fk_timeslot_slots_2_idx` (`opening_slot_id` ASC),
  INDEX `fk_timeslot_slots_3_idx` (`closing_slot_id` ASC),
  UNIQUE INDEX `iimeslot_slots_uk1` (`opening_slot_id` ASC, `closing_slot_id` ASC, `timeslot_id` ASC),
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
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Groups our timeslots into slot groups.';


-- -----------------------------------------------------
-- Table `exclusion_rules`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `exclusion_rules` ;

CREATE TABLE IF NOT EXISTS `exclusion_rules` (
  `exclusion_rule_id` INT NOT NULL AUTO_INCREMENT,
  `rule_name` VARCHAR(50) NULL COMMENT 'name for this rule',
  `valid_from` DATE NOT NULL COMMENT 'opening application date',
  `valid_to` DATE NOT NULL DEFAULT '3000-01-01' COMMENT 'closing application date\n',
  `apply_on` TINYINT NOT NULL DEFAULT 0 COMMENT '0 = weekday/weekend\n1 = weekend\n2 = weekday',
  `exclude_length` INT NOT NULL COMMENT 'number of minutes to exclude\nuse minutes as our common slot is 1 minute durations',
  `repeat_minute` VARCHAR(50) NOT NULL DEFAULT 0 COMMENT 'min (0 - 59)',
  `repeat_hour` VARCHAR(50) NOT NULL DEFAULT 0 COMMENT 'hour (0 - 23)',
  `repeat_dayofweek` VARCHAR(50) NOT NULL DEFAULT 0 COMMENT 'day of week (0 - 6) (0 to 6 are Sunday to Saturday, or use names; 7 is Sunday, the same as 0)',
  `repeat_month` VARCHAR(50) NOT NULL DEFAULT 0 COMMENT 'day of week (0 - 6) (0 to 6 are Sunday to Saturday, or use names; 7 is Sunday, the same as 0)',
  `repeat_dayofmonth` VARCHAR(50) NOT NULL DEFAULT 0 COMMENT 'day of month (1 - 31)',
  `repeat_year` VARCHAR(50) NOT NULL DEFAULT 0,
  `schedule_group_id` INT(11) NOT NULL,
  PRIMARY KEY (`exclusion_rule_id`),
  UNIQUE INDEX `rule_name_UNIQUE` (`rule_name` ASC),
  INDEX `fk_exclusion_rules_1_idx` (`schedule_group_id` ASC),
  CONSTRAINT `fk_exclusion_rules_1`
    FOREIGN KEY (`schedule_group_id`)
    REFERENCES `schedule_groups` (`group_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Describes the rules that exclude timeslots';


-- -----------------------------------------------------
-- Table `inclusion_rules`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `inclusion_rules` ;

CREATE TABLE IF NOT EXISTS `inclusion_rules` (
  `inclusion_rule_id` INT NOT NULL AUTO_INCREMENT,
  `rule_name` VARCHAR(45) NOT NULL,
  `valid_from` DATE NOT NULL,
  `valid_to` DATE NOT NULL DEFAULT '3000-01-01',
  `interval_start` TIME NULL COMMENT 'starting slot that is open',
  `interval_length` TIME NULL COMMENT 'last slot time to include in rule\n',
  `repeat_minute` VARCHAR(45) NOT NULL,
  `repeat_hour` VARCHAR(45) NOT NULL,
  `repeat_dayofweek` VARCHAR(45) NOT NULL,
  `repeat_dayofmonth` VARCHAR(45) NOT NULL,
  `repeat_month` VARCHAR(45) NOT NULL,
  `repeat_year` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`inclusion_rule_id`),
  UNIQUE INDEX `rule_name_UNIQUE` (`rule_name` ASC))
ENGINE = InnoDB
COMMENT = 'Rules that mark intervals to are available to schedule\n';


-- -----------------------------------------------------
-- Table `inclusion_overrides`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `inclusion_overrides` ;

CREATE TABLE IF NOT EXISTS `inclusion_overrides` (
  `inclusion_overrides_id` INT NOT NULL AUTO_INCREMENT,
  `schedule_id` INT NOT NULL,
  `slot_id` INT NOT NULL,
  PRIMARY KEY (`inclusion_overrides_id`),
  INDEX `fk_inclusion_overrides_1_idx` (`slot_id` ASC),
  INDEX `fk_inclusion_overrides_2_idx` (`schedule_id` ASC),
  UNIQUE INDEX `inclusion_overrides_uk1` (`schedule_id` ASC, `slot_id` ASC),
  CONSTRAINT `fk_inclusion_overrides_1`
    FOREIGN KEY (`slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inclusion_overrides_2`
    FOREIGN KEY (`schedule_id`)
    REFERENCES `schedules` (`schedule_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'Slots that should be included in schedule will override excl /* comment truncated */ /*usion rules but not exclusion overrides
*/';


-- -----------------------------------------------------
-- Table `exclusion_overrides`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `exclusion_overrides` ;

CREATE TABLE IF NOT EXISTS `exclusion_overrides` (
  `exclusion_override_id` INT NOT NULL AUTO_INCREMENT,
  `slot_id` INT NOT NULL,
  `schedule_id` INT NOT NULL,
  PRIMARY KEY (`exclusion_override_id`),
  INDEX `fk_exclusion_override_1_idx` (`slot_id` ASC),
  INDEX `fk_exclusion_override_2_idx` (`schedule_id` ASC),
  UNIQUE INDEX `index4` (`slot_id` ASC, `schedule_id` ASC),
  CONSTRAINT `fk_exclusion_override_1`
    FOREIGN KEY (`slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_exclusion_override_2`
    FOREIGN KEY (`schedule_id`)
    REFERENCES `schedules` (`schedule_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
COMMENT = 'List of slots that should not be included in schedule, will  /* comment truncated */ /*override all inclusion rules and inclusion overrides*/';


-- -----------------------------------------------------
-- Table `ints`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ints` ;

CREATE TABLE IF NOT EXISTS `ints` (
  `i` TINYINT NOT NULL,
  PRIMARY KEY (`i`))
ENGINE = InnoDB
COMMENT = 'seed table for creating calender';


-- -----------------------------------------------------
-- Table `bookings`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `bookings` ;

CREATE TABLE IF NOT EXISTS `bookings` (
  `booking_id` INT NOT NULL,
  `start_slot_id` INT NOT NULL,
  `end_slot_id` INT NOT NULL,
  `valid_from` DATE NOT NULL,
  `valid_to` DATE NULL DEFAULT '3000-01-01',
  `schedule_id` INT NOT NULL,
  PRIMARY KEY (`booking_id`),
  INDEX `fk_bookings_1_idx` (`schedule_id` ASC),
  CONSTRAINT `fk_bookings_1`
    FOREIGN KEY (`schedule_id`)
    REFERENCES `schedules` (`schedule_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `bookings`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `app_activity_log` ;

CREATE TABLE IF NOT EXISTS `app_activity_log` (
  `activity_id` INT NOT NULL AUTO_INCREMENT,
  `activity_date` DATETIME NOT NULL,
  `activity_name` VARCHAR(32) NOT NULL,
  `activity_description` VARCHAR(255) NOT NULL,
  `username`  varchar(255) NOT NULL,
  `entity_id` INT NULL, 
  PRIMARY KEY (`activity_id`))
ENGINE = InnoDB;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;