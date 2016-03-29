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
        
        
        
        
    }


    public function up(Connection $db, Schema $sc)
    {
        $this->buildUtilityTables($db,$sc);
        $this->buildCalendarTables($db,$sc);
        $this->buildSlotTables($db,$sc);
        $this->buildScheduleTables($db,$sc);
         

    }

    public function down(Connection $db, Schema $sc)
    {


    }


}
/* End of File */
