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
    
    /**
     * @var integer the number of times to try and obtain a lock
     */ 
    protected $iLockRetry;
    
    
    
    protected function lockScheduleTables()
    {
        $oDatabase              = $this->oDatabaseAdapter;
        $sScheduleTableName     = $this->aTableNames['bm_schedule'];
        $sScheduleSlotTableName = $this->aTableNames['bm_schedule_slot'];
        $bLockObtained          = false;
        $iLockCount             = $this->iLockRetry;
        
        do {
            
            try {
                
                $sLockSql = " LOCK TABLES $sScheduleTableName     WRITE , 
                                          $sScheduleTableName     WRITE AS t1, 
                                          $sScheduleTableName     WRITE AS t2, 
                                          $sScheduleSlotTableName WRITE ";
     
           
                $bLockObtained = $oDatabase->executeUpdate($sLockSql,[],[]);
                
            } catch(DBALException $e) {
                $iLockCount = $iLockCount - 1;
            }
            
        } while($bLockObtained === false && $iLockCount > 0);
        
        return $bLockObtained;
        
    }
    
    
    
    public function __construct(array $aTableNames, Connection $oDatabaseAdapter, $iLockRetry = 3)
    {
        $this->oDatabaseAdapter = $oDatabaseAdapter;
        $this->aTableNames      = $aTableNames;
        $this->iLockRetry       = $iLockRetry;
       
    
    }
    
    
    public function handle(RolloverSchedulesCommand $oCommand)
    {
        $oDatabase              = $this->oDatabaseAdapter;
        $sScheduleTableName     = $this->aTableNames['bm_schedule'];
        $sScheduleSlotTableName = $this->aTableNames['bm_schedule_slot'];
        $sCalenderTableName     = $this->aTableNames['bm_calendar'];
        $sSlotTableName         = $this->aTableNames['bm_timeslot_day']; 
       
        $iCalendarYear          = $oCommand->getCalendarYearRollover();
        $iNextCalenderYear      = $iCalendarYear +1;
        
        $aSql                   = [];
        $a2Sql                  = [];
        $aFlagOffSql            = [];
      
       
       # Step 1 Get lock on the schedule Table and the slot table;
       if(false === $this->lockScheduleTables()) {
           ScheduleException::hasFailedRolloverSchedule($oCommand, new DBALException("Unable to get lock on $sScheduleTableName or $sScheduleSlotTableName"));
       }
       
       
       # Step 2 Turn off the carry over flag for any schedules that already existing in the next rollover period
       # these onces would have been created with start command.
       # we have obtained a write lock assume that no other schedule will be created until this rollover is done.
       
       $aFlagOffSql[] = " UPDATE $sScheduleTableName t1 ";
       $aFlagOffSql[] = " SET `t1`.`is_carryover` = false ";
       $aFlagOffSql[] = " LEFT JOIN $sScheduleTableName t2 on `t1`.`membership_id` = `t2`.`membership_id` and `t2`.`calendar_year` = :iNextCalYear ";
       $aFlagOffSql[] = " WHERE t2.schedule_id IS NOT NULL ";
       
        
        # Step 3 Create The schedule only for those with carry on flag and have not been done already which
        # could happend if manually created.
        
        $aSql[] = " INSERT INTO $sScheduleTableName (`schedule_id`,`timeslot_id`,`membership_id`,`calendar_year`,`registered_date`) ";
        $aSql[] = " SELECT `t1`.`schedule_id`, `t1`.timeslot_id`, `t1`.`membership_id`, :iNextCalYear , now() ";
        $aSql[] = " FROM $sScheduleTableName t1  ";
        $aSql[] = " WHERE `t1`.`is_carryover` = true AND `t1`.`calendar_year` = :iCalYear ";
      
        $sSql = implode(PHP_EOL,$aSql);
        
        # Step 4 Create new Slots for these schedules, rules will be applied in another operation.

        $a2Sql[] = " INSERT INTO $sScheduleSlotTableName (`timeslot_day_id`, `schedule_id`, `slot_open`, `slot_close`)  ";
        $a2Sql[] = " SELECT `sl`.`timeslot_day_id`, ?, (`c`.`calendar_date` + INTERVAL `sl`.`open_minute` MINUTE) , (`c`.`calendar_date` + INTERVAL `sl`.`close_minute` MINUTE) ";
        $a2Sql[] = " FROM $sScheduleTableName t1 ";
        $a2Sql[] = " CROSS JOIN $sCalenderTableName c ";
        $a2Sql[] = " CROSS JOIN $sSlotTableName sl ";
        $a2Sql[] = " WHERE `c`.`y` = :iNextCalYear ";
        $a2Sql[] = " AND `t1`.`is_carryover` = true AND `t1`.`calendar_year` = :iNextCalYear ";  
        $a2Sql[] = " AND `t1`.`timeslot_id` = `sl`.`timeslot_id` ";  
        
        $s2Sql .= implode(PHP_EOL,$a2Sql);

        
        # Step 5 Unlock the schedule tables
        
        $sUnlockSql = " UNLOCK TABLES ";
        
        
        
        try {
            
                
	        $oDateType = Type::getType(Type::DATE);
	        $oIntType  = Type::getType(Type::INTEGER);
	        
            
            # Execute the carryflag off 
            $oDatabase->executeUpdate($aFlagOffSql,[':iNextCalYear' => $iNextCalenderYear],[$oIntType]);
            
        
	        # Execute Schedule Rollover
	        $iNumberRolledOver = $oDatabase->executeUpdate($sSql,[':iNextCalYear' => $iNextCalenderYear,':iCalYear' => $iCalendarYear] , [$oIntType,$oIntType]);
	        
	        
	        $oCommand->setRollOverNumber($iNumberRolledOver);
	      
	        # Execute the slots carryover table
	        $iNumberNewSlots = $oDatabase->executeUpdate($s2Sql,[':iNextCalYear' => $iNextCalenderYear] , [$oIntType]);
	        
	        if($iNumberNewSlots == 0) {
	            throw new DBALException('Unable to create slots for rolled over schedules');
	        }
	        
	        # unlock the schedule tables
	        $oDatabase->executeUpdate($sUnlockSql);
                 
	    }
	    catch(DBALException $e) {
	        throw ScheduleException::hasFailedRolloverSchedule($oCommand, $e);
	    }
        
        
        return true;
    }
     
    
}
/* End of File */