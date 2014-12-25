SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Table `proclog` (is created in procedure)
-- -----------------------------------------------------

DROP TABLE IF EXISTS `proclog`;

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
  -- uses a closed:closed interval format due to  slots only have 1 minute length

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
-- Table `timeslot_slots`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `timeslot_slots` ;

CREATE TABLE IF NOT EXISTS `timeslot_slots` (
  -- uses a closed:open interval format

  `timeslot_slot_id` INT NOT NULL AUTO_INCREMENT,
  `opening_slot_id` INT NOT NULL,
  `closing_slot_id` INT NOT NULL,
  `timeslot_id` INT NOT NULL,
  PRIMARY KEY (`timeslot_slot_id`),
  INDEX `fk_timeslot_slots_1_idx` (`timeslot_id` ASC),
  INDEX `fk_timeslot_slots_2_idx` (`opening_slot_id` ASC),
  INDEX `fk_timeslot_slots_3_idx` (`closing_slot_id` ASC),
  UNIQUE INDEX `timeslot_slots_uk1` (`opening_slot_id` ASC, `closing_slot_id` ASC, `timeslot_id` ASC),
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


-- -----------------------------------------------------
-- Table `rules` Common table for all rules
-- -----------------------------------------------------
DROP TABLE IF EXISTS `rules`;

CREATE TABLE IF NOT EXISTS `rules` (
  -- common rule fields
  `rule_id` INT NOT NULL AUTO_INCREMENT,
  `rule_name` VARCHAR(45) NOT NULL,
  `rule_type` ENUM('inclusion', 'exclusion','priority','padding','maxbook'),
  `rule_repeat` ENUM('adhoc', 'repeat','runtime'),

  -- validity date fields
  `valid_from` DATE NOT NULL,
  `valid_to`   DATE NOT NULL,
 
  -- rule durations
  `rule_duration` INT  NULL COMMENT 'event duration of repeat rule', 
 
  PRIMARY KEY (`rule_id`),
  INDEX `idx_rule_cover` (`valid_from`,`valid_to`,`rule_type`)
) ENGINE = InnoDB
COMMENT = 'Common rule storage table';

-- -----------------------------------------------------
-- Table `rules_relations`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `rules_relations`;

CREATE TABLE IF NOT EXISTS `rules_relations` (
  `rule_relation_id` INT NOT NULL AUTO_INCREMENT, 
  `rule_id` INT NOT NULL COMMENT 'Rule from common table',
  `schedule_group_id` INT COMMENT 'Known as a schedule rule',
  `membership_id` INT COMMENT 'Known as a member rule',
  
  PRIMARY KEY (`rule_relation_id`),
  CONSTRAINT `fk_rule_relation_rule`
    FOREIGN KEY (`rule_id`)
    REFERENCES `rules` (`rule_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  
  CONSTRAINT `fk_rule_relation_group`
    FOREIGN KEY (`schedule_group_id`)
    REFERENCES `schedule_groups` (`group_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  
  CONSTRAINT `fk_rule_relation_member`
    FOREIGN KEY (`membership_id`)
    REFERENCES `schedule_membership` (`membership_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
    
  UNIQUE KEY `uk_rule_relation_group`  (rule_id, schedule_group_id),
  UNIQUE KEY `uk_rule_relation_member` (rule_id, membership_id) 
) ENGINE = InnoDB
COMMENT = 'Relations table for rules';

-- -----------------------------------------------------
-- Table `audit_rules_relations`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `audit_rules_relations`;

CREATE TABLE `audit_rules_relations` (
  -- audit fields
  `change_seq` INT NOT NULL AUTO_INCREMENT COMMENT 'Table Primary key\n',
  `action` CHAR(1) DEFAULT '',
  `change_time` TIMESTAMP NOT NULL,
  `changed_by` VARCHAR(100) NOT NULL COMMENT 'Database user not application user',
  
  
  `rule_id` INT NOT NULL COMMENT 'Rule from common table',
  `schedule_group_id` INT COMMENT 'Known as a schedule rule',
  `membership_id` INT COMMENT 'Known as a member rule',
  
  PRIMARY KEY (`change_seq`),
  INDEX idx_audit_rules_rel_rule (`rule_id`)

) ENGINE = InnoDB
COMMENT= 'Audit trail for rule relationships';


-- -----------------------------------------------------
-- Table `rules_maxbook`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `rules_maxbook`;

CREATE TABLE IF NOT EXISTS `rules_maxbook` (
  `rule_id` INT NOT NULL AUTO_INCREMENT,
  `rule_name` VARCHAR(45) NOT NULL,
  `rule_type` ENUM('maxbook'),
  `rule_repeat` ENUM('adhoc', 'repeat','runtime'),

  -- validity date fields
  `valid_from` DATE NOT NULL,
  `valid_to`   DATE NOT NULL,
 
  -- custom rule fields
  `max_bookings` INT NOT NULL COMMENT 'Maximum number of allows booking per X calendar period',
  `calendar_period` ENUM('day','week','month','year') NOT NULL COMMENT 'periods to group booking into',
 
  PRIMARY KEY (`rule_id`)

)ENGINE=InnoDB 
COMMENT='Holds rule to allow a maxium number of bookings';


-- -----------------------------------------------------
-- Table `rules_maxbook`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `audit_rules_maxbook`;

CREATE TABLE IF NOT EXISTS `audit_rules_maxbook` (
   -- audit fields
  `change_seq` INT NOT NULL AUTO_INCREMENT COMMENT 'Table Primary key\n',
  `action` CHAR(1) DEFAULT '',
  `change_time` TIMESTAMP NOT NULL,
  `changed_by` VARCHAR(100) NOT NULL COMMENT 'Database user not application user',
  
  
  -- validity date fields
  `valid_from` DATE NOT NULL,
  `valid_to`   DATE NOT NULL,
  
  `rule_id` INT NOT NULL,
  `rule_name` VARCHAR(45) NOT NULL,
  `rule_type` ENUM('maxbook'),
  `rule_repeat` ENUM('adhoc', 'repeat','runtime'),

  -- custom rule fields
  `max_bookings` INT NOT NULL COMMENT 'Maximum number of allows booking per X calendar period',
  `calendar_period` ENUM('day','week','month','year') NOT NULL COMMENT 'periods to group booking into',
 

  PRIMARY KEY (`change_seq`)


)ENGINE=InnoDB 
COMMENT='Audit trail for maxbook rule';

-- -----------------------------------------------------
-- Table `rules_padding`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `rules_padding`;

CREATE TABLE IF NOT EXISTS `rules_padding` (
  `rule_id` INT NOT NULL AUTO_INCREMENT,
  `rule_name` VARCHAR(45) NOT NULL,
  `rule_type` ENUM('padding'),
  `rule_repeat` ENUM('adhoc', 'repeat','runtime'),

  -- validity date fields
  `valid_from` DATE NOT NULL,
  `valid_to`   DATE NOT NULL,
 
  -- custom fields
  `before_duration` INT NOT NULL COMMENT 'Length in minutes to pad before a booking',
  `after_duration` INT NOT NULL COMMENT 'Length in minutes to pad after a booking',


  PRIMARY KEY (`rule_id`)

)ENGINE=InnoDB 
COMMENT='Adds padding time between bookings';

-- -----------------------------------------------------
-- Table `audit_rules_padding`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `audit_rules_padding`;

CREATE TABLE IF NOT EXISTS `audit_rules_padding` (
  -- audit fields
  `change_seq` INT NOT NULL AUTO_INCREMENT COMMENT 'Table Primary key\n',
  `action` CHAR(1) DEFAULT '',
  `change_time` TIMESTAMP NOT NULL,
  `changed_by` VARCHAR(100) NOT NULL COMMENT 'Database user not application user',
 
  `rule_id` INT NOT NULL,
  `rule_name` VARCHAR(45) NOT NULL,
  `rule_type` ENUM('padding'),
  `rule_repeat` ENUM('adhoc', 'repeat','runtime'),

  -- validity date fields
  `valid_from` DATE NOT NULL,
  `valid_to`   DATE NOT NULL,
 
   -- custom fields
  `before_duration` INT NOT NULL COMMENT 'Length in minutes to pad before a booking',
  `after_duration` INT NOT NULL COMMENT 'Length in minutes to pad after a booking',

 

  PRIMARY KEY (`change_seq`)

)ENGINE=InnoDB COMMENT='Audit trail for the padding rules table';

-- -----------------------------------------------------
-- Table `rules_repeat`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `rules_repeat`;

CREATE TABLE IF NOT EXISTS `rules_repeat` (
  `rule_id` INT NOT NULL,
  `rule_name` VARCHAR(45) NOT NULL,
  `rule_type` ENUM('inclusion', 'exclusion','priority'),
  `rule_repeat` ENUM('adhoc', 'repeat','runtime'),

  -- validity date fields
  `valid_from` DATE NOT NULL,
  `valid_to`   DATE NOT NULL,
  
  -- repeat rules fields  
  `repeat_minute` VARCHAR(45) NOT NULL,
  `repeat_hour` VARCHAR(45) NOT NULL,
  `repeat_dayofweek` VARCHAR(45) NOT NULL,
  `repeat_dayofmonth` VARCHAR(45) NOT NULL,
  `repeat_month` VARCHAR(45) NOT NULL,
  `start_from`    DATE NULL COMMENT 'for repeat rules first date rule apply on',
  `end_at`        DATE NULL COMMENT 'only for repat rules last date rule apply on',

  -- rule durations
  `rule_duration` INT  NULL COMMENT 'event duration of repeat rule',
  
  PRIMARY KEY (`rule_id`),
  CONSTRAINT `fk_rule_repeat_rule`
    FOREIGN KEY (`rule_id`)
    REFERENCES `rules` (`rule_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  INDEX `idx_rule_repeat_cover` (`valid_from`,`valid_to`,`rule_type`)
) ENGINE = InnoDB
COMMENT = 'Stores an entire repeat rule';

-- -----------------------------------------------------
-- Table `rules_repeat`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `audit_rules_repeat`;

CREATE TABLE IF NOT EXISTS `audit_rules_repeat` (
   -- audit fields
  `change_seq` INT NOT NULL AUTO_INCREMENT COMMENT 'Table Primary key\n',
  `action` CHAR(1) DEFAULT '',
  `change_time` TIMESTAMP NOT NULL,
  `changed_by` VARCHAR(100) NOT NULL COMMENT 'Database user not application user',
  
  `rule_id` INT NOT NULL,
  `rule_name` VARCHAR(45) NOT NULL,
  `rule_type` ENUM('inclusion', 'exclusion','priority'),
  `rule_repeat` ENUM('adhoc', 'repeat','runtime'),

  -- validity date fields
  `valid_from` DATE NOT NULL,
  `valid_to`   DATE NOT NULL,
  
  -- repeat rules fields  
  `repeat_minute` VARCHAR(45) NOT NULL,
  `repeat_hour` VARCHAR(45) NOT NULL,
  `repeat_dayofweek` VARCHAR(45) NOT NULL,
  `repeat_dayofmonth` VARCHAR(45) NOT NULL,
  `repeat_month` VARCHAR(45) NOT NULL,
  `start_from`    DATE NULL COMMENT 'for repeat rules first date rule apply on',
  `end_at`        DATE NULL COMMENT 'only for repat rules last date rule apply on',

  -- rule durations
  `rule_duration` INT  NULL COMMENT 'event duration of repeat rule',
  
  PRIMARY KEY (`change_seq`)
)
ENGINE = InnoDB
COMMENT = 'Stores audit trail for repeat rule';

-- -----------------------------------------------------
-- Table `rules_adhoc`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `rules_adhoc`;

CREATE TABLE IF NOT EXISTS `rules_adhoc` (
  `rule_id` INT NOT NULL AUTO_INCREMENT,
  `rule_name` VARCHAR(45) NOT NULL,
  `rule_type` ENUM('inclusion', 'exclusion','priority'),
  `rule_repeat` ENUM('adhoc', 'repeat','runtime'),

   -- validity date fields
  `valid_from` DATE NOT NULL,
  `valid_to`   DATE NOT NULL,
  
  -- rule durations
  `rule_duration` INT  NULL COMMENT 'event duration of repeat rule',
  
  PRIMARY KEY (`rule_id`),
  CONSTRAINT `fk_rule_adhoc_rule`
    FOREIGN KEY (`rule_id`)
    REFERENCES `rules` (`rule_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  INDEX `idx_rule_adhoc_cover` (`valid_from`,`valid_to`,`rule_type`)
)
ENGINE = InnoDB
COMMENT = 'Stores an entire adhoc rule';
-- -----------------------------------------------------
-- Table `rules_repeat`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `audit_rules_adhoc`;

CREATE TABLE IF NOT EXISTS `audit_rules_adhoc` (
   -- audit fields
  `change_seq` INT NOT NULL AUTO_INCREMENT COMMENT 'Table Primary key\n',
  `action` CHAR(1) DEFAULT '',
  `change_time` TIMESTAMP NOT NULL,
  `changed_by` VARCHAR(100) NOT NULL COMMENT 'Database user not application user',

  `rule_id` INT NOT NULL,
  `rule_name` VARCHAR(45) NOT NULL,
  `rule_type` ENUM('inclusion', 'exclusion','priority'),
  `rule_repeat` ENUM('adhoc', 'repeat','runtime'),

   -- validity date fields
  `valid_from` DATE NOT NULL,
  `valid_to`   DATE NOT NULL,
  
  -- rule durations
  `rule_duration` INT  NULL COMMENT 'smallest interval to use in rule_slots',
  
  PRIMARY KEY (`change_seq`)
)
ENGINE = InnoDB
COMMENT = 'Stores audit trail for adhoc rule';


-- -----------------------------------------------------
-- Table `rule_slots`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `rule_slots` ;

CREATE TABLE IF NOT EXISTS `rule_slots` (
  -- uses a closed:open interval format
  
  `rule_slot_id` INT NOT NULL AUTO_INCREMENT,
  `rule_id` INT NOT NULL,
  `open_slot_id` INT NOT NULL,
  `close_slot_id` INT NOT NULL,
  
  INDEX `idx_rule_slots_slot` (`rule_id` ASC,`open_slot_id` ASC,`close_slot_id` ASC),
  -- need a constraint check in the procedure to stop periods that equal 
  -- ie sequence duplicates, but won't stop periods that start / finish / overlap, we use a constrain check in procedure
  UNIQUE KEY `uk_rule_slots` (`rule_id` ASC, `open_slot_id` ASC, `close_slot_id` ASC),
  
  PRIMARY KEY (`rule_slot_id`),
  CONSTRAINT `fk_rule_slots_openslot`
    FOREIGN KEY (`open_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_rule_slots_closeslot`
    FOREIGN KEY (`close_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_rule_slots_rule`
    FOREIGN KEY (`rule_id`)
    REFERENCES `rules` (`rule_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
    
ENGINE = InnoDB
COMMENT = 'Relates the rule to the slots they affect';

-- -----------------------------------------------------
-- Table `rule_slots_operations`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `rule_slots_operations`;

CREATE TABLE IF NOT EXISTS `rule_slots_operations` (
  `change_seq` INT NOT NULL AUTO_INCREMENT COMMENT 'Table Primary key\n',
  `operation` ENUM('addition', 'subtraction','clean') NOT NULL,
  `change_time` TIMESTAMP NOT NULL,
  `changed_by` VARCHAR(100) NOT NULL COMMENT 'Database user not application user',
  `opening_slot_id` INT COMMENT 'only known for addition and subtraction operations', 
  `closing_slot_id` INT COMMENT 'only  know for addition and subtraction operations',
  `rule_id` INT NOT NULL COMMENT ' Rule that change relates too, not fk as rule could be deleted',
  
  PRIMARY KEY (`change_seq`),

  CONSTRAINT `fk_slot_op_slots_a`
    FOREIGN KEY (`opening_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
    
  CONSTRAINT `fk_slot_op_slots_b`
    FOREIGN KEY (`closing_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION 
  
)
ENGINE = InnoDB
COMMENT = 'Log of rule slot operations';

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
-- Table `app_activity_log`
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


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;