-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


-- --------------------------------------------------------

--
-- Table structure for table `bm_booking`
--

DROP TABLE IF EXISTS `bm_booking`;
CREATE TABLE IF NOT EXISTS `bm_booking` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule_id` int(11) NOT NULL,
  `slot_open` datetime NOT NULL,
  `slot_close` datetime NOT NULL,
  `registered_date` datetime NOT NULL,
  PRIMARY KEY (`booking_id`),
  KEY `booking_fk1` (`schedule_id`,`slot_close`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contain details on bookings' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bm_booking_conflict`
--

DROP TABLE IF EXISTS `bm_booking_conflict`;
CREATE TABLE IF NOT EXISTS `bm_booking_conflict` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `known_date` datetime NOT NULL,
  PRIMARY KEY (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Books Found in Conflict' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bm_calendar`
--

DROP TABLE IF EXISTS `bm_calendar`;
CREATE TABLE IF NOT EXISTS `bm_calendar` (
  `calendar_date` date NOT NULL COMMENT 'date and table key',
  `y` smallint(6) DEFAULT NULL COMMENT 'year where date occurs',
  `q` tinytext COMMENT 'quarter of the year date belongs',
  `m` tinyint(4) DEFAULT NULL COMMENT 'month of the year',
  `d` tinyint(4) DEFAULT NULL COMMENT 'numeric date part',
  `dw` tinyint(4) DEFAULT NULL COMMENT 'day number of the date in a week',
  `month_name` varchar(9) DEFAULT NULL COMMENT 'text name of the month',
  `day_name` varchar(9) DEFAULT NULL COMMENT 'text name of the day',
  `w` tinyint(4) DEFAULT NULL COMMENT 'week number in the year',
  `is_week_day` tinyint(4) DEFAULT NULL COMMENT 'true value if current date falls between monday-friday',
  PRIMARY KEY (`calendar_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Calender table that store the next 10 years of dates';

-- --------------------------------------------------------

--
-- Table structure for table `bm_calendar_months`
--

DROP TABLE IF EXISTS `bm_calendar_months`;
CREATE TABLE IF NOT EXISTS `bm_calendar_months` (
  `y` smallint(6) NOT NULL DEFAULT '0' COMMENT 'year where date occurs',
  `m` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'month of the year',
  `month_name` varchar(9) DEFAULT NULL COMMENT 'text name of the month',
  `m_sweek` tinyint(4) DEFAULT NULL COMMENT 'week number in the year',
  `m_eweek` tinyint(4) DEFAULT NULL COMMENT 'week number in the year',
  PRIMARY KEY (`y`,`m`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Calender table that store the next x years in month aggerates';

-- --------------------------------------------------------

--
-- Table structure for table `bm_calendar_quarters`
--

DROP TABLE IF EXISTS `bm_calendar_quarters`;
CREATE TABLE IF NOT EXISTS `bm_calendar_quarters` (
  `y` smallint(6) NOT NULL DEFAULT '0' COMMENT 'year where date occurs',
  `q` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'quarter of the year date belongs',
  `m_start` date DEFAULT NULL COMMENT 'starting month',
  `m_end` date DEFAULT NULL COMMENT 'ending_months',
  PRIMARY KEY (`y`,`q`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Calender table that store the next x years in month quarter aggerates';

-- --------------------------------------------------------

--
-- Table structure for table `bm_calendar_weeks`
--

DROP TABLE IF EXISTS `bm_calendar_weeks`;
CREATE TABLE IF NOT EXISTS `bm_calendar_weeks` (
  `y` smallint(6) NOT NULL DEFAULT '0' COMMENT 'year where date occurs',
  `m` tinyint(4) DEFAULT NULL COMMENT 'month of the year',
  `w` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'week in the year',
  PRIMARY KEY (`y`,`w`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Calender table that store the next x years in week aggerates';

-- --------------------------------------------------------

--
-- Table structure for table `bm_calendar_years`
--

DROP TABLE IF EXISTS `bm_calendar_years`;
CREATE TABLE IF NOT EXISTS `bm_calendar_years` (
  `y` smallint(6) NOT NULL DEFAULT '0' COMMENT 'year where date occurs',
  `y_start` datetime NOT NULL,
  `y_end` datetime NOT NULL,
  PRIMARY KEY (`y`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Calender table that store the next x years';

-- --------------------------------------------------------

--
-- Table structure for table `bm_rule`
--

DROP TABLE IF EXISTS `bm_rule`;
CREATE TABLE IF NOT EXISTS `bm_rule` (
  `rule_id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_type_id` int(11) NOT NULL,
  `timeslot_id` int(11) NOT NULL,
  `repeat_minute` varchar(45) NOT NULL,
  `repeat_hour` varchar(45) NOT NULL,
  `repeat_dayofweek` varchar(45) NOT NULL,
  `repeat_dayofmonth` varchar(45) NOT NULL,
  `repeat_month` varchar(45) NOT NULL,
  `start_from` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `open_slot` int(11) NOT NULL,
  `close_slot` int(11) NOT NULL,
  `cal_year` int(11) NOT NULL,
  `is_single_day` tinyint(1) DEFAULT '0',
  `carry_from_id` int(11) NOT NULL,
  PRIMARY KEY (`rule_id`),
  KEY `rule_fk1` (`rule_type_id`),
  KEY `rule_fk2` (`timeslot_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Rule Slots' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bm_rule_schedule`
--

DROP TABLE IF EXISTS `bm_rule_schedule`;
CREATE TABLE IF NOT EXISTS `bm_rule_schedule` (
  `rule_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `is_rollover` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`rule_id`,`schedule_id`),
  KEY `rule_schedule_fk2` (`schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Links a rule to a schedule';

-- --------------------------------------------------------

--
-- Table structure for table `bm_rule_series`
--

DROP TABLE IF EXISTS `bm_rule_series`;
CREATE TABLE IF NOT EXISTS `bm_rule_series` (
  `rule_id` int(11) NOT NULL,
  `rule_type_id` int(11) NOT NULL,
  `cal_year` int(11) NOT NULL,
  `slot_open` datetime NOT NULL,
  `slot_close` datetime NOT NULL,
  PRIMARY KEY (`rule_id`,`cal_year`,`slot_close`),
  KEY `rule_series_fk2` (`rule_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Defines schedule slots affected by rule';

-- --------------------------------------------------------

--
-- Table structure for table `bm_rule_type`
--

DROP TABLE IF EXISTS `bm_rule_type`;
CREATE TABLE IF NOT EXISTS `bm_rule_type` (
  `rule_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_code` char(6) NOT NULL,
  `is_work_day` tinyint(1) DEFAULT '0',
  `is_exclusion` tinyint(1) DEFAULT '0',
  `is_inc_override` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`rule_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Defines basic avability rules' AUTO_INCREMENT=5 ;

--
-- Dumping data for table `bm_rule_type`
--

INSERT INTO `bm_rule_type` (`rule_type_id`, `rule_code`, `is_work_day`, `is_exclusion`, `is_inc_override`) VALUES
(1, 'workda', 1, 0, 0),
(2, 'break', 0, 1, 0),
(3, 'holida', 0, 1, 0),
(4, 'overti', 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `bm_schedule`
--

DROP TABLE IF EXISTS `bm_schedule`;
CREATE TABLE IF NOT EXISTS `bm_schedule` (
  `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
  `timeslot_id` int(11) NOT NULL,
  `membership_id` int(11) NOT NULL,
  `calendar_year` int(11) NOT NULL,
  `registered_date` datetime NOT NULL,
  `close_date` date DEFAULT NULL,
  `is_carryover` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`schedule_id`),
  UNIQUE KEY `schedule_uniq1` (`membership_id`,`calendar_year`),
  KEY `schedule_fk1` (`timeslot_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='A Members schedule details' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bm_schedule_membership`
--

DROP TABLE IF EXISTS `bm_schedule_membership`;
CREATE TABLE IF NOT EXISTS `bm_schedule_membership` (
  `membership_id` int(11) NOT NULL AUTO_INCREMENT,
  `registered_date` datetime NOT NULL,
  PRIMARY KEY (`membership_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Used to group schedules by externel membership entity' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bm_schedule_slot`
--

DROP TABLE IF EXISTS `bm_schedule_slot`;
CREATE TABLE IF NOT EXISTS `bm_schedule_slot` (
  `schedule_id` int(11) NOT NULL,
  `slot_open` datetime NOT NULL,
  `slot_close` datetime NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT '0',
  `is_excluded` tinyint(1) DEFAULT '0',
  `is_override` tinyint(1) DEFAULT '0',
  `is_closed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`schedule_id`,`slot_close`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='A Members schedule details';

-- --------------------------------------------------------

--
-- Table structure for table `bm_schedule_team`
--

DROP TABLE IF EXISTS `bm_schedule_team`;
CREATE TABLE IF NOT EXISTS `bm_schedule_team` (
  `team_id` int(11) NOT NULL AUTO_INCREMENT,
  `timeslot_id` int(11) NOT NULL,
  `registered_date` datetime NOT NULL,
  PRIMARY KEY (`team_id`),
  KEY `schedule_team_fk1` (`timeslot_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Group schedules together with a common timeslot' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bm_schedule_team_members`
--

DROP TABLE IF EXISTS `bm_schedule_team_members`;
CREATE TABLE IF NOT EXISTS `bm_schedule_team_members` (
  `team_id` int(11) NOT NULL,
  `membership_id` int(11) NOT NULL,
  `registered_date` datetime NOT NULL,
  `schedule_id` int(11) NOT NULL,
  PRIMARY KEY (`team_id`,`membership_id`),
  KEY `schedule_team_members_fk1` (`membership_id`),
  KEY `schedule_team_members_fk3` (`schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Relates members to teams only for a single calendar year (match the schedule)';

-- --------------------------------------------------------

--
-- Table structure for table `bm_timeslot`
--

DROP TABLE IF EXISTS `bm_timeslot`;
CREATE TABLE IF NOT EXISTS `bm_timeslot` (
  `timeslot_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Table Primary key',
  `timeslot_length` int(11) NOT NULL COMMENT 'Number of minutes in the slot',
  `is_active_slot` tinyint(1) DEFAULT '1' COMMENT 'Be used in new schedules',
  PRIMARY KEY (`timeslot_id`),
  UNIQUE KEY `timeslot_length_UNIQUE` (`timeslot_length`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This describes the intervals lengths of each timeslots that used by schedules' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bm_timeslot_day`
--

DROP TABLE IF EXISTS `bm_timeslot_day`;
CREATE TABLE IF NOT EXISTS `bm_timeslot_day` (
  `timeslot_day_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Table Primary key',
  `timeslot_id` int(11) NOT NULL COMMENT 'FK to slot table',
  `open_minute` int(11) NOT NULL COMMENT 'Closing Minute component',
  `close_minute` int(11) NOT NULL COMMENT 'Closing Minute component',
  PRIMARY KEY (`timeslot_day_id`),
  UNIQUE KEY `timeslot_day_uqidx_1` (`timeslot_id`,`close_minute`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='the timeslots for a given day' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bm_timeslot_year`
--

DROP TABLE IF EXISTS `bm_timeslot_year`;
CREATE TABLE IF NOT EXISTS `bm_timeslot_year` (
  `timeslot_year_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Table Primary key',
  `timeslot_id` int(11) NOT NULL COMMENT 'FK to slot table',
  `y` smallint(6) DEFAULT NULL COMMENT 'year where date occurs',
  `m` tinyint(4) DEFAULT NULL COMMENT 'month of the year',
  `d` tinyint(4) DEFAULT NULL COMMENT 'numeric date part',
  `dw` tinyint(4) DEFAULT NULL COMMENT 'day number of the date in a week',
  `w` tinyint(4) DEFAULT NULL COMMENT 'week number in the year',
  `open_minute` int(11) NOT NULL COMMENT 'Closing Minute component',
  `close_minute` int(11) NOT NULL COMMENT 'Closing Minute component',
  `closing_slot` datetime NOT NULL COMMENT 'The closing slot time',
  `opening_slot` datetime NOT NULL COMMENT 'The opening slot time',
  PRIMARY KEY (`timeslot_year_id`),
  UNIQUE KEY `timeslot_year_uqidx_1` (`timeslot_id`,`closing_slot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='the timeslots for a given year' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `ints`
--

DROP TABLE IF EXISTS `ints`;
CREATE TABLE IF NOT EXISTS `ints` (
  `i` tinyint(4) NOT NULL,
  PRIMARY KEY (`i`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='seed table for creating calender';

--
-- Dumping data for table `ints`
--

INSERT INTO `ints` (`i`) VALUES
(0),
(1),
(2),
(3),
(4),
(5),
(6),
(7),
(8),
(9);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bm_booking`
--
ALTER TABLE `bm_booking`
  ADD CONSTRAINT `booking_fk1` FOREIGN KEY (`schedule_id`, `slot_close`) REFERENCES `bm_schedule_slot` (`schedule_id`, `slot_close`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `bm_booking_conflict`
--
ALTER TABLE `bm_booking_conflict`
  ADD CONSTRAINT `booking_conflict_fk1` FOREIGN KEY (`booking_id`) REFERENCES `bm_booking` (`booking_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `bm_rule`
--
ALTER TABLE `bm_rule`
  ADD CONSTRAINT `rule_fk1` FOREIGN KEY (`rule_type_id`) REFERENCES `bm_rule_type` (`rule_type_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `rule_fk2` FOREIGN KEY (`timeslot_id`) REFERENCES `bm_timeslot` (`timeslot_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `bm_rule_schedule`
--
ALTER TABLE `bm_rule_schedule`
  ADD CONSTRAINT `rule_schedule_fk1` FOREIGN KEY (`rule_id`) REFERENCES `bm_rule` (`rule_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `rule_schedule_fk2` FOREIGN KEY (`schedule_id`) REFERENCES `bm_schedule` (`schedule_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `bm_rule_series`
--
ALTER TABLE `bm_rule_series`
  ADD CONSTRAINT `rule_series_fk1` FOREIGN KEY (`rule_id`) REFERENCES `bm_rule` (`rule_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `rule_series_fk2` FOREIGN KEY (`rule_type_id`) REFERENCES `bm_rule_type` (`rule_type_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `bm_schedule`
--
ALTER TABLE `bm_schedule`
  ADD CONSTRAINT `schedule_fk1` FOREIGN KEY (`timeslot_id`) REFERENCES `bm_timeslot` (`timeslot_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `schedule_fk2` FOREIGN KEY (`membership_id`) REFERENCES `bm_schedule_membership` (`membership_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `bm_schedule_slot`
--
ALTER TABLE `bm_schedule_slot`
  ADD CONSTRAINT `schedule_slot_fk1` FOREIGN KEY (`schedule_id`) REFERENCES `bm_schedule` (`schedule_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `bm_schedule_team`
--
ALTER TABLE `bm_schedule_team`
  ADD CONSTRAINT `schedule_team_fk1` FOREIGN KEY (`timeslot_id`) REFERENCES `bm_timeslot` (`timeslot_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `bm_schedule_team_members`
--
ALTER TABLE `bm_schedule_team_members`
  ADD CONSTRAINT `schedule_team_members_fk1` FOREIGN KEY (`membership_id`) REFERENCES `bm_schedule_membership` (`membership_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `schedule_team_members_fk2` FOREIGN KEY (`team_id`) REFERENCES `bm_schedule_team` (`team_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `schedule_team_members_fk3` FOREIGN KEY (`schedule_id`) REFERENCES `bm_schedule` (`schedule_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `bm_timeslot_day`
--
ALTER TABLE `bm_timeslot_day`
  ADD CONSTRAINT `timeslot_day_fk_1` FOREIGN KEY (`timeslot_id`) REFERENCES `bm_timeslot` (`timeslot_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `bm_timeslot_year`
--
ALTER TABLE `bm_timeslot_year`
  ADD CONSTRAINT `timeslot_year_fk_1` FOREIGN KEY (`timeslot_id`) REFERENCES `bm_timeslot` (`timeslot_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
