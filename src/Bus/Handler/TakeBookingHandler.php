<?php
namespace IComeFromTheNet\BookMe\Bus\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\TakeBookingCommand;
use IComeFromTheNet\BookMe\Bus\Exception\BookingException;


/**
 * Used to take a booking by reserving slots in schedule and make a booking.
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class TakeBookingHandler 
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
    
    
    public function handle(TakeBookingCommand $oCommand)
    {
        $oDatabase              = $this->oDatabaseAdapter;
        $sScheduleTableName     = $this->aTableNames['bm_schedule'];
        $sScheduleSlotTableName = $this->aTableNames['bm_schedule_slot'];
        $sBookingTableName      = $this->aTableNames['bm_booking'];
        
        $iScheduleId            = $oCommand->getScheduleId();
        $oCloseDate             = $oCommand->getClosingSlot();
        $oOpenDate              = $oCommand->getOpeningSlot();     
        
        $aLockSql               = [];
        $sLockSql               = '';
        $aTakeBookSql           = [];
        $sTakeBookSql           = '';
        
        $aCreateBookSql         = [];
        $sCreateBookSql         = '';
        
        
        # Step 1 Lock the schedule rows
        
        $aLockSql[] = " SELECT `booking_id` ";
        $aLockSql[] = " FROM $sScheduleSlotTableName  ";
        $aLockSql[] = " WHERE `schedule_id` = :iScheduleId ";
        $aLockSql[] = " AND `slot_open` >= :oSlotOpen AND `slot_close` <= :oSlotClose ";
        $aLockSql[] = " FOR UPDATE ";
        
        $sLockSql = implode(PHP_EOL,$aLockSql);
        
        # Step 2 Create the booking
        
        $aTakeBookSql[] = " INSERT INTO $sBookingTableName (`booking_id`,`schedule_id`,`slot_open`,`slot_close`,`registered_date`) ";
        $aTakeBookSql[] = " VALUES (NULL, :iScheduleId, :oSlotOpen, :oSlotClose, NOW()) ";
        
        $sTakeBookSql = implode(PHP_EOL, $aTakeBookSql);
      
        # Step 3 Update the schedule with a booking
        
        $aCreateBookSql[] = " UPDATE $sScheduleSlotTableName  ";
        $aCreateBookSql[] = " SET  booking_id = :iBookingId ";
        $aCreateBookSql[] = " WHERE `schedule_id` = :iScheduleId ";
        $aCreateBookSql[] = " AND `slot_open` >= :oSlotOpen AND `slot_close` <= :oSlotClose ";
        $aCreateBookSql[] = " AND `booking_id` IS NULL ";
        $aCreateBookSql[] = " AND ((is_available = true AND is_excluded = false) OR is_override = true) ";
        $aCreateBookSql[] = " AND is_closed = false";
          
        $sCreateBookSql = implode(PHP_EOL,$aCreateBookSql);
      
        
        
        try {
            
	        $oDateType = Type::getType(Type::DATETIME);
	        $oIntType  = Type::getType(Type::INTEGER);
	    
	        $aParams = [
	            ':iScheduleId'  => $iScheduleId,
	            ':oSlotOpen'    => $oOpenDate,
	            ':oSlotClose' => $oCloseDate,
	            
	       ];
	       
	       $aTypes = [
	            ':iScheduleId'  => $oIntType,
	            ':oSlotOpen'    => $oDateType,
	            ':oSlotClose' => $oDateType,
	           
	       ];
	    
	        $iRowsLocked = $oDatabase->executeUpdate($sLockSql, $aParams, $aTypes);
	        
	        if($iRowsLocked == 0) {
	            throw BookingException::hasFailedToFindSlots($oCommand);
	        }
	        
	        $oDatabase->executeUpdate($sTakeBookSql, $aParams, $aTypes);
	        
	        $iBookingId = $oDatabase->lastInsertId();
	        
	        $oCommand->setBoookingId($iBookingId);
	       
	        if(empty($iBookingId)) {
	           throw new DBALException('Unable to insert booking into database');
	        }
	       
	        
	        $iScheduleUsed = $oDatabase->executeUpdate($sCreateBookSql, array_merge($aParams,[':iBookingId' => $iBookingId]), array_merge($aTypes,[':iBookingId'=> $oIntType]));
	        
	        # We need to verify that we have applied the booking to each of the required slots. If we did not have this check we could end up with half a booking on the schedule
	        # this assumes the lock SQL has pikedup all slots required.
	        
	        if($iScheduleUsed == 0 || ($iScheduleUsed !==  $iRowsLocked)) {
	            throw BookingException::hasFailedToReserveSlots($oCommand);
	        }
                 
	    }
	    catch(DBALException $e) {
	        throw BookingException::hasFailedToTakeBooking($oCommand, $e);
	    }
        
        
        return true;
    }
     
    
}
/* End of File */