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
    
    
    protected function testAvailabilityChanges()
    {
        
        
    }
    
    protected function testMaxBookingExceeded()
    {
        
        
    }
    
    
    public function handle(LookBookingConflictsCommand $oCommand)
    {
        $oDatabase              = $this->oDatabaseAdapter;
        $sConflictTableName     = $this->aTableNames['bm_booking_conflict'];
        $sScheduleSlotTableName = $this->aTableNames['bm_schedule_slot'];
        $sBookingTableName      = $this->aTableNames['bm_booking'];
        
        $iCalYear               = $oCommand->getCalYear();
        
        $aClearSlotsSql        = [];
        $sClearSlotsSql        = '';
        $aRemoveBookingSql     = [];
        $sRemoveBookingSql     = '';
        $aRemoveConflictSql    = [];
        $sRemoveConflictSql    = '';
        
        
        # Step 1 Clear the booking from the schedule rows
        
        $aClearSlotsSql[] = " UPDATE $sScheduleSlotTableName sl SET `sl`.`booking_id` = NULL ";
        $aClearSlotsSql[] = " JOIN $sBookingTableName b on `b`.`schedule_id` = `sl`.`schedule_id` ";
        $aClearSlotsSql[] = " WHERE `b`.`booking_id` = :iBookingDatabaseId ";
        $aClearSlotsSql[] = " AND `b`.`slot_open` >= `sl`.`slot_open` AND `b`.`slot_close` <= `sl`.`slot_close` ";
        
        $sClearSlotsSql = implode(PHP_EOL,$aClearSlotsSql);
        
        # Step 2 Clear the conflict booking table
        
        $aRemoveConflictSql[] = " DELETE FROM  $sConflictTableName  ";
        $aRemoveConflictSql[] = " WHERE booking_id = :iBookingDatabaseId ";
          
        $sRemoveConflictSql = implode(PHP_EOL,$aRemoveConflictSql);
       
          
        # Step 3 Clear the booking table  
        
        $aRemoveBookingSql[] = " DELETE FROM  $sBookingTableName  ";
        $aRemoveBookingSql[] = " WHERE booking_id = :iBookingDatabaseId ";
          
        $sRemoveBookingSql = implode(PHP_EOL,$aRemoveConflictSql);
      
        
        try {
            
	        $oIntType  = Type::getType(Type::INTEGER);
	    
	       	$iRowsAffected = $oDatabase->executeUpdate($sClearSlotsSql, ['iBookingDatabaseId' => $sClearSlotsSql], [$oIntType]);
	        
	        if($iRowsAffected == 0) {
	            throw new DBALException('Could not clear of a booking slots');
	        }
	        
	        $iRowsAffected = $oDatabase->executeUpdate($sRemoveConflictSql, ['iBookingDatabaseId' => $sClearSlotsSql], [$oIntType]);
	        
	        
	        $iRowsAffected = $oDatabase->executeUpdate($sRemoveBookingSql, ['iBookingDatabaseId' => $sClearSlotsSql], [$oIntType]);
	        
	        if($iRowsAffected == 0) {
	            throw new DBALException('Could not remove the booking from the database');
	        }
                 
	    }
	    catch(DBALException $e) {
	        throw BookingException::hasFailedToClearBooking($oCommand, $e);
	    }
        
        
        return true;
    }
     
    
}
/* End of File */