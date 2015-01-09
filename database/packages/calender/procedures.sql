-- -----------------------------------------------------
-- procedures for package
-- -----------------------------------------------------
DELIMITER $$

-- -----------------------------------------------------
-- procedure bm_calendar_addtimeslot
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `bm_calendar_add_timeslot`$$

CREATE PROCEDURE `bm_calendar_add_timeslot` (IN slotLength INT,OUT timeslotID INT)
BEGIN
	
	IF slotLength <= 1 AND slotLength > (60*24) THEN 
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Slot must be between 1 minutes and 1440 (day) in length';
	END IF;
	
	IF MOD((60*24),slotLength) > 0 THEN 
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Slot length must be divide day evenly';
	END IF;
	
	-- unique index on length column stop duplicates
    -- trigger should fire that record this addition onto audit table
	INSERT INTO timeslots (timeslot_id,timeslot_length) values (NULL,slotLength);

    -- calculate this timeslots , slot groups. 
	SET timeslotID = LAST_INSERT_ID();
	
	IF @bm_debug = true THEN
		CALL util_proc_log(concat('Add Timeslot at:: ',timeslotID));
	END IF;	

	CALL bm_calendar_build_timeslot_slots(timeslotID,slotLength);

END$$

-- -----------------------------------------------------
-- procedure bm_calendar_remove_timeslot
-- -----------------------------------------------------
DROP PROCEDURE IF EXISTS `bm_calendar_remove_timeslot`$$

CREATE PROCEDURE `bm_calendar_remove_timeslot` (IN slotID INT)
BEGIN
	
	-- remove the slot form the relation
	DELETE FROM timeslot_slots WHERE timeslot_id = slotID;
	
	-- if slots not removed above the fk relation will
	-- case this delete to error
	DELETE FROM timeslots WHERE timeslot_id = slotID;
    
    
   	IF ROW_COUNT() = 0 THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Unable to remove the timeslot could be unknown slot ID was given';
	END IF;
	
	IF @bm_debug = true THEN
		CALL util_proc_log(concat('Timeslot at:: ',timeslotID,' was removed'));
	END IF;	

END$$

-- -----------------------------------------------------
-- procedure bm_calendar_build_timeslot_slots
-- -----------------------------------------------------

DROP procedure IF EXISTS `bm_calendar_build_timeslot_slots`$$

CREATE PROCEDURE `bm_calendar_build_timeslot_slots` ( IN timeslotID INT
													 ,IN timeslotLength INT )
BEGIN
		
	-- Need to group our slots and insert results into group cache table
    -- As out slot tabe has sequential id we can use this to build buckets
    
    -- Using a closed:open with this schema a closing interval is always
    -- last instance +1 which opening of the next interval. Two intervals with 5 mintues each [1:6)[6:11)
    --
    -- For reference for an interval range 1-5
    -- open:open     (0:6)
    -- closed:closed [1:5]
    -- open:closed   (0:5]
    -- close:open    [1:6)
    --
    
    -- This table uses RI Tree so needs to call utl_fork_node to calculate the node column. 
    -- This in effect creats a virtual tree structure.
    
    INSERT INTO timeslot_slots (timeslot_slot_id,opening_slot_id,closing_slot_id,timeslot_id,node)  
	SELECT NULL
          ,min(`a`.`slot_id`) as slot_open_id	
          ,max(`a`.`slot_id`) +1 as slot_close_id 
          ,timeslotID
          ,utl_fork_node(min(`a`.`slot_id`),max(`a`.`slot_id`))
    FROM `slots` a
    -- as where using closed:open we can not use the last row in slot table
    -- this would cause a FK key constrain we stop at the last slot in the table. 
    WHERE `a`.`slot_id` < (select max(`b`.`slot_id`) FROM `slots` b)
	GROUP BY ceil(`a`.`slot_id`/timeslotLength);
		
	IF @bm_debug = true THEN
		CALL util_proc_log(concat('For timeslot at:: ',timeslotID,' Inserted ',ROW_COUNT(),' into timeslot_slots'));
	END IF;	
		
END$$


