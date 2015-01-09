-- -----------------------------------------------------
-- functions for package
-- -----------------------------------------------------
DELIMITER $$

-- -----------------------------------------------------
-- function utl_is_valid_date_range
-- -----------------------------------------------------
DROP function IF EXISTS `utl_is_valid_date_range`$$

CREATE FUNCTION `utl_is_valid_date_range`(validFrom DATE,validTo DATE) 
RETURNS INTEGER DETERMINISTIC BEGIN
	DECLARE isValid INT DEFAULT 0;

	-- test if closure date occurs after the opening date 
	-- and opening date does not occur in past.
	-- smallest temportal unit is 1 day we need to cast to a date
	IF CAST(validFrom AS DATE) <= CAST(validTo AS DATE) && CAST(validFrom AS DATE) >= CAST(NOW() AS DATE) THEN
		SET isValid = 1;
	END IF;

	RETURN isValid;

END;
$$

-- -----------------------------------------------------
-- function utl_fork_node
-- -----------------------------------------------------
DROP FUNCTION IF EXISTS `utl_fork_node`$$

CREATE FUNCTION `utl_fork_node`(lower INT, upper INT) RETURNS INTEGER 
BEGIN
	-- Used in the RI Tree 
	DECLARE treeHeight INT DEFAULT 0;
	DECLARE treeRoot INT DEFAULT 0; 
	DECLARE treeNode INT DEFAULT 0; 
	DECLARE searchStep INT DEFAULT 0;
	
	SET treeHeight  = ceil(LOG2((SELECT MAX(slot_id) FROM slots)+1));
	SET treeRoot    = power(2,(treeHeight-1)); 
	SET treeNode    = treeRoot;
	SET searchStep  =  treeNode / 2;

	myloop:WHILE searchStep >= 1 DO
	    
	    IF upper < treeNode THEN SET treeNode = treeNode - searchStep;
	    ELSEIF lower > treeNode THEN SET treeNode = treeNode + searchStep;
	    ELSE LEAVE myloop;
	    END IF;
	    
	    SET searchStep = searchStep / 2;
	    
  	END WHILE myloop;
	
	RETURN treeNode;
	
END;
$$