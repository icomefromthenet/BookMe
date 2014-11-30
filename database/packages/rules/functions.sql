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
        WHEN 'dayofmonth' THEN SET matchRegex  = '^([1-9]{1}|[1-2][0-9]{1}|[3][0-1]{1})-([1-9]{1}|[1-2][0-9]{1}|[3][0-1]{1})/([0-9]+)$';
        WHEN 'dayofweek'  THEN SET matchRegex  = '';
        WHEN 'month'      THEN SET matchRegex  = '';
        WHEN 'year'       THEN SET matchRegex  = '';    
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
        WHEN 'dayofweek'  THEN SET matchRegex  = '';
        WHEN 'month'      THEN SET matchRegex  = '';
        WHEN 'year'       THEN SET matchRegex  = '';    
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
        WHEN 'dayofweek'  THEN SET matchRegex  = '';
        WHEN 'month'      THEN SET matchRegex  = '';
        WHEN 'year'       THEN SET matchRegex  = '';    
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
        WHEN 'dayofweek'  THEN SET matchRegex  = '';
        WHEN 'month'      THEN SET matchRegex  = '';
        WHEN 'year'       THEN SET matchRegex  = '';    
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
        WHEN 'dayofweek'  THEN SET matchRegex  = '';
        WHEN 'month'      THEN SET matchRegex  = '';
        WHEN 'year'       THEN SET matchRegex  = '';    
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
        WHEN 'minute'     THEN SET myVal  = 1;
        WHEN 'hour'       THEN SET myVal  = 0;
        WHEN 'dayofmonth' THEN SET myVal  = 1;
        WHEN 'dayofweek'  THEN SET myVal  = '';
        WHEN 'month'      THEN SET myVal  = '';
        WHEN 'year'       THEN SET myVal  = '';    
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
        WHEN 'dayofweek'  THEN SET myVal  = '';
        WHEN 'month'      THEN SET myVal  = '';
        WHEN 'year'       THEN SET myVal  = '';    
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