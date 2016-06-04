<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\LookBookingConflictsCommand;
use IComeFromTheNet\BookMe\Bus\Exception\BookingException;


/**
 * Search for Booking Conflicts that occur when availability rules are applied
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class LookBookingConflictsHandler 
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
    
    
    public function handle(LookBookingConflictsCommand $oCommand)
    {
        $oDatabase              = $this->oDatabaseAdapter;
        $sConflictTableName     = $this->aTableNames['bm_booking_conflict'];
        $sScheduleSlotTableName = $this->aTableNames['bm_schedule_slot'];
        $sBookingTable          = $this->aTableNames['bm_booking'];
        $oNow                   = $oCommand->getNow();
        $aFindConflictsSql      = [];
        $sFindConflictsSql      = '';
        
       
        $sClearConflicts     = " DELETE FROM $sConflictTableName WHERE 1=1 "; 
       
        
        $aFindConflictsSql[] = " INSERT INTO $sConflictTableName (`booking_id`,`known_date`)  ";
        // Find bookings where availability was removed
        $aFindConflictsSql[] = " SELECT distinct(`booking_id`), NOW() FROM $sScheduleSlotTableName ";
        $aFindConflictsSql[] = " WHERE  `is_available` = false AND `is_override` = false AND `booking_id` IS NOT NULL ";
        $aFindConflictsSql[] = " AND `slot_open` >= ? ";
        $aFindConflictsSql[] = " UNION ";
        // Find bookings where exclusion rule been applied and no overrride
        $aFindConflictsSql[] = " SELECT distinct(`booking_id`), NOW() FROM $sScheduleSlotTableName ";
        $aFindConflictsSql[] = " WHERE  `is_excluded` = true AND `is_override` = false AND `booking_id` IS NOT NULL ";
        $aFindConflictsSql[] = " AND `slot_open` >= ? ";
        $aFindConflictsSql[] = " UNION ";
        // Find bookings where the schedule has been closed        
        $aFindConflictsSql[] = " SELECT distinct(`booking_id`), NOW() FROM $sScheduleSlotTableName ";
        $aFindConflictsSql[] = " WHERE  `is_closed` = true AND `booking_id` IS NOT NULL ";
        $aFindConflictsSql[] = " AND `slot_open` >= ?";
        
        $sFindConflictsSql = implode(PHP_EOL,$aFindConflictsSql);
      
        
        try {
            # Delete conflicts where only storing the results from last command run
            
	        $oDatabase->executeUpdate($sClearConflicts);
	        
	        # Execute the conflict check
	        
	        $iRowsAffected = $oDatabase->executeUpdate($sFindConflictsSql, [$oNow,$oNow,$oNow], [Type::DATE,Type::DATE,Type::DATE]);
	     
	        $oCommand->setNumberConflictsFound($iRowsAffected);
	        
	        
	    }
	    catch(DBALException $e) {
	        throw new BookingException('Unable to execute booking conflict query', 0,$e);
	    }
        
        
        return true;
    }
     
    
}
/* End of File */