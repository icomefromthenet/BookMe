<?php
namespace IComeFromTheNet\BookMe\Bus\Decorator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\DBALException;
use IComeFromTheNet\BookMe\Bus\Command\TakeBookingCommand;
use IComeFromTheNet\BookMe\Bus\Exception\BookingException;


/**
 * Used stop booking being taken when the max has been reached. 
 * 
 * Throw an exception before the booking is taken. 
 * 
 * Only check if the booking command has been configued with a max > 0
 * 
 * @author Lewis Dyer <getintouch@icomefromthenet.com>
 * @since 1.0
 */ 
class MaxBookingsDecorator
{
    
    /**
     * @var array   a map internal table names to external names
     */ 
    protected $aTableNames;
    
    /**
     * @var Doctrine\DBAL\Connection    the database adpater
     */ 
    protected $oDatabaseAdapter;
    
    
    protected $oHandler;
    
    
    public function __construct($oInternalHander ,array $aTableNames, Connection $oDatabaseAdapter)
    {
        $this->oDatabaseAdapter = $oDatabaseAdapter;
        $this->aTableNames      = $aTableNames;
        $this->oHandler         = $oInternalHander;    
        
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
        $iMaxBookings           = $oCommand->getMaxBookings();
       
        
        if($iMaxBookings > 0) {
            
            
            $iBookingCount = (int)$oDatabase->fetchColumn("SELECT count(booking_id)
                                     FROM $sBookingTableName 
                                     WHERE DATE(slot_open) = ?"
                                     ,[$oOpenDate],0,[Type::DATE]);
            
            # Have we exceeded max booking rile
            if($iBookingCount > $iMaxBookings) {
                throw BookingException::maxBookingsExceeded($oCommand,null);      
            } 
        
        }
        
        
        return $this->oHandler->handle($oCommand);
        
    }
     
    
}
/* End of File */