-- -----------------------------------------------------
-- procedure utl_create_rule_tmp_tables
-- -----------------------------------------------------
DELIMITER $$
DROP procedure IF EXISTS `utl_create_rule_tmp_tables`$$

CREATE procedure `utl_create_rule_tmp_tables` ()
BEGIN
	
	DROP TEMPORARY TABLE IF EXISTS bm_parsed_ranges;
	CREATE TEMPORARY TABLE bm_parsed_ranges (
		id INT PRIMARY KEY AUTO_INCREMENT,
		range_open INT NOT NULL,
		range_closed INT NOT NULL,
		mod_value INT NULL,
		value_type ENUM('minute','hour','day','month','year')
	) ENGINE=MEMORY;
	
	DROP TEMPORARY TABLE IF EXISTS bm_range_values;
	CREATE TEMPORARY TABLE bm_range_values(
		id INT PRIMARY KEY AUTO_INCREMENT,
		include_minute INT NULL,
		include_hour INT NULL,
		include_day	INT NULL,
		include_month INT NULL,
		include_year INT NULL,
		CONSTRAINT bm_range_uk_1 UNIQUE INDEX (include_minute,include_hour
												,include_day,include_month
												,include_year)
	)ENGINE=MEMORY;
END$$

