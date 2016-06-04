<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\ClearBookingCommand;
use IComeFromTheNet\BookMe\Bus\Exception\BookingException;


/**
 * Used to clear a booking.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class ClearBookingHandler 
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
    
    
    public function handle(ClearBookingCommand $oCommand)
    {
        $oDatabase              = $this->oDatabaseAdapter;
        $sConflictTableName     = $this->aTableNames['bm_booking_conflict'];
        $sScheduleSlotTableName = $this->aTableNames['bm_schedule_slot'];
        $sBookingTableName      = $this->aTableNames['bm_booking'];
        
        $iBookingId            = $oCommand->getBookingId();
        
        $aClearSlotsSql        = [];
        $sClearSlotsSql        = '';
        $aRemoveBookingSql     = [];
        $sRemoveBookingSql     = '';
        $aRemoveConflictSql    = [];
        $sRemoveConflictSql    = '';
        
        
        # Step 1 Clear the booking from the schedule rows
        
        $aClearSlotsSql[] = " UPDATE $sScheduleSlotTableName SET `booking_id` = NULL ";
        $aClearSlotsSql[] = " WHERE `booking_id` = ?";
   
        
        $sClearSlotsSql = implode(PHP_EOL,$aClearSlotsSql);
        
        # Step 2 Clear the conflict booking table
        
        $aRemoveConflictSql[] = " DELETE FROM  $sConflictTableName  ";
        $aRemoveConflictSql[] = " WHERE booking_id = ?";
          
        $sRemoveConflictSql = implode(PHP_EOL,$aRemoveConflictSql);
       
          
        # Step 3 Clear the booking table  
        
        $aRemoveBookingSql[] = " DELETE FROM  $sBookingTableName  ";
        $aRemoveBookingSql[] = " WHERE booking_id = ? ";
          
        $sRemoveBookingSql = implode(PHP_EOL,$aRemoveBookingSql);
      
        
        try {
            
	        $oIntType  = Type::getType(Type::INTEGER);
	    
	       	$iRowsAffected = $oDatabase->executeUpdate($sClearSlotsSql, [$iBookingId], [$oIntType]);
	        
	        if($iRowsAffected == 0) {
	            throw new DBALException('Could not clear of a booking slots');
	        }
	        
	        $iRowsAffected = $oDatabase->executeUpdate($sRemoveConflictSql, [$iBookingId], [$oIntType]);
	        
	        
	        $iRowsAffected = $oDatabase->executeUpdate($sRemoveBookingSql, [$iBookingId], [$oIntType]);
	        
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