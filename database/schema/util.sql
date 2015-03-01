-- -----------------------------------------------------
-- Table `proclog` (is created in procedure)
-- -----------------------------------------------------

DROP TABLE IF EXISTS `proclog`;

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
