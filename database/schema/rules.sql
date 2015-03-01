
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
  `after_slots` INT NOT NULL COMMENT 'Number of slots to pad after a booking',


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
   `after_slots` INT NOT NULL COMMENT 'Number of slots to pad after a booking',

 

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