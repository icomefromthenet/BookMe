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
        
        # Step 2 create the booking
        $aTakeBookSql[] = " INSERT INTO $sBookingTableName (`booking_id`,`schedule_id`,`slot_open,`slot_close`,`registered_date`) ";
        $aTakeBookSql[] = " VALUES (NULL, :iScheduleId, :oSlotOpen, :oSlotClose, NOW()) ";
        
        $sTakeBookSql = implode(PHP_EOL, $aTakeBookSql);
      
        # Step 3 update the scheudle with a booking
        
        $aCreateBookSql[] = " UPDATE $sScheduleSlotTableName  ";
        $aCreateBookSql[] = " SET  booking_id = :iBookingId ";
        $aCreateBookSql[] = " WHERE `schedule_id` = :iScheduleId ";
        $aCreateBookSql[] = " AND `slot_open` >= :oSlotOpen AND `slot_close` <= :oSlotClose ";
          
        $sCreateBookSql = implode(PHP_EOL,$aCreateBookSql);
      
        
        
        try {
            
	        $oDateType = Type::getType(Type::DATE);
	        $oIntType  = Type::getType(Type::INTEGER);
	    
	        $aParams = [
	            ':iScheduleId'  => $iScheduleId,
	            ':oSlotOpen'    => $oOpenDate,
	            ':oClosingSlot' => $oCloseDate,
	            
	       ];
	       
	       $aTypes = [
	            ':iScheduleId'  => $oIntType,
	            ':oSlotOpen'    => $oDateType,
	            ':oClosingSlot' => $oDateType,
	           
	       ];
	    
	        $iRowsAffected = $oDatabase->executeUpdate($sLockSql, $aParams, $aTypes);
	        
	        if($iRowsAffected == 0) {
	            throw BookingException::hasFailedToReserveSlots($oCommand ,new DBALException('Could not find schedule slots to lock'));
	        }
	        
	        $oDatabase->executeUpdate($sTakeBookSql, $aParams, $aTypes);
	        
	        $iBookingId = $oDatabase->lastInsertId();
	        
	        $oCommand->setBoookingId($iBookingId);
	       
	        if(empty($iBookingId)) {
	           throw new DBALException('Unable to insert booking into database');
	        }
	       
	        
	        $iRowsAffected = $oDatabase->executeUpdate($sCreateBookSql, array_merge($aParams,[':iBookingId' => $iBookingId]), array_merge($aTypes,[':iBookingId'=> $oIntType]));
	        
	        if($iRowsAffected == 0) {
	            throw new DBALException('Could not update schedule with new booking');
	        }
                 
	    }
	    catch(DBALException $e) {
	        throw BookingException::hasFailedToTakeBooking($oCommand, $e);
	    }
        
        
        return true;
    }
     
    
}
/* End of File */