-- -----------------------------------------------------
-- procedure bm_calender_setup_slots
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_calender_setup_slots`$$

CREATE PROCEDURE `bm_calender_setup_slots`()
BEGIN
	
	-- AS the slot length is 1 minute and a minute is the smallets granual we are
	-- using a closed:open period two intervals, so add 1 to the closing slot
		
	INSERT INTO slots (slot_id,cal_date,slot_open,slot_close)
		SELECT NULL
              ,calendar_date 
			  ,calendar_date + INTERVAL d.i *1000 + c.i *100 + b.i*10 + a.i MINUTE as slot_open
			  ,calendar_date + INTERVAL d.i *1000 + c.i *100 + b.i*10 + a.i + 1 MINUTE as slot_closed 
		FROM calendar
		JOIN ints a JOIN ints b JOIN ints c JOIN ints d
		WHERE d.i*1000 + c.i *100 + b.i*10 + a.i < 1440;

    -- add the extra slot at the end to allow for even years when using closed:open interval format
	INSERT INTO slots (slot_id,cal_date,slot_open,slot_close) 
		SELECT s.slot_id +1
			  ,(s.cal_date + INTERVAL 1 DAY)
			  ,s.slot_close
			  ,(s.slot_close + INTERVAL 1 MINUTE) 
		FROM slots s
		ORDER BY s.slot_id DESC
		LIMIT 1;
		

	-- add slots to the calender table (day boundaries)
	UPDATE calendar cd 
	set cd.open_slot_id = (
		select min(s.slot_id)
		from slots s
		where cd.calendar_date = s.cal_date
		group by extract(year from s.cal_date), extract(month from s.cal_date), extract(day from s.cal_date)
	)
	, cd.close_slot_id = (
		select max(s.slot_id)
		from slots s
		where cd.calendar_date = s.cal_date
		group by extract(year from s.cal_date), extract(month from s.cal_date), extract(day from s.cal_date)
	) +1;
	
	-- add slots to weeks table (weeks boundaries)
	
	update calendar_weeks cd 
	set cd.open_slot_id = (
		select min(s.open_slot_id)
		from calendar s
		where cd.y = s.y
		and   cd.m =  s.w
		group by s.y,s.w
	)
	, cd.close_slot_id = (
		select max(s.close_slot_id)
		from calendar s
		where cd.y = s.y
		and   cd.m =  s.w
		group by s.y,s.w
	);
	
	-- add slots to months table (mounth boundaries)
	
	update calendar_months cd 
	set cd.open_slot_id = (
		select min(s.open_slot_id)
		from calendar s
		where cd.y = s.y
		and   cd.m =  s.m
		group by s.y,s.m
	)
	, cd.close_slot_id = (
		select max(s.close_slot_id)
		from calendar s
		where cd.y = s.y
		and   cd.m =  s.m
		group by s.y,s.m
	);
	
	
	
	
	-- add slots to quarter table (mounth boundaries)
	update calendar_quarters cd 
	set cd.open_slot_id = (
		select min(s.open_slot_id)
		from calendar s
		where cd.y = s.y
		and   cd.q =  s.q
		group by s.y,s.q
	)
	, cd.close_slot_id = (
		select max(s.close_slot_id)
		from calendar s
		where cd.y = s.y
		and   cd.q =  s.q
		group by s.y,s.q
	);
	
	
	-- add slots to years table (mounth boundaries)
	update calendar_years cd 
	set cd.open_slot_id = (
		select min(s.open_slot_id)
		from calendar s
		where cd.y = s.y
		group by s.y
	)
	, cd.close_slot_id = (
		select max(s.close_slot_id)
		from calendar s
		where cd.y = s.y
		group by s.y
	);



	IF @bm_debug = true THEN
		CALL util_proc_log('Setup slots for our calender');
	END IF;	

	
END$$

-- -----------------------------------------------------
-- procedure bm_calendar_setup_cal
-- -----------------------------------------------------
DROP procedure IF EXISTS `bm_calendar_setup_cal`$$

CREATE PROCEDURE `bm_calendar_setup_cal` (IN x INT)
BEGIN
	DECLARE maxPeriod INT DEFAULT 10;

	-- validate the length is in valid range
	IF x < 1 OR x > maxPeriod THEN
		SIGNAL SQLSTATE '45000'
		SET MESSAGE_TEXT = 'Minimum calendar year is 1 and maxium is 10';
	END IF;

	INSERT INTO calendar (calendar_date)
		SELECT DATE_FORMAT(NOW() ,'%Y-01-01') + INTERVAL a.i*10000 + b.i*1000 + c.i*100 + d.i*10 + e.i DAY
		FROM ints a JOIN ints b JOIN ints c JOIN ints d JOIN ints e
		WHERE (a.i*10000 + b.i*1000 + c.i*100 + d.i*10 + e.i) <= DATEDIFF(DATE_FORMAT(NOW()+ INTERVAL (x -1) YEAR,'%Y-12-31'),DATE_FORMAT(NOW() ,'%Y-01-01'))
		ORDER BY 1;
	
	
	UPDATE calendar
	SET is_week_day = CASE WHEN dayofweek(calendar_date) IN (1,7) THEN 0 ELSE 1 END,
		y = YEAR(calendar_date),
		q = quarter(calendar_date),
		m = MONTH(calendar_date),
		d = dayofmonth(calendar_date),
		dw = dayofweek(calendar_date),
		month_name = monthname(calendar_date),
		day_name = dayname(calendar_date),
		w = week(calendar_date);
	
	IF @bm_debug = true THEN
		CALL util_proc_log(concat('Build calendar for ',x,' years'));
	END IF;
	
	-- weeks table
	INSERT INTO `calendar_weeks` (`y`,`m`,`w`)
	SELECT `c`.`y`, `c`.`m`, `c`.`w`
	FROM `calendar` c
	GROUP BY `c`.`y`,`c`.`w`;
	
	-- create months table
	
	INSERT INTO `calendar_months` (`y`,`m`,`month_name`,`m_sweek`,`m_eweek`)
	SELECT `c`.`y`, `c`.`m`, max(`c`.`month_name`) as month_name
	       ,min(`c`.`w`) AS a, max(`c`.`w`) AS b 
	FROM `calendar` c
	GROUP BY `c`.`y`,`c`.`m`;
	
	-- create quaters table
	
	INSERT INTO `calendar_quarters` (`y`,`q`,`m_start`,`m_end`)
	SELECT `c`.`y`,`c`.`q`
			,min(`c`.`calendar_date`)
			,max(`c`.`calendar_date`)
	FROM `calendar` c
	GROUP BY `c`.`y`,`c`.`q`;

	-- create years table
	INSERT INTO `calendar_years` (`y`,`y_start`,`y_end`)
	SELECT `c`.`y`,min(`c`.`calendar_date`),max(`c`.`calendar_date`)
	FROM `calendar` c
	GROUP BY `c`.`y`;
	
END