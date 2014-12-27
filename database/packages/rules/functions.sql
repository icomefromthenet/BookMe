-- -----------------------------------------------------
-- functions for package
-- -----------------------------------------------------
DELIMITER $$

-- -----------------------------------------------------
-- functions `bm_rules_regex_pattern_a`
-- -----------------------------------------------------
DROP function IF EXISTS `bm_rules_regex_pattern_a`$$

CREATE FUNCTION `bm_rules_regex_pattern_a`(cronType VARCHAR(10))
RETURNS VARCHAR(255) DETERMINISTIC BEGIN
	DECLARE matchRegex VARCHAR(255);
	
	-- pattern format ##-##/##
	 
	CASE cronType
        WHEN 'minute'     THEN SET matchRegex  = '^([0-5][0-9]{1}|[0-9]{1})-([0-5][0-9]{1}|[0-9]{1})/([0-9]+)$';
        WHEN 'hour'       THEN SET matchRegex  = '^([1-9]{1}|[1-2][0-9]{1}|[3][0-1]{1})-([1-9]{1}|[1-2][0-9]{1}|[3][0-1]{1})/([0-9]+)$';
        WHEN 'dayofmonth' THEN SET matchRegex  = '^([0-6]{1})-([0-6]{1})/([0-9]+)$';
        WHEN 'dayofweek'  THEN SET matchRegex  = '^([0-6]{1})-([0-6]{1})/([0-9]+)$';
        WHEN 'month'      THEN SET matchRegex  = '^([1-9]{1}|[1-2][1-2]{1})-([1-9]{1}|[1-2][1-2]{1})/([0-9]+)$';
        ELSE 
            SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'can not match the cron type for patternA to a regex';
    END CASE;
	 
	RETURN matchRegex;
END$$

-- -----------------------------------------------------
-- functions `bm_rules_regex_pattern_b`
-- -----------------------------------------------------
DROP function IF EXISTS `bm_rules_regex_pattern_b`$$

CREATE FUNCTION `bm_rules_regex_pattern_b`(cronType VARCHAR(10))
RETURNS VARCHAR(255) DETERMINISTIC BEGIN
	DECLARE matchRegex VARCHAR(255);
	
	-- pattern format ##/##
	 
	CASE cronType
        WHEN 'minute'     THEN SET matchRegex  = '^([0-5][0-9]{1}|[0-9]{1})/([0-9]+)$';
        WHEN 'hour'       THEN SET matchRegex  = '^([0-1][0-9]|[2][0-3]{1}|[0-9]{1})/([0-9]+)$';
        WHEN 'dayofmonth' THEN SET matchRegex  = '^([1-9]{1}|[1-2][0-9]{1}|[3][0-1]{1})/([0-9]+)$';
        WHEN 'dayofweek'  THEN SET matchRegex  = '^([0-6]{1})/([0-9]+)$';
        WHEN 'month'      THEN SET matchRegex  = '^([1-9]{1}|[1-2][1-2]{1})/([0-9]+)$';
        ELSE 
            SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'can not match the cron type for patternB to a regex';
    END CASE;
	 
	RETURN matchRegex;
END$$

-- -----------------------------------------------------
-- functions `bm_rules_regex_pattern_c`
-- -----------------------------------------------------
DROP function IF EXISTS `bm_rules_regex_pattern_c`$$

CREATE FUNCTION `bm_rules_regex_pattern_c`(cronType VARCHAR(10))
RETURNS VARCHAR(255) DETERMINISTIC BEGIN
	DECLARE matchRegex VARCHAR(255);
	 
	-- pattern format ##-## 
	 
	CASE cronType
        WHEN 'minute'     THEN SET matchRegex  = '^([0-5][0-9]{1}|[0-9]{1})-([0-5][0-9]{1}|[0-9]{1})$';
        WHEN 'hour'       THEN SET matchRegex  = '^([0-1][0-9]|[2][0-3]{1}|[0-9]{1})-([0-1][0-9]|[2][0-3]{1}|[0-9]{1})$';
        WHEN 'dayofmonth' THEN SET matchRegex  = '^([1-9]{1}|[1-2][0-9]{1}|[3][0-1]{1})-([1-9]{1}|[1-2][0-9]{1}|[3][0-1]{1})$';
        WHEN 'dayofweek'  THEN SET matchRegex  = '^([0-6]{1})-([0-6]{1})$';
        WHEN 'month'      THEN SET matchRegex  = '^([1-9]{1}|[1-2][1-2]{1})-([1-9]{1}|[1-2][1-2]{1})';
        ELSE 
            SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'can not match the cron type for patternC to a regex';
    END CASE;
	 
	RETURN matchRegex;
END$$

-- -----------------------------------------------------
-- functions `bm_rules_regex_pattern_d`
-- -----------------------------------------------------

