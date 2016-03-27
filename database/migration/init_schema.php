<?php
namespace Migration\Components\Migration\Entities;

use Doctrine\DBAL\Connection,
    Doctrine\DBAL\Schema\AbstractSchemaManager as Schema,
    Migration\Components\Migration\EntityInterface;

class init_schema implements EntityInterface
{

    public function up(Connection $db, Schema $sc)
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
            CREATE TABLE IF NOT EXISTS `ints` (
              `i` TINYINT NOT NULL,
              PRIMARY KEY (`i`)
            )
            ENGINE = InnoDB
            COMMENT = 'seed table for creating calender'
        ");

    }

    public function down(Connection $db, Schema $sc)
    {


    }


}
/* End of File */
