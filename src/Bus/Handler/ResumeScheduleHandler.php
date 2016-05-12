<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\ResumeScheduleCommand;
use IComeFromTheNet\BookMe\Bus\Exception\ScheduleException;


/**
 * Used to restart a schedule that was stopped will open closed slots from the current datetime
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class ResumeScheduleHandler 
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
    
    
    public function handle(ResumeScheduleCommand $oCommand)
    {
        $oDatabase              = $this->oDatabaseAdapter;
        $sScheduleTableName     = $this->aTableNames['bm_schedule'];
        $sScheduleSlotTableName = $this->aTableNames['bm_schedule_slot'];
        $iScheduleId            = $oCommand->getScheduleId();
        $oOpenDate             = $oCommand->getFromDate();
        $aSql                   = [];
        $a2Sql                  = [];
        $a3Sql                  = [];
       
        
        # Step 1 Set the close date and the carryover
        
        $aSql[] = " UPDATE $sScheduleTableName  ";
        $aSql[] = " SET  is_carryover = true, close_date = null ";
        $aSql[] = " WHERE schedule_id = ? ";
        
        $sSql = implode(PHP_EOL,$aSql);
        
        # Step 2 Blackout the slots from the close date but first obtain a row lock to stop bookings
        
        $a2Sql[] = " SELECT `slot_open` ";
        $a2Sql[] = " FROM $sScheduleSlotTableName  ";
        $a2Sql[] = " WHERE schedule_id = ? ";
        $a2Sql[] = " AND slot_open >= ? ";
        $a2Sql[] = " FOR UPDATE ";
        
        $s2Sql = implode(PHP_EOL,$a2Sql);
        
        $a3Sql[] = " UPDATE $sScheduleSlotTableName  ";
        $a3Sql[] = " SET  is_closed = false ";
        $a3Sql[] = " WHERE schedule_id = ? ";
        $a3Sql[] = " AND slot_open >= ?";
          
        $s3Sql = implode(PHP_EOL,$a3Sql);
      
        
        try {
            
	        $oDateType = Type::getType(Type::DATE);
	        $oIntType  = Type::getType(Type::INTEGER);
	    
	        # Close the schedule
	        $iRowsAffected = $oDatabase->executeUpdate($sSql,[$iScheduleId], [$oIntType]);
	        
	        if($iRowsAffected == 0) {
	            throw new DBALException('Could not match a schedule to close please check database id');
	        }
	        
	        // Execute the lock Statement
	        $oDatabase->executeUpdate($s2Sql, [$iScheduleId, $oOpenDate], [$oIntType, $oDateType]);
	        
	        // Execute the Blockout update
	        $iRowsAffected = $oDatabase->executeUpdate($s3Sql, [$iScheduleId,$oOpenDate], [$oIntType,$oDateType]);
	        
	        if($iRowsAffected == 0) {
	            throw new DBALException('Could not match a schedule to  open dates please check database id');
	        }
                 
	    }
	    catch(DBALException $e) {
	        throw ScheduleException::hasFailedResumeSchedule($oCommand, $e);
	    }
        
        
        return true;
    }
     
    
}
/* End of File */