DROP function IF EXISTS `bm_rules_regex_pattern_d`$$

CREATE FUNCTION `bm_rules_regex_pattern_d`(cronType VARCHAR(10))
RETURNS VARCHAR(255) DETERMINISTIC BEGIN
	DECLARE matchRegex VARCHAR(255);
	
	-- pattern format ##
	 
	CASE cronType
        WHEN 'minute'     THEN SET matchRegex  = '^([0-5][0-9]?|[0-9]{1})$';
        WHEN 'hour'       THEN SET matchRegex  = '^([0-1][0-9]|[2][0-3]{1}|[0-9]{1})$';
        WHEN 'dayofmonth' THEN SET matchRegex  = '^([1-9]{1}|[1-2][0-9]{1}|[3][0-1]{1})$';
        WHEN 'dayofweek'  THEN SET matchRegex  = '^([0-6]{1})$';
        WHEN 'month'      THEN SET matchRegex  = '^([1-9]{1}|[1-2][1-2]{1})$';
        ELSE 
            SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'can not match the cron type for patternD to a regex';
    END CASE;
	 
	RETURN matchRegex;
END$$

-- -----------------------------------------------------
-- functions `bm_rules_regex_pattern_e` 
-- -----------------------------------------------------
DROP function IF EXISTS `bm_rules_regex_pattern_e`$$

CREATE FUNCTION `bm_rules_regex_pattern_e`(cronType VARCHAR(10))
RETURNS VARCHAR(255) DETERMINISTIC BEGIN
	DECLARE matchRegex VARCHAR(255);
	
	-- pattern format */##
	 
	CASE cronType
        WHEN 'minute'     THEN SET matchRegex  = '^([*]{1})/([0-9]+)$';
        WHEN 'hour'       THEN SET matchRegex  = '^([*]{1})/([0-9]+)$';
        WHEN 'dayofmonth' THEN SET matchRegex  = '^([*]{1})/([0-9]+)$';
        WHEN 'dayofweek'  THEN SET matchRegex  = '^([*]{1})/([0-9]+)$';
        WHEN 'month'      THEN SET matchRegex  = '^([*]{1})/([0-9]+)$';
        ELSE 
            SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'can not match the cron type for patternE to a regex';
    END CASE;
	 
	RETURN matchRegex;
END$$

-- -----------------------------------------------------
-- functions `bm_rules_min` 
-- -----------------------------------------------------
DROP function IF EXISTS `bm_rules_min`$$

CREATE FUNCTION `bm_rules_min` (cronType VARCHAR(10))
RETURNS INTEGER DETERMINISTIC BEGIN
    DECLARE myVal VARCHAR(255);

	CASE cronType
        WHEN 'minute'     THEN SET myVal  = 0;
        WHEN 'hour'       THEN SET myVal  = 0;
        WHEN 'dayofmonth' THEN SET myVal  = 1;
        WHEN 'dayofweek'  THEN SET myVal  = 0;
        WHEN 'month'      THEN SET myVal  = 1;
        ELSE 
            SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'can not match the cron type for min datetime';
    END CASE;
	 
	RETURN myVal;
END$$

-- -----------------------------------------------------
-- functions `bm_rules_max` 
-- -----------------------------------------------------
DROP function IF EXISTS `bm_rules_max`$$

CREATE FUNCTION `bm_rules_max` (crontype VARCHAR(10))
RETURNS INTEGER DETERMINISTIC BEGIN
    DECLARE myVal VARCHAR(255);

	CASE cronType
        WHEN 'minute'     THEN SET myVal  = 59;
        WHEN 'hour'       THEN SET myVal  = 23;
        WHEN 'dayofmonth' THEN SET myVal  = 31;
        WHEN 'dayofweek'  THEN SET myVal  = 6;
        WHEN 'month'      THEN SET myVal  = 12;
        ELSE 
            SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'can not match the cron type for max datetime';
    END CASE;
	 
	RETURN myVal;
END$$


-- -----------------------------------------------------
-- functions `bm_rules_valid_rule_type` 
-- -----------------------------------------------------
DROP function IF EXISTS `bm_rules_valid_rule_type`$$

CREATE FUNCTION `bm_rules_valid_rule_type` (ruleType VARCHAR(10))
RETURNS BOOLEAN DETERMINISTIC BEGIN
    DECLARE isValid BOOLEAN DEFAULT false;
    
    IF ruleType = 'inclusion' OR ruleType = 'exclusion' THEN
        SET isValid = true;
    END IF;
    
    RETURN isValid;
END$$


-- -----------------------------------------------------
-- functions `bm_rules_valid_duration` 
-- -----------------------------------------------------
DROP function IF EXISTS `bm_rules_valid_duration`$$

