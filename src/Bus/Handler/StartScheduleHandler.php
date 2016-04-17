<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\StartScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Exception\ScheduleException;


/**
 * Used to create a schedule in the given calendar year. 
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class StartScheduleHandler 
{
    
    /**
     * @var array   a map internal table names to external names
     */ 
    protected $aTableNames;
    
    /**
     * @var Doctrine\DBAL\Connection    the database adpater
     */ 
    protected $oDatabaseAdapter;
    
    
    
    public function __construct(array $aTableNames, Connection $oDatabaseAdapter)
    {
        $this->oDatabaseAdapter = $oDatabaseAdapter;
        $this->aTableNames      = $aTableNames;
        
        
    }
    
    
    public function handle(StartScheduleCommand $oCommand)
    {
        $oDatabase              = $this->oDatabaseAdapter;
        $sScheduleTableName     = $this->aTableNames['bm_schedule'];
        $sScheduleSlotTableName = $this->aTableNames['bm_schedule_slot'];
        $sTimeSlotYearTableName = $this->aTableNames['bm_timeslot_year'];
        $sSlotTableName         = $this->aTableNames['bm_timeslot_day']; 
        
        $iScheduleId            = null;
        $iCalendarYear          = $oCommand->getCalendarYear();
        $iMemberId              = $oCommand->getMemberId();
        $iTimeSlotId            = $oCommand->getTimeSlotId();
        
        $aSql                   = [];
        $a2Sql                  = [];
       
        
        # Step 1 Create The schedule
        
        $aSql[] = " INSERT INTO $sScheduleTableName (`schedule_id`,`timeslot_id`,`membership_id`,`calendar_year`,`registered_date`) ";
        $aSql[] = " VALUES (NULL, ?, ?, ?, NOW()) ";
        
        $sSql = implode(PHP_EOL,$aSql);
        
        # Step 2 create slots for this calender year  
        
        
        $a2Sql[] = " INSERT INTO $sScheduleSlotTableName (`schedule_id`, `slot_open`, `slot_close`)  ";
        $a2Sql[] = " SELECT  ? , `c`.`opening_slot` as slot_open , `c`.`closing_slot` as slot_close ";
        $a2Sql[] = " FROM $sTimeSlotYearTableName c";
        $a2Sql[] = " WHERE `c`.`y` = ? ";
        $a2Sql[] = " AND `c`.`timeslot_id` = ? ";
          
          
        $s2Sql = implode(PHP_EOL,$a2Sql);
      
        
        try {
            
	        $oDateType = Type::getType(Type::DATE);
	        $oIntType  = Type::getType(Type::INTEGER);
	    
	        $oDatabase->executeUpdate($sSql, [$iTimeSlotId,$iMemberId,$iCalendarYear], [$oIntType,$oIntType,$oIntType]);
	        
	        $iScheduleId = $oDatabase->lastInsertId();
	        
	        if(true == empty($iScheduleId)) {
	            throw new DBALException('Could not Insert a new schedule');
	        }
	        
	        $oCommand->setScheduleId($iScheduleId);
	        
	        
	        $iRowsAffected = $oDatabase->executeUpdate($s2Sql, [$iScheduleId,$iCalendarYear,$iTimeSlotId], [$oIntType,$oIntType,$oIntType]);
	        
	        if($iRowsAffected == 0) {
	            throw new DBALException('Could not generate schedule slots');
	        }
                 
	    }
	    catch(DBALException $e) {
	        throw ScheduleException::hasFailedStartSchedule($oCommand, $e);
	    }
        
        
        return true;
    }
     
    
}
/* End of File */