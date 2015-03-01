

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
  INDEX `fk_slots_1_idx` (`cal_date` ASC)
)ENGINE = InnoDB
 COMMENT = 'The common slots table, each slot is the minium slot duratio /* comment truncated */ /*n of 1 minute. */';

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
  `open_slot_id` INT NULL COMMENT 'The slot bounderies for the  start of the day',
  `close_slot_id` INT NULL COMMENT 'The slot bounderies for the end of the day',
  
  PRIMARY KEY (`calendar_date`),
  
  CONSTRAINT `fk_cal_slots_1`
    FOREIGN KEY (`open_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_cal_slots_2`
    FOREIGN KEY (`close_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION 
    
)ENGINE = InnoDB
COMMENT = 'Calender table that store the next 10 years of dates';

-- -----------------------------------------------------
-- Table `calendar_weeks`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `calendar_weeks`;

CREATE TABLE IF NOT EXISTS `calendar_weeks` (
 `y` SMALLINT NULL COMMENT 'year where date occurs',
 `m` TINYINT NULL COMMENT 'month of the year',
 `w` TINYINT NULL COMMENT 'week in the year',
 `open_slot_id` INT NULL COMMENT 'The slot bounderies for the  start of the week',
 `close_slot_id` INT NULL COMMENT 'The slot bounderies for the end of the week',
 
 PRIMARY KEY(`y`,`w`),
 CONSTRAINT `fk_cal_weeks_slots_1`
    FOREIGN KEY (`open_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
 CONSTRAINT `fk_cal_weeks_slots_2`
    FOREIGN KEY (`close_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
 
)ENGINE = InnoDB
COMMENT = 'Calender table that store the next x years in week aggerates';

-- -----------------------------------------------------
-- Table `calendar_months`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `calendar_months`;

CREATE TABLE IF NOT EXISTS `calendar_months` (
 `y` SMALLINT NULL COMMENT 'year where date occurs',
 `m` TINYINT NULL COMMENT 'month of the year',
 `month_name` VARCHAR(9) NULL COMMENT 'text name of the month',
 `m_sweek` TINYINT NULL COMMENT 'week number in the year',
 `m_eweek` TINYINT NULL COMMENT 'week number in the year',
 `open_slot_id` INT NULL COMMENT 'The slot bounderies for the  start of the month',
 `close_slot_id` INT NULL COMMENT 'The slot bounderies for the end of the month',
 
 PRIMARY KEY(`y`,`m`),
 CONSTRAINT `fk_cal_months_slots_1`
    FOREIGN KEY (`open_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
 CONSTRAINT `fk_cal_months_slots_2`
    FOREIGN KEY (`close_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
 
)ENGINE = InnoDB
COMMENT = 'Calender table that store the next x years in month aggerates';

-- -----------------------------------------------------
-- Table `calendar_quarters`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `calendar_quarters`;

CREATE TABLE IF NOT EXISTS `calendar_quarters` (
 `y` SMALLINT NULL COMMENT 'year where date occurs',
 `q` TINYINT NULL COMMENT 'quarter of the year date belongs',
 `m_start` DATE NULL COMMENT 'starting month',
 `m_end` DATE NULL COMMENT 'ending_months',
 `open_slot_id` INT NULL COMMENT 'The slot bounderies for the  start of the quarter',
 `close_slot_id` INT NULL COMMENT 'The slot bounderies for the end of the quarter',
 
 PRIMARY KEY(`y`,`q`),
 CONSTRAINT `fk_cal_quarters_slots_1`
    FOREIGN KEY (`open_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
 CONSTRAINT `fk_cal_quarters_slots_2`
    FOREIGN KEY (`close_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
 
)ENGINE = InnoDB
COMMENT = 'Calender table that store the next x years in month quarter aggerates';

-- -----------------------------------------------------
-- Table `calendar_years`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `calendar_years`;

CREATE TABLE IF NOT EXISTS `calendar_years` (
 `y` SMALLINT NULL COMMENT 'year where date occurs',
 `y_start` DATETIME NOT NULL,
 `y_end` DATETIME NOT NULL,
 `open_slot_id` INT NULL COMMENT 'The slot bounderies for the  start of the year',
 `close_slot_id` INT NULL COMMENT 'The slot bounderies for the end of the year',
 
 PRIMARY KEY(`y`),
 CONSTRAINT `fk_cal_years_slots_1`
    FOREIGN KEY (`open_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
 CONSTRAINT `fk_cal_years_slots_2`
    FOREIGN KEY (`close_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)ENGINE = InnoDB
COMMENT = 'Calender table that store the next x years';