CREATE FUNCTION `bm_rules_valid_duration` (duration INT)
RETURNS BOOLEAN DETERMINISTIC BEGIN
    DECLARE isValid BOOLEAN DEFAULT false;
    
    -- Min Duration is 1 minute (0-59) and Maxium value is 1 leap year
    -- starting at 0 to keep conistent with cron minute format which 0-59

    IF (duration >= 0) && (duration < (60*24*7*366)) THEN
        SET isValid = true;
    END IF;
    
    RETURN isValid;
END$$

-- -----------------------------------------------------
-- functions `bm_rules_is_exclusion` 
-- -----------------------------------------------------
DROP function IF EXISTS `bm_rules_is_exclusion`$$

CREATE FUNCTION `bm_rules_is_exclusion` (ruleType varchar(255))
RETURNS BOOLEAN DETERMINISTIC BEGIN
    DECLARE isRule BOOLEAN DEFAULT false;
    
    IF ruleType = 'exclusion' THEN
        SET isRule = true;
    END IF;
    
    RETURN isRule;
END$$

-- -----------------------------------------------------
-- functions `bm_rules_is_inclusion` 
-- -----------------------------------------------------
DROP function IF EXISTS `bm_rules_is_inclusion`$$

CREATE FUNCTION `bm_rules_is_inclusion` (ruleType varchar(255))
RETURNS BOOLEAN DETERMINISTIC BEGIN
    DECLARE isRule BOOLEAN DEFAULT false;
    
    IF ruleType = 'inclusion' THEN
        SET isRule = true;
    END IF;
    
    RETURN isRule;
END$$

-- -----------------------------------------------------
-- functions `bm_rules_is_priority` 
-- -----------------------------------------------------
DROP function IF EXISTS `bm_rules_is_priority`$$

CREATE FUNCTION `bm_rules_is_priority` (ruleType varchar(255))
RETURNS BOOLEAN DETERMINISTIC BEGIN
    DECLARE isRule BOOLEAN DEFAULT false;
    
    IF ruleType ='priority' THEN
        SET isRule = true;
    END IF;
    
    RETURN isRule;
END$$

-- -----------------------------------------------------
-- functions `bm_rules_is_maxbook` 
-- -----------------------------------------------------
DROP function IF EXISTS `bm_rules_is_maxbook`$$

CREATE FUNCTION `bm_rules_is_maxbook` (ruleType varchar(255))
RETURNS BOOLEAN DETERMINISTIC BEGIN
    DECLARE isRule BOOLEAN DEFAULT false;
    
    IF ruleType ='maxbook' THEN
        SET isRule = true;
    END IF;
    
    RETURN isRule;
END$$


-- -----------------------------------------------------
-- functions `bm_rules_is_padding` 
-- -----------------------------------------------------
DROP function IF EXISTS `bm_rules_is_padding`$$

CREATE FUNCTION `bm_rules_is_padding` (ruleType varchar(255))
RETURNS BOOLEAN DETERMINISTIC BEGIN
    DECLARE isRule BOOLEAN DEFAULT false;
    
    IF ruleType ='padding' THEN
        SET isRule = true;
    END IF;
    
    RETURN isRule;
END$$

-- -----------------------------------------------------
-- functions `bm_rules_is_member` 
-- -----------------------------------------------------
DROP function IF EXISTS `bm_rules_is_member`$$

CREATE FUNCTION `bm_rules_is_member` (memberID INT,scheduleGroupID INT)
RETURNS BOOLEAN DETERMINISTIC BEGIN
    DECLARE isRule BOOLEAN DEFAULT false;
    
    IF memberID > 0 AND scheduleGroupID is NULL THEN
        SET isRule = true;
    END IF;
    
    RETURN isRule;
END$$

-- -----------------------------------------------------
-- functions `bm_rules_is_schedule_group` 
-- -----------------------------------------------------
DROP function IF EXISTS `bm_rules_is_schedule_group`$$

CREATE FUNCTION `bm_rules_is_schedule_group` (memberID INT,scheduleGroupID INT)
RETURNS BOOLEAN DETERMINISTIC BEGIN
    DECLARE isRule BOOLEAN DEFAULT false;
    
    IF memberID is NULL AND scheduleGroupID > 0 THEN
        SET isRule = true;
    END IF;
    
    RETURN isRule;
END$$

-- -----------------------------------------------------
-- functions `bm_rules_is_valid_calendar_type` 
-- -----------------------------------------------------
DROP function IF EXISTS `bm_rules_is_valid_calendar_type`$$

CREATE FUNCTION `bm_rules_is_valid_calendar_type` (calValue VARCHAR(45))
RETURNS BOOLEAN DETERMINISTIC BEGIN
    DECLARE isValid BOOLEAN DEFAULT false;
    
    IF calValue IN ('day','week','month','year') THEN
        SET isValid = true;
    END IF;
    
    RETURN isValid;
END$$



