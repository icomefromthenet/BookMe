

-- -----------------------------------------------------
-- Table `bookings`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `bookings`;

CREATE TABLE IF NOT EXISTS `bookings` (
  `booking_id` INT NOT NULL,
  `schedule_id` INT NOT NULL,
  
  `open_slot_id` INT NOT NULL,
  `close_slot_id` INT NOT NULL,
  
  -- helpful de-normalisation to avoid a join back on slots table to fetch cal date  
  `starting_date` DATETIME NOT NULL,
  `closing_date` DATETIME NOt NULL,
  
  -- RItree forkNode
  `node` INT NOT NULL,
  
  PRIMARY KEY (`booking_id`),
  
  -- stop obvious overlaps, two appointments for same schedule
  -- cant end or begin at same time or they would forfill the
  -- Starts,Finishes and Equals allens relations.
  -- this won't stop other allens relations part of intersects group, that be
  -- up to the procedure
  UNIQUE KEY `booking_uk1` (`schedule_id`,`close_slot_id`),
  UNIQUE KEY `booking_uk2` (`schedule_id`,`open_slot_id`),
  
  -- RI Tree Indexes.
  INDEX `booking_upper_idx` (`schedule_id`,`close_slot_id`),
  INDEX `booking_lower_idx` (`schedule_id`,`open_slot_id`),
  
  CONSTRAINT `fk_bookings_1`
    FOREIGN KEY (`schedule_id`)
    REFERENCES `schedules` (`schedule_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  
  CONSTRAINT `fk_bookings_open_slot`
    FOREIGN KEY (`open_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  
  CONSTRAINT `fk_bookings_close_slot`
    FOREIGN KEY (`close_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
    
) ENGINE = InnoDB
COMMENT = 'Table to record bookings, implements RI Tree to speed up interval queries';


-- -----------------------------------------------------
-- Table `bookings_audit_trail`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `bookings_audit_trail` ;

CREATE TABLE IF NOT EXISTS `bookings_audit_trail` (
   -- audit fields
  `change_seq` INT NOT NULL AUTO_INCREMENT COMMENT 'Table Primary key\n',
  `action` CHAR(1) DEFAULT '',
  `change_time` TIMESTAMP NOT NULL,
  `changed_by` VARCHAR(100) NOT NULL COMMENT 'Database user not application user',
  
  -- booking fields
  `booking_id` INT NOT NULL,
  `schedule_id` INT NOT NULL,
  
  `open_slot_id` INT NOT NULL,
  `close_slot_id` INT NOT NULL,
  
  -- helpful de-normalisation to avoid a join back on slots table to fetch cal date  
  `starting_date` DATE NOT NULL,
  `closing_date` DATE NOt NULL,
  
  PRIMARY KEY (`change_seq`),
  
  -- we cant have unqiue keys as removed bookings recorded in this table
  -- and could violate a unique key. have some index to speed up basic queries
  INDEX `bookings_trail_idx1` (`schedule_id` ASC,`close_slot_id` ASC),
  INDEX `bookings_trail_idx2` (`schedule_id` ASC,`open_slot_id` ASC)

) ENGINE = InnoDB
COMMENT = 'Auidt trail for bookings';

-- -----------------------------------------------------
-- Table `booking_conflict_notice`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `booking_conflict_notice`;

CREATE TABLE IF NOT EXISTS `booking_conflict_notice` (
  `conflict_seq` INT NOT NULL AUTO_INCREMENT,
  `booking_id` INT NOT NULL,
  `conflict_date` DATETIME NOT NULL,
  `conflict_reason` VARCHAR(255),
  
  PRIMARY KEY (`conflict_seq`),
  
  CONSTRAINT `fk_bookings_conflict`
    FOREIGN KEY (`booking_id`)
    REFERENCES `bookings` (`booking_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION

) ENGINE = InnoDB
COMMENT = 'Bookings that found to be in conflict due to rule changes';


-- -----------------------------------------------------
-- Table `bookings_agg_mv`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `bookings_agg_mv` ;

CREATE TABLE IF NOT EXISTS `bookings_agg_mv` (
  `schedule_id` INT NOT NULL,
  
  `cal_week` INT NOT NULL,
  `cal_month` INT NOT NULL,
  `cal_year`  INT NOT NULL,
  `cal_sun` INT DEFAULT 0, 
  `cal_mon` INT DEFAULT 0, 
  `cal_tue` INT DEFAULT 0, 
  `cal_wed` INT DEFAULT 0,
  `cal_thu` INT DEFAULT 0,
  `cal_fri` INT DEFAULT 0,
  `cal_sat` INT DEFAULT 0,
  
  `open_slot_id` INT NOT NULL,
  `close_slot_id` INT NOT NULL,
  
  PRIMARY KEY (schedule_id,cal_week,cal_year),
  CONSTRAINT `fk_bookings_agg_open_slot`
    FOREIGN KEY (`open_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  
  CONSTRAINT `fk_bookings_agg_close_slot`
    FOREIGN KEY (`close_slot_id`)
    REFERENCES `slots` (`slot_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE = InnoDB 
COMMENT= 'Materialised view for booking count agg divided into calender periods';




-- -----------------------------------------------------
-- Table `bookings_monthly_agg_vw`
-- -----------------------------------------------------

DROP VIEW IF EXISTS `bookings_monthly_agg_vw`;
CREATE VIEW `bookings_monthly_agg_vw` AS
SELECT `cal`.`y` as y,`cal`.`m` as m, `b`.`schedule_id`
    ,sum(ifnull(`b`.`cal_sun`,0)) AS cal_sun ,sum(ifnull(`b`.`cal_mon`,0)) AS cal_mon ,sum(ifnull(`b`.`cal_tue`,0)) AS cal_tue
	  ,sum(ifnull(`b`.`cal_wed`,0)) AS cal_wed ,sum(ifnull(`b`.`cal_thu`,0)) AS cal_thu ,sum(ifnull(`b`.`cal_fri`,0)) AS cal_fri
	  ,sum(ifnull(`b`.`cal_sat`,0)) AS cal_sat
	  ,min(`cal`.`open_slot_id`) AS open_slot_id
	  ,max(`cal`.`close_slot_id`) AS close_slot_id	
FROM calendar_months cal
LEFT JOIN `bookings_agg_mv` b ON `b`.`cal_year` = `cal`.`y` 
AND `b`.`open_slot_id` >= `cal`.`open_slot_id`
AND `b`.`close_slot_id` <= `cal`.`close_slot_id`
GROUP BY `cal`.`y`, `cal`.`m`, `b`.`schedule_id`;


-- -----------------------------------------------------
-- Table `bookings_yearly_agg_vw`
-- -----------------------------------------------------

DROP VIEW IF EXISTS `bookings_yearly_agg_vw`;

CREATE VIEW `bookings_yearly_agg_vw` AS
SELECT `cal`.`y` as y, `b`.`schedule_id`
    ,sum(ifnull(`b`.`cal_sun`,0)) AS cal_sun ,sum(ifnull(`b`.`cal_mon`,0)) AS cal_mon ,sum(ifnull(`b`.`cal_tue`,0)) AS cal_tue
	  ,sum(ifnull(`b`.`cal_wed`,0)) AS cal_wed ,sum(ifnull(`b`.`cal_thu`,0)) AS cal_thu ,sum(ifnull(`b`.`cal_fri`,0)) AS cal_fri
	  ,sum(ifnull(`b`.`cal_sat`,0)) AS cal_sat
	  ,min(`cal`.`open_slot_id`) AS open_slot_id
	  ,max(`cal`.`close_slot_id`) AS close_slot_id	
FROM calendar_years cal
LEFT JOIN `bookings_agg_mv` b ON `b`.`cal_year` = `cal`.`y` 
GROUP BY `cal`.`y`, `b`.`schedule_id`;