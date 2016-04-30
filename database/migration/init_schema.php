<?php
namespace Migration\Components\Migration\Entities;

use Doctrine\DBAL\Connection,
    Doctrine\DBAL\Schema\AbstractSchemaManager as Schema,
    Migration\Components\Migration\EntityInterface;

class init_schema implements EntityInterface
{

    protected function buildUtilityTables(Connection $db, Schema $sc)
    {
           $db->executeUpdate("    
            CREATE TABLE IF NOT EXISTS `ints` (
              `i` TINYINT NOT NULL,
              PRIMARY KEY (`i`)
            )
            ENGINE = InnoDB
            COMMENT = 'seed table for creating calender'
        ");
        
    }


    protected function buildCalendarTables(Connection $db, Schema $sc)
    {
          $db->executeUpdate("
            CREATE TABLE IF NOT EXISTS `bm_calendar` (
                  `calendar_date` DATE NOT NULL COMMENT 'date and table key',
                  `y` SMALLINT NULL COMMENT 'year where date occurs',
                  `q` TINYTEXT NULL COMMENT 'quarter of the year date belongs',
                  `m` TINYINT NULL COMMENT 'month of the year',
                  `d` TINYINT NULL COMMENT 'numeric date part',
                  `dw` TINYINT NULL COMMENT 'day number of the date in a week',
                  `month_name` VARCHAR(9) NULL COMMENT 'text name of the month',
                  `day_name` VARCHAR(9) NULL COMMENT 'text name of the day',
                  `w` TINYINT NULL COMMENT 'week number in the year',
                  `is_week_day` TINYINT NULL COMMENT 'true value if current date falls between monday-friday',
                  
                  PRIMARY KEY (`calendar_date`)
                    
            )
            ENGINE = InnoDB
            COMMENT = 'Calender table that store the next 10 years of dates'");
            
            
     
        
        
        $db->executeUpdate("
            CREATE TABLE IF NOT EXISTS `bm_calendar` (
                  `calendar_date` DATE NOT NULL COMMENT 'date and table key',
                  `y` SMALLINT NULL COMMENT 'year where date occurs',
                  `q` TINYTEXT NULL COMMENT 'quarter of the year date belongs',
                  `m` TINYINT NULL COMMENT 'month of the year',
                  `d` TINYINT NULL COMMENT 'numeric date part',
                  `dw` TINYINT NULL COMMENT 'day number of the date in a week',
                  `month_name` VARCHAR(9) NULL COMMENT 'text name of the month',
                  `day_name` VARCHAR(9) NULL COMMENT 'text name of the day',
                  `w` TINYINT NULL COMMENT 'week number in the year',
                  `is_week_day` TINYINT NULL COMMENT 'true value if current date falls between monday-friday',
                  
                  PRIMARY KEY (`calendar_date`)
                    
            )
            ENGINE = InnoDB
            COMMENT = 'Calender table that store the next 10 years of dates'");
          
        $db->executeUpdate("
            CREATE TABLE IF NOT EXISTS `bm_calendar_weeks` (
                 `y` SMALLINT NULL COMMENT 'year where date occurs',
                 `m` TINYINT NULL COMMENT 'month of the year',
                 `w` TINYINT NULL COMMENT 'week in the year',
                 
                 PRIMARY KEY(`y`,`w`)
                 
            )
            ENGINE = InnoDB
            COMMENT = 'Calender table that store the next x years in week aggerates'"
        );
        
        
        $db->executeUpdate("
            CREATE TABLE IF NOT EXISTS `bm_calendar_months` (
                 `y` SMALLINT NULL COMMENT 'year where date occurs',
                 `m` TINYINT NULL COMMENT 'month of the year',
                 `month_name` VARCHAR(9) NULL COMMENT 'text name of the month',
                 `m_sweek` TINYINT NULL COMMENT 'week number in the year',
                 `m_eweek` TINYINT NULL COMMENT 'week number in the year',
                 
                 PRIMARY KEY(`y`,`m`)
                 
            )
            ENGINE = InnoDB
            COMMENT = 'Calender table that store the next x years in month aggerates'
        ");
        
        $db->executeUpdate("
            CREATE TABLE IF NOT EXISTS `bm_calendar_quarters` (
                 `y` SMALLINT NULL COMMENT 'year where date occurs',
                 `q` TINYINT NULL COMMENT 'quarter of the year date belongs',
                 `m_start` DATE NULL COMMENT 'starting month',
                 `m_end` DATE NULL COMMENT 'ending_months',
                 
                 PRIMARY KEY(`y`,`q`)
            )
            ENGINE = InnoDB
            COMMENT = 'Calender table that store the next x years in month quarter aggerates'

        ");
        
        $db->executeUpdate("
            CREATE TABLE IF NOT EXISTS `bm_calendar_years` (
                 `y` SMALLINT NULL COMMENT 'year where date occurs',
                 `y_start` DATETIME NOT NULL,
                 `y_end` DATETIME NOT NULL,

                 PRIMARY KEY(`y`)
            )
            ENGINE = InnoDB
            COMMENT = 'Calender table that store the next x years'
        ");
     
        
    }

    protected function buildSlotTables(Connection $db, Schema $sc)
    {
        $db->executeUpdate("
        CREATE TABLE IF NOT EXISTS `bm_timeslot` (
          `timeslot_id`     INT NOT NULL AUTO_INCREMENT COMMENT 'Table Primary key',
          `timeslot_length` INT NOT NULL                COMMENT 'Number of minutes in the slot',
          `is_active_slot`  BOOLEAN DEFAULT true       COMMENT 'Be used in new schedules',
          
          PRIMARY KEY (`timeslot_id`),
          UNIQUE INDEX `timeslot_length_UNIQUE` (`timeslot_length` ASC))
        ENGINE = InnoDB
        COMMENT = 'This describes the intervals lengths of each timeslots that used by schedules';
        ");
        
        
        $db->executeUpdate("
        CREATE TABLE IF NOT EXISTS `bm_timeslot_day` (
          `timeslot_day_id`   INT NOT NULL AUTO_INCREMENT COMMENT 'Table Primary key',
          `timeslot_id`       INT NOT NULL COMMENT 'FK to slot table',
          `open_minute`       INT NOT NULL COMMENT 'Closing Minute component',    
          `close_minute`      INT NOT NULL COMMENT 'Closing Minute component',    
         
          PRIMARY KEY (`timeslot_day_id`),
          UNIQUE INDEX `timeslot_day_uqidx_1` (`timeslot_id`,`close_minute`),
          CONSTRAINT `timeslot_day_fk_1`
            FOREIGN KEY (`timeslot_id`)
            REFERENCES `bm_timeslot` (`timeslot_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
        )
        ENGINE = InnoDB
        COMMENT = 'the timeslots for a given day';
        ");
        
        $db->executeUpdate("
        CREATE TABLE IF NOT EXISTS `bm_timeslot_year` (
          `timeslot_year_id`  INT NOT NULL AUTO_INCREMENT COMMENT 'Table Primary key',
          `timeslot_id`       INT NOT NULL COMMENT 'FK to slot table',
          `y`                 SMALLINT NULL COMMENT 'year where date occurs',
          `m`                 TINYINT NULL COMMENT 'month of the year',
          `d`                 TINYINT NULL COMMENT 'numeric date part',
          `dw`                TINYINT NULL COMMENT 'day number of the date in a week',
          `w`                 TINYINT NULL COMMENT 'week number in the year',
          `open_minute`       INT NOT NULL COMMENT 'Closing Minute component',    
          `close_minute`      INT NOT NULL COMMENT 'Closing Minute component', 
         
          `closing_slot`      DATETIME NOT NULL COMMENT 'The closing slot time',
          `opening_slot`      DATETIME NOT NULL COMMENT 'The opening slot time',
         
         
          PRIMARY KEY (`timeslot_year_id`),
          UNIQUE INDEX `timeslot_year_uqidx_1` (`timeslot_id`,`closing_slot`),
          CONSTRAINT `timeslot_year_fk_1`
            FOREIGN KEY (`timeslot_id`)
            REFERENCES `bm_timeslot` (`timeslot_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
        )
        ENGINE = InnoDB
        COMMENT = 'the timeslots for a given year';
        ");
        
    }


    protected function buildScheduleTables(Connection $db, Schema $sc)
    {
        
        $db->executeUpdate("
        CREATE TABLE IF NOT EXISTS `bm_schedule_membership` (
          `membership_id`   INT NOT NULL AUTO_INCREMENT,
          `registered_date` DATETIME NOT NULL,
          
          PRIMARY KEY (`membership_id`)
        )
        ENGINE = InnoDB
        COMMENT = 'Used to group schedules by externel membership entity';
        ");
        
        
        $db->executeUpdate("
        CREATE TABLE IF NOT EXISTS `bm_schedule_team` (
          `team_id`         INT NOT NULL AUTO_INCREMENT,
          `timeslot_id`     INT NOT NULL,
          `registered_date` DATETIME NOT NULL,
          
          
          PRIMARY KEY (`team_id`),
          CONSTRAINT `schedule_team_fk1`
            FOREIGN KEY (`timeslot_id`)
            REFERENCES `bm_timeslot` (`timeslot_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
        )
        ENGINE = InnoDB
        COMMENT = 'Group schedules together with a common timeslot';
        ");
        
        
        
        $db->executeUpdate("
        CREATE TABLE IF NOT EXISTS `bm_schedule` (
          `schedule_id`     INT NOT NULL AUTO_INCREMENT,
          `timeslot_id`     INT NOT NULL,
          `membership_id`    INT NOT NULL,
          `calendar_year`   INT NOT NULL,
          `registered_date` DATETIME NOT NULL,
          `close_date`     DATE NULL,
          `is_carryover`   BOOLEAN DEFAULT true,
          
          
          PRIMARY KEY (`schedule_id`),
          UNIQUE INDEX `schedule_uniq1` (`membership_id`,`calendar_year`),
          CONSTRAINT `schedule_fk1`
            FOREIGN KEY (`timeslot_id`)
            REFERENCES `bm_timeslot` (`timeslot_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
          CONSTRAINT `schedule_fk2`
            FOREIGN KEY (`membership_id`)
            REFERENCES `bm_schedule_membership` (`membership_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
        )
        ENGINE = InnoDB
        COMMENT = 'A Members schedule details';
        ");
        
        
        $db->executeUpdate("
        CREATE TABLE IF NOT EXISTS `bm_schedule_slot` (
          `schedule_id`    INT NOT NULL,
          
          `slot_open`   DATETIME NOT NULL,
          `slot_close` DATETIME NOT NULL,
          
          `booking_id`    INT  DEFAULT NULL,
          
          `is_available` BOOLEAN DEFAULT false,
          `is_excluded`  BOOLEAN DEFAULT false,
          `is_override`  BOOLEAN DEFAULT false,
          `is_closed`  BOOLEAN DEFAULT false,
          
          
          PRIMARY KEY (`schedule_id`,`slot_close`),
          CONSTRAINT `schedule_slot_fk1`
            FOREIGN KEY (`schedule_id`)
            REFERENCES `bm_schedule` (`schedule_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
        
        )
        ENGINE = InnoDB
        COMMENT = 'A Members schedule details';
        ");
        
       
        
        $db->executeUpdate("
        CREATE TABLE IF NOT EXISTS `bm_booking` (
          `booking_id`              INT NOT NULL AUTO_INCREMENT,
          `schedule_id`             INT NOT NULL,
          `slot_open`               DATETIME NOT NULL,
          `slot_close`              DATETIME NOT NULL,
          `registered_date`         DATETIME NOT NULL,  
   
          PRIMARY KEY (`booking_id`),
         CONSTRAINT `booking_fk1`
            FOREIGN KEY (`schedule_id`,`slot_close`)
            REFERENCES `bm_schedule_slot` (`schedule_id`,`slot_close`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
       )
       ENGINE = InnoDB
       COMMENT = 'Contain details on bookings';
     
       ");
      
      $db->executeUpdate("
          CREATE TABLE IF NOT EXISTS `bm_booking_conflict` (
            `booking_id`      INT NOT NULL AUTO_INCREMENT,
            `known_date`      DATETIME NOT NULL,  
            
            PRIMARY KEY (`booking_id`),
            CONSTRAINT `booking_conflict_fk1`
              FOREIGN KEY (`booking_id`)
              REFERENCES `bm_booking` (`booking_id`)
              ON DELETE NO ACTION
              ON UPDATE NO ACTION
       
         )
         ENGINE = InnoDB
         COMMENT = 'Books Found in Conflict';
       ");
        
        $db->executeUpdate("
        CREATE TABLE IF NOT EXISTS `bm_schedule_team_members` (
          `team_id`         INT NOT NULL,
          `membership_id`   INT NOT NULL,
          `registered_date` DATETIME NOT NULL,
          `schedule_id`     INT NOT NULL,
          
          
          PRIMARY KEY (`team_id`,`membership_id`),
          CONSTRAINT `schedule_team_members_fk1`
            FOREIGN KEY (`membership_id`)
            REFERENCES `bm_schedule_membership` (`membership_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
          CONSTRAINT `schedule_team_members_fk2`
            FOREIGN KEY (`team_id`)
            REFERENCES `bm_schedule_team` (`team_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
          CONSTRAINT `schedule_team_members_fk3`
            FOREIGN KEY (`schedule_id`)
            REFERENCES `bm_schedule` (`schedule_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
        )
        ENGINE = InnoDB
        COMMENT = 'Relates members to teams only for a single calendar year (match the schedule)';
        ");
        
        
    }

    public function buildRulesTable(Connection $db, Schema $sc)
    {
        
        $db->executeUpdate("
        CREATE TABLE IF NOT EXISTS `bm_rule_type` (
          `rule_type_id` INT NOT NULL AUTO_INCREMENT,
          `rule_code`    CHAR(6) NOT NULL,
          
           PRIMARY KEY (`rule_type_id`)
        )
        ENGINE = InnoDB
        COMMENT = 'Defines basic avability rules';
      ");
      
      $db->executeUpdate("
        CREATE TABLE IF NOT EXISTS `bm_rule` (
          `rule_id`      INT NOT NULL AUTO_INCREMENT,
          `rule_type_id` INT NOT NULL,
          `timeslot_id`  INT NOT NULL,
          
          `repeat_minute` VARCHAR(45) NOT NULL,
          `repeat_hour` VARCHAR(45) NOT NULL,
          `repeat_dayofweek` VARCHAR(45) NOT NULL,
          `repeat_dayofmonth` VARCHAR(45) NOT NULL,
          `repeat_month` VARCHAR(45) NOT NULL,
            
          `start_from` DATETIME NOT NULL,
          `end_at`     DATETIME NOT NULL,  
          
          `open_slot`  INT NOT NULL,
          `close_slot` INT NOT NULL, 
          
          `cal_year`   INT NOT NULL,
          
          PRIMARY KEY (`rule_id`),
          CONSTRAINT `rule_fk1`
            FOREIGN KEY (`rule_type_id`)
            REFERENCES `bm_rule_type` (`rule_type_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
          CONSTRAINT `rule_fk2`
            FOREIGN KEY (`timeslot_id`)
            REFERENCES `bm_timeslot` (`timeslot_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
        )
        ENGINE = InnoDB
        COMMENT = 'Rule Slots';
      ");
      
      $db->executeUpdate("
        CREATE TABLE IF NOT EXISTS `bm_rule_series` (
          `rule_id`        INT NOT NULL,
          `rule_type_id`   INT NOT NULL,
          `cal_year`       INT NOT NULL,
          `slot_open`      DATETIME NOT NULL,
          `slot_close`     DATETIME NOT NULL,
          
         PRIMARY KEY (`cal_year`,`slot_close`),
         CONSTRAINT `rule_series_fk1`
            FOREIGN KEY (`rule_id`)
            REFERENCES `bm_rule` (`rule_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
         CONSTRAINT `rule_series_fk2`
            FOREIGN KEY (`rule_type_id`)
            REFERENCES `bm_rule_type` (`rule_type_id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
       )
        ENGINE = InnoDB
        COMMENT = 'Defines schedule slots affected by rule';
      ");
      
      
      
    }
  
    public function up(Connection $db, Schema $sc)
    {
        $this->buildUtilityTables($db,$sc);
        $this->buildCalendarTables($db,$sc);
        $this->buildSlotTables($db,$sc);
        $this->buildScheduleTables($db,$sc);
        $this->buildRulesTable($db,$sc);
    }

    public function down(Connection $db, Schema $sc)
    {


    }


}
/* End of File */
