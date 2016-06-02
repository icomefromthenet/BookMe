<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\RolloverSchedulesCommand;
use IComeFromTheNet\BookMe\Bus\Exception\ScheduleException;


/**
 * Used to rollover last years schedule into the new year
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class RolloverSchedulesHandler 
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
    
    
    public function handle(RolloverSchedulesCommand $oCommand)
    {
        $oDatabase              = $this->oDatabaseAdapter;
        $sScheduleTableName     = $this->aTableNames['bm_schedule'];
        $sScheduleSlotTableName = $this->aTableNames['bm_schedule_slot'];
        $sCalenderTableName     = $this->aTableNames['bm_calendar'];
        $sSlotTableName         = $this->aTableNames['bm_timeslot_day']; 
        $sSlotYearTableName     = $this->aTableNames['bm_timeslot_year'];
       
        $iNextCalenderYear      = $oCommand->getNewCalendarYear();
        $iCalendarYear          = $iNextCalenderYear-1;
        
        
        $aHeader                = [];
        $aBuildSchedule         = [];
        $aFlagOffSql            = [];
      
       
       # Step 1 Turn off the carry over flag for any schedules that already existing in the next rollover period
       # these onces would have been created with start command.
       # we have obtained a write lock assume that no other schedule will be created until this rollover is done.
       
       $aFlagOffSql[] = " UPDATE $sScheduleTableName t1 ";
       $aFlagOffSql[] = " LEFT JOIN $sScheduleTableName t2 on `t1`.`membership_id` = `t2`.`membership_id` AND `t2`.`calendar_year` = :iNextCalYear ";
       $aFlagOffSql[] = " SET `t1`.`is_carryover` = false ";
       $aFlagOffSql[] = " WHERE t2.schedule_id IS NOT NULL ";
       
       $sFlagOffSql = implode(PHP_EOL,$aFlagOffSql);
        
        # Step 2 Create The schedule only for those with carry on flag and have not been done already which
        # could happend if manually created.
        
        $aHeader[] = " INSERT INTO $sScheduleTableName (`schedule_id`,`timeslot_id`,`membership_id`,`calendar_year`,`registered_date`) ";
        $aHeader[] = " SELECT NULL, `t1`.`timeslot_id`, `t1`.`membership_id`, ? , NOW() ";
        $aHeader[] = " FROM $sScheduleTableName t1  ";
        $aHeader[] = " WHERE `t1`.`is_carryover` = true AND `t1`.`calendar_year` = ? ";
      
        $sAddHeader = implode(PHP_EOL,$aHeader);
        
        # Step 3 Create new Slots for these schedules, rules will be applied in another operation.

        $aBuildSchedule[] = " INSERT INTO $sScheduleSlotTableName ( `schedule_id`, `slot_open`, `slot_close`)  ";
        $aBuildSchedule[] = " SELECT  `t1`.`schedule_id`, `c`.`opening_slot`, `c`.`closing_slot` ";
        $aBuildSchedule[] = " FROM $sScheduleTableName t1 ";
        $aBuildSchedule[] = " CROSS JOIN $sSlotYearTableName c ON `c`.`y` = :iNextCalYear ";
        $aBuildSchedule[] = " WHERE `t1`.`is_carryover` = true AND `t1`.`calendar_year` = :iNextCalYear ";  
        $aBuildSchedule[] = " AND `t1`.`timeslot_id` = `c`.`timeslot_id` ";  
        
        $sBuildSchedule .= implode(PHP_EOL,$aBuildSchedule);

        
        
        try {
            
                
	        $oDateType = Type::getType(Type::DATE);
	        $oIntType  = Type::getType(Type::INTEGER);
	        
            
            # Execute the carryflag off 
            $iFlagOff = $oDatabase->executeUpdate($sFlagOffSql,[':iNextCalYear' => $iNextCalenderYear],[$oIntType]);
            
        
	        # Execute Schedule Rollover
	        $iNumberRolledOver = $oDatabase->executeUpdate($sAddHeader,[$iNextCalenderYear,$iCalendarYear] , [$oIntType,$oIntType]);
	        
	        
	        $oCommand->setRollOverNumber($iNumberRolledOver);
	      
	        # Execute the slots carryover table
	        $iNumberNewSlots = $oDatabase->executeUpdate($sBuildSchedule,[':iNextCalYear' => $iNextCalenderYear] , [$oIntType]);
	        
	        if($iNumberNewSlots == 0) {
	            throw new DBALException('Unable to create slots for rolled over schedules');
	        }
	        
	      
	    }
	    catch(DBALException $e) {
	        throw ScheduleException::hasFailedRolloverSchedule($oCommand, $e);
	    }
        
        
        return true;
    }
     
    
}
/* End of File */