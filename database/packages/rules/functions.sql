-- -----------------------------------------------------
-- functions for package
-- -----------------------------------------------------
DELIMITER $$

-- -----------------------------------------------------
-- functions `getRegexPatternA`
-- -----------------------------------------------------
DROP function IF EXISTS `getRegexPatternA`$$

CREATE FUNCTION `getRegexPatternA`(cronType VARCHAR(10))
RETURNS VARCHAR(255) DETERMINISTIC BEGIN
	DECLARE matchRegex VARCHAR(255);
	
	-- pattern format ##-##/##
	 
	CASE cronType
        WHEN 'minute'     THEN SET matchRegex  = '^([0-5][0-9]{1}|[0-9]{1})-([0-5][0-9]{1}|[0-9]{1})/([0-5][0-9]{1}|[0-9]{1})$';
        WHEN 'hour'       THEN SET matchRegex  = '';
        WHEN 'dayofmonth' THEN SET matchRegex  = '';
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
-- functions `getRegexPatternB`
-- -----------------------------------------------------
DROP function IF EXISTS `getRegexPatternB`$$

CREATE FUNCTION `getRegexPatternB`(cronType VARCHAR(10))
RETURNS VARCHAR(255) DETERMINISTIC BEGIN
	DECLARE matchRegex VARCHAR(255);
	
	-- pattern format ##/##
	 
	CASE cronType
        WHEN 'minute'     THEN SET matchRegex  = '^([0-5][0-9]{1}|[0-9]{1})/([0-5][0-9]{1}|[0-9]{1})$';
        WHEN 'hour'       THEN SET matchRegex  = '';
        WHEN 'dayofmonth' THEN SET matchRegex  = '';
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
-- functions `getRegexPatternC`
-- -----------------------------------------------------
DROP function IF EXISTS `getRegexPatternC`$$

CREATE FUNCTION `getRegexPatternC`(cronType VARCHAR(10))
RETURNS VARCHAR(255) DETERMINISTIC BEGIN
	DECLARE matchRegex VARCHAR(255);
	 
	-- pattern format ##-## 
	 
	CASE cronType
        WHEN 'minute'     THEN SET matchRegex  = '^([0-5][0-9]{1}|[0-9]{1})-([0-5][0-9]{1}|[0-9]{1})$';
        WHEN 'hour'       THEN SET matchRegex  = '';
        WHEN 'dayofmonth' THEN SET matchRegex  = '';
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
-- functions `getRegexPatternD`
-- -----------------------------------------------------

DROP function IF EXISTS `getRegexPatternD`$$

CREATE FUNCTION `getRegexPatternD`(cronType VARCHAR(10))
RETURNS VARCHAR(255) DETERMINISTIC BEGIN
	DECLARE matchRegex VARCHAR(255);
	
	-- pattern format ##
	 
	CASE cronType
        WHEN 'minute'     THEN SET matchRegex  = '^([0-5][0-9]?|[0-9]?)$';
        WHEN 'hour'       THEN SET matchRegex  = '';
        WHEN 'dayofmonth' THEN SET matchRegex  = '';
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
-- functions `getRegexPatternE` 
-- -----------------------------------------------------
DROP function IF EXISTS `getRegexPatternE`$$

CREATE FUNCTION `getRegexPatternE`(cronType VARCHAR(10))
RETURNS VARCHAR(255) DETERMINISTIC BEGIN
	DECLARE matchRegex VARCHAR(255);
	
	-- pattern format */##
	 
	CASE cronType
        WHEN 'minute'     THEN SET matchRegex  = '^([*]{1})/([0-5][0-9]{1}|[0-9]{1})$';
        WHEN 'hour'       THEN SET matchRegex  = '';
        WHEN 'dayofmonth' THEN SET matchRegex  = '';
        WHEN 'dayofweek'  THEN SET matchRegex  = '';
        WHEN 'month'      THEN SET matchRegex  = '';
        WHEN 'year'       THEN SET matchRegex  = '';    
        ELSE 
            SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'can not match the cron type for patternE to a regex';
    END CASE;
	 
	RETURN matchRegex;
END